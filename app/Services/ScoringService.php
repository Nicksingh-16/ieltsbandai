<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * LLM Provider Note
 * -----------------
 * This service delegates all LLM transport to LLMRouter, which calls Gemini
 * via its OpenAI-compatible endpoint. Despite legacy OPENAI_* env vars and
 * config('services.openai.*') names, the active provider is Gemini 2.5 Pro
 * (with Flash as the quota-fallback). Free tier; ~95% of GPT-4o-mini quality.
 *
 * Migration to a real OpenAI key (production launch):
 *   1. Set OPENAI_API_KEY=<real OpenAI key> in production secrets.
 *   2. Set OPENAI_BASE_URL=https://api.openai.com/v1
 *   3. Set OPENAI_MODEL=gpt-4o-mini (or whichever model is purchased).
 *   No code changes required — LLMRouter and prompt-building stay identical.
 *
 * What this service still owns: prompt construction (Cambridge band
 * descriptors + Layer 3 few-shot calibration anchors via CalibrationService)
 * and post-LLM validation/normalization of scores. The HTTP call itself
 * lives in LLMRouter.
 */
class ScoringService
{
    /**
     * Pinned to every benchmark JSON so prompt regressions are diff-able.
     * Bump on any material change to the prompt body or retrieval strategy
     * (e.g. L3-v2 = topic-keyword-ranked few-shot, L4-v1 = LanguageTool block).
     */
    public const PROMPT_VERSION = 'L5-v5';

    protected CalibrationService $calibration;
    protected LLMRouter $router;

    public function __construct(?CalibrationService $calibration = null, ?LLMRouter $router = null)
    {
        $this->calibration = $calibration ?? app(CalibrationService::class);
        $this->router      = $router ?? app(LLMRouter::class);
    }

    /**
     * Score IELTS speaking transcript using OpenAI API directly
     * 
     * @param string $transcript Combined transcript text
     * @return array|null Scoring data or null on failure
     */
    public function scoreSpeaking(string $transcript, ?int $userId = null, ?int $testId = null, array $words = []): ?array
    {
        try {
            $prompt = $this->buildSpeakingScoringPrompt($transcript, $words);

            $data = $this->router
                ->withContext($userId, $testId, 'speaking_score')
                ->chatCompletion([
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a standardised IELTS Speaking examiner certified by the British Council and IDP. You score transcripts using the official IELTS Speaking Band Descriptors exactly as trained. You must respond ONLY with valid JSON, no prose before or after.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('LLM response missing content');
                return null;
            }

            // Parse JSON response (strip markdown fences first — Claude / some
            // models wrap JSON in ```json...``` even when response_format is set).
            $scoring = json_decode($this->stripJsonFences($content), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse scoring JSON', [
                    'content' => $content,
                    'error' => json_last_error_msg(),
                ]);
                return null;
            }

            // Normalize field names
            $scoring['grammar'] = $scoring['grammatical_range_accuracy'] ?? ($scoring['grammar'] ?? 0);

            // Validate / Normalize
            $required = ['fluency', 'lexical', 'grammar', 'pronunciation'];
            foreach ($required as $field) {
                if (!isset($scoring[$field])) {
                    Log::error('Missing scoring field: ' . $field);
                    return null;
                }
            }

            // Validate scores are numeric and in range
            foreach ($required as $field) {
                $score = (float) $scoring[$field];
                if ($score < 0 || $score > 9) {
                    Log::error('Invalid score range for ' . $field . ': ' . $score);
                    return null;
                }
                $scoring[$field] = $score;
            }

            // L5-v2: single canonical bias-correction site (same as writing).
            $this->applyBiasCorrection($scoring, [
                'fluency', 'lexical', 'grammar', 'pronunciation',
            ]);

            return $scoring;

        } catch (\Exception $e) {
            Log::error('Speaking scoring failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Score IELTS writing using OpenAI API
     * 
     * @param string $answer User's written response
     * @param object $question Question object with type and content
     * @return array|null Scoring data or null on failure
     */
    public function scoreWriting(string $answer, $question, ?int $userId = null, ?int $testId = null): ?array
    {
        try {
            $prompt = $this->buildWritingScoringPrompt($answer, $question);

            $data = $this->router
                ->withContext($userId, $testId, 'writing_score')
                ->chatCompletion([
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a standardised IELTS Writing examiner certified by Cambridge Assessment English. You score writing responses using the official IELTS Writing Band Descriptors exactly as trained. You must respond ONLY with valid JSON, no prose before or after.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('LLM response missing content for writing scoring');
                return null;
            }

            $scoring = json_decode($this->stripJsonFences($content), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse scoring JSON', [
                    'content' => $content,
                    'error' => json_last_error_msg(),
                ]);
                return null;
            }

            // Validate and normalize fields
            $scoring['task_achievement'] = $scoring['task_response'] ?? ($scoring['task_achievement'] ?? 0);
            $scoring['grammar'] = $scoring['grammatical_range_accuracy'] ?? ($scoring['grammar'] ?? 0);
            
            $required = ['task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar'];
            foreach ($required as $field) {
                if (!isset($scoring[$field])) {
                    Log::error('Missing scoring field: ' . $field);
                    return null;
                }
            }

            // Ensure band_9_rewrite and other new fields exist
            $scoring['band_9_rewrite'] = $scoring['band_9_rewrite'] ?? '';
            $scoring['topic_vocabulary'] = $scoring['topic_vocabulary'] ?? [];
            $scoring['examiner_comments'] = $scoring['examiner_comments'] ?? [];
            $scoring['error_summary'] = $scoring['error_summary'] ?? [];

            // Validate scores
            foreach ($required as $field) {
                $score = (float) $scoring[$field];
                if ($score < 0 || $score > 9) {
                    Log::error('Invalid score range for ' . $field . ': ' . $score);
                    return null;
                }
                $scoring[$field] = $score;
            }

            // L5-v1: hard server-side under-length cap on TA/TR. Rule 6 of the
            // prompt tells the LLM to reduce TA/TR by 0.5 when below minimum
            // word count, but observed runs show the model frequently ignores
            // it — apply deterministically here so the penalty is always
            // enforced and visible in benchmark deltas.
            $wordCount = str_word_count(trim($answer));
            $category = (string) ($question->category ?? '');
            $isTask2  = str_contains($category, 'task2');
            $minWords = $isTask2 ? 250 : 150;
            if ($wordCount > 0 && $wordCount < $minWords) {
                $original = $scoring['task_achievement'];
                $scoring['task_achievement'] = max(0.0, round(($original - 0.5) * 2) / 2);
                Log::info('Applied under-length TA/TR penalty', [
                    'word_count'  => $wordCount,
                    'min_words'   => $minWords,
                    'original_ta' => $original,
                    'adjusted_ta' => $scoring['task_achievement'],
                ]);
            }

            // L5-v2: single canonical bias-correction site. Shifts EACH
            // criterion AND overall_band by the same piecewise amount so the
            // report is internally consistent (sub-scores average to overall).
            //
            // L5-v3 experiment (signal-driven high-band boost) was tried and
            // reverted: TTR/cohesion/CEFR-share signals do NOT reliably
            // separate Band 6 from Band 8.5 in the Cambridge dataset. The
            // detector false-positived on strong Band 6 essays (over-shifted
            // them to Band 8) without recovering the genuinely-Band-8.5 cases.
            // Re-enable only when LanguageTool grammar-error density is
            // available (it's the strongest discriminator we have, and is
            // unreliable in dev without Docker running).
            $this->applyBiasCorrection($scoring, [
                'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
            ]);

            // Normalize and Validate Errors (Utmost Level)
            $normalizedErrors = [];
            $rawErrors = $scoring['errors'] ?? [];
            if (is_array($rawErrors)) {
                foreach ($rawErrors as $index => $error) {
                    if (empty($error['text'])) continue;
                    
                    $normalizedErrors[] = [
                        'id' => 'err_' . ($index + 1) . '_' . dechex(time()),
                        'text' => trim($error['text']),
                        'type' => ucfirst(strtolower($error['type'] ?? 'Grammar')),
                        'category' => ucfirst(strtolower($error['type'] ?? 'Grammar')),
                        'severity' => strtolower($error['severity'] ?? 'medium'),
                        'correction' => $error['correction'] ?? '',
                        'explanation' => $error['explanation'] ?? '',
                    ];
                }
            }
            $scoring['errors'] = $normalizedErrors;

            // Validate errors before returning
            $scoring['errors'] = $this->validateAndCleanErrors($scoring['errors'], $answer);

            return $scoring;

        } catch (\Exception $e) {
            Log::error('Writing scoring failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Strip markdown code fences from an LLM JSON response. Anthropic and some
     * OpenRouter passthroughs wrap JSON in ```json...``` even when the request
     * sets response_format=json_object. Falls back to extracting the largest
     * {...} block if a wrapper looks broken.
     */
    protected function stripJsonFences(string $content): string
    {
        $trimmed = trim($content);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```\s*$/', '', $trimmed) ?? $trimmed;
        }
        // Safety net: extract the outermost JSON object if there's prose around it.
        if (!str_starts_with(trim($trimmed), '{') && preg_match('/\{.*\}/s', $trimmed, $m)) {
            $trimmed = $m[0];
        }
        return trim($trimmed);
    }

    /**
     * Validate and clean AI-detected errors to prevent false positives
     * 
     * @param array $errors Array of errors from AI
     * @param string $userAnswer Original user's answer text
     * @return array Validated errors only
     */
    protected function validateAndCleanErrors(array $errors, string $userAnswer): array
    {
        $validated = [];
        $seen = [];
        
        foreach ($errors as $error) {
            $errorText = trim($error['text'] ?? '');
            
            // Skip empty errors
            if (empty($errorText)) {
                Log::debug('Skipping empty error text');
                continue;
            }
            
            // Skip duplicate errors (case-insensitive)
            $key = strtolower($errorText);
            if (isset($seen[$key])) {
                Log::debug('Skipping duplicate error', ['text' => $errorText]);
                continue;
            }
            $seen[$key] = true;
            
            // CRITICAL: Verify error text actually exists in user's answer
            // Use case-insensitive search to handle minor variations
            if (stripos($userAnswer, $errorText) === false) {
                // Try with normalized whitespace
                $normalizedError = preg_replace('/\s+/', ' ', $errorText);
                $normalizedAnswer = preg_replace('/\s+/', ' ', $userAnswer);
                
                if (stripos($normalizedAnswer, $normalizedError) === false) {
                    Log::warning('AI hallucinated error - text not found in answer', [
                        'error_text' => $errorText,
                        'type' => $error['type'] ?? 'unknown',
                        'category' => $error['category'] ?? 'unknown'
                    ]);
                    continue;
                }
            }
            
            $validated[] = $error;
        }
        
        Log::info('Error validation complete', [
            'original_count' => count($errors),
            'validated_count' => count($validated),
            'removed_count' => count($errors) - count($validated)
        ]);
        
        return $validated;
    }

    /**
     * Build the scoring prompt for speaking AI evaluation.
     *
     * TODO Phase A.5: inject speaking few-shot examples once calibration set
     * covers parts 1–3. The CalibrationService already returns an empty
     * Collection for speaking_part_* so wiring will be a no-op until then.
     */
    protected function buildSpeakingScoringPrompt(string $transcript, array $words = []): string
    {
        // Layer 4 (speaking): deterministic acoustic signals from STT
        // word timestamps + per-word confidence. Empty when words[] is
        // missing (legacy rows / provider returned no word data) — block
        // renders as empty string and the prompt continues unchanged.
        $acousticBlock = '';
        if (!empty($words)) {
            /** @var SpeakingAcousticAnalyzer $analyzer */
            $analyzer = app(SpeakingAcousticAnalyzer::class);
            $signals  = $analyzer->analyze($words);
            $acousticBlock = $analyzer->buildPromptBlock($signals);
        }

        return <<<PROMPT
You are a trained IELTS Speaking examiner with 15+ years of examining experience, certified by the British Council and IDP. You have been standardised against the official IELTS Speaking Band Descriptors and apply them with the same precision as a live examiner.

Your task: score the following speaking transcript against the four official IELTS Speaking criteria using the OFFICIAL PUBLIC BAND DESCRIPTORS reproduced below.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
OFFICIAL IELTS SPEAKING BAND DESCRIPTORS
(Public Version — British Council / IDP / Cambridge)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CRITERION 1 — FLUENCY AND COHERENCE (FC)
Band 9: Speaks fluently with only rare repetition or self-correction; any hesitation is content-related rather than to find words or grammar; speaks coherently with fully coherent discourse; develops topics fully and appropriately.
Band 8: Speaks fluently with only occasional repetition or self-correction; hesitation is usually content-related and only rarely to search for language; develops topics coherently and appropriately.
Band 7: Speaks at length without noticeable effort or loss of coherence; may demonstrate language-related hesitation at times, or some repetition and/or self-correction; uses a range of connectives and discourse markers with some flexibility.
Band 6: Is willing to speak at length, though may lose coherence at times due to occasional digression, repetition or self-correction; uses a range of connectives and discourse markers but not always appropriately.
Band 5: Usually maintains flow of speech but uses repetition, self-correction and/or slow speech to keep going; may over-use certain connectives and discourse markers; produces simple speech fluently, but more complex communication causes fluency problems.
Band 4: Cannot always maintain fluency and is often hesitant with limited language resources; hesitation is often associated with mid-utterance; limited ability to link utterances or ideas coherently; often uses only basic discourse markers.

CRITERION 2 — LEXICAL RESOURCE (LR)
Band 9: Uses vocabulary with full flexibility and precision in all topics; uses idiomatic language naturally and accurately.
Band 8: Uses a wide vocabulary resource readily and flexibly to convey precise meaning; uses less common and idiomatic vocabulary skilfully, with occasional inaccuracies; uses paraphrase effectively as required.
Band 7: Uses vocabulary resource flexibly to discuss a variety of topics; uses some less common and idiomatic vocabulary and shows some awareness of style and collocation, with some inappropriate choices; uses paraphrase effectively.
Band 6: Has a wide enough vocabulary to discuss topics at length and make meaning clear in spite of some inappropriacies; generally paraphrases successfully.
Band 5: Manages to talk about familiar and unfamiliar topics but uses vocabulary with limited flexibility; attempts to use paraphrase but with mixed success.
Band 4: Can talk about familiar topics but can only convey basic meaning on unfamiliar topics and makes frequent errors in word choice; rarely attempts paraphrase.

CRITERION 3 — GRAMMATICAL RANGE AND ACCURACY (GRA)
Band 9: Uses a full range of structures naturally and appropriately; produces consistently accurate structures apart from 'slips' characteristic of native speaker speech.
Band 8: Uses a wide range of structures flexibly; produces a majority of error-free sentences with only very occasional inappropriacies or basic non-systematic errors.
Band 7: Uses a range of complex structures with some flexibility; frequently produces error-free sentences, though some grammatical mistakes persist.
Band 6: Uses a mix of simple and complex structures, but with limited flexibility; may make frequent mistakes with complex structures, though these rarely cause comprehension difficulties.
Band 5: Produces basic sentence forms with reasonable accuracy; uses a limited range of more complex structures, but these are usually inaccurate; errors can cause some comprehension difficulties.
Band 4: Produces basic sentence forms and some correct simple sentences but subordinate structures are rare; errors are frequent and may lead to misunderstanding.

CRITERION 4 — PRONUNCIATION (PRON)
Band 9: Uses a full range of phonological features with precision and subtlety; sustains flexible use of features, with only rare lapses; is easy to understand throughout.
Band 8: Uses a wide range of phonological features with precision and subtlety; only very occasional lapses in control; is easy to understand throughout; L1 accent has minimal effect on intelligibility.
Band 7: Uses phonological features effectively; accent is non-intrusive; individual sounds and word stress are generally accurate.
Band 6: Uses a range of phonological features with mixed control; can generally be understood throughout, though mispronunciation of individual words or sounds reduces clarity at times.
Band 5: Shows evidence of an attempt to produce phonological features, but control is limited; mispronunciations are frequent and cause some difficulty for the listener.
Band 4: Uses a limited range of phonological features; mispronunciations are frequent and cause considerable difficulty for the listener.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CALIBRATION ANCHORS (Examiner Standardisation Reference)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Band 5.0 anchor: Candidate speaks on familiar topics with simple fluency; frequent grammatical errors in complex structures; vocabulary is adequate but repetitive; accent requires occasional listener effort.
Band 6.0 anchor: Candidate speaks at reasonable length; some language-related hesitation; uses a mix of simple and complex grammar with limited accuracy on complex forms; vocabulary adequate with some inappropriate choices; generally intelligible.
Band 6.5 anchor: Better than Band 6 on most criteria but not yet consistently meeting Band 7 — award 6.5 when candidate oscillates between descriptors.
Band 7.0 anchor: Candidate speaks at length without noticeable effort; uses a RANGE of complex structures with FREQUENT error-free sentences; vocabulary is flexible and includes less common items used accurately; accent does not impede.
Band 8.0 anchor: Near-expert control; hesitation is content-driven not language-driven; the MAJORITY of sentences are error-free; idiomatic vocabulary used with skill.
{$acousticBlock}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EXAMINER SCORING RULES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Score each criterion independently against its descriptor — do NOT average first.
2. Half-bands (5.5, 6.5, 7.5) are awarded when the candidate sits between two descriptors.
3. Overall band = arithmetic mean of 4 criteria, rounded to nearest 0.5.
4. Band 8+ requires the candidate to MEET the Band 8 descriptor on ALL 4 criteria. A single Band 6 criterion prevents Band 8 overall.
5. Fillers ("uh", "um", "like", "you know") exceeding 15 per 100 words → FC ≤ 5.5. When the GROUND-TRUTH ACOUSTIC SIGNALS block above is present, USE its computed filler-per-100-words figure as evidence — do NOT re-count fillers from the transcript.
6. Memorised or rehearsed responses that lack spontaneous development → FC ≤ 5.5, LR ≤ 6.0.
7. Accent that causes the listener repeated effort → PRON ≤ 5.5.
8. If the transcript is very short (< 80 words) or refuses to engage, cap all criteria at Band 5.0.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TRANSCRIPT TO EVALUATE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
{$transcript}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
REQUIRED JSON OUTPUT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Return ONLY valid JSON. No prose before or after.

{
  "fluency": X.X,
  "lexical": X.X,
  "grammatical_range_accuracy": X.X,
  "pronunciation": X.X,
  "overall_band": X.X,
  "band_confidence_range": "X.X – X.X",
  "descriptor_match": {
    "fluency": "Exact descriptor phrase from the band awarded",
    "lexical": "Exact descriptor phrase from the band awarded",
    "grammatical_range_accuracy": "Exact descriptor phrase from the band awarded",
    "pronunciation": "Exact descriptor phrase from the band awarded"
  },
  "examiner_comments": [
    "Evidence-based comment citing specific transcript moments that justify the fluency score.",
    "Evidence-based comment citing specific transcript moments that justify the lexical score.",
    "Evidence-based comment citing specific transcript moments that justify the grammar score.",
    "Evidence-based comment citing specific transcript moments that justify the pronunciation score."
  ],
  "strengths": ["Specific positive features observed in the transcript"],
  "areas_to_improve": ["Specific, actionable improvement targets tied to descriptor gaps"],
  "error_summary": {
    "grammar_errors_per_100_words": X,
    "repeated_errors": ["specific repeated error patterns observed"]
  },
  "metadata": {
    "fillers_detected": X,
    "word_count_estimate": X,
    "repetition_flags": true|false,
    "pronunciation_notes": ["specific phonological observations"]
  }
}
PROMPT;
    }

    /**
     * Build the scoring prompt for writing AI evaluation based on task type
     */
    protected function buildWritingScoringPrompt(string $answer, $question): string
    {
        $questionContent = $question->content ?? '';
        $category = $question->category ?? '';
        // Question::metadata may be cast to array by Eloquent (json cast) or
        // be a raw string from older rows. Handle both without crashing.
        $rawMetadata = $question->metadata ?? '{}';
        $metadata = is_array($rawMetadata) ? $rawMetadata : (json_decode($rawMetadata, true) ?: []);

        // Determine task type and specific criteria
        $taskType = $this->determineTaskType($category);
        $specificCriteria = $this->getTaskSpecificCriteria($taskType);

        // Layer 3: pull 3 calibrated reference essays (low/mid/high band anchors)
        // for the matching task type and inject them as few-shot anchors. Empty
        // collection => no block rendered (e.g. unknown task type).
        $calibTaskType = $this->mapToCalibrationTaskType($taskType);
        $fewShotBlock = $this->buildCalibratedExamplesBlock($answer, $calibTaskType);

        // Layer 4: deterministic ground-truth signals (LanguageTool grammar
        // count + LinguisticAnalyzer TTR/CEFR/cohesion). Injected so the LLM
        // does not have to count or estimate these features itself.
        $signalsBlock = $this->buildGroundTruthSignalsBlock($answer);

        $task1MetadataInstructions = "";
        if (str_contains($taskType, 'Task 1') && str_contains($taskType, 'Academic')) {
            $metaJson = json_encode($metadata, JSON_PRETTY_PRINT);
            $task1MetadataInstructions = <<<META
CRITICAL - DATA SOURCES:
The following is the ground truth metadata for the graph/chart.
Evaluate ONLY based on this data. Do NOT interpret or hallucinate from any external knowledge.
METADATA:
{$metaJson}

TASK 1 ACHIEVEMENT GUIDANCE:
The presence and quality of an overview is a key TA signal but NOT a hard cap.
- A clear, accurate overview (any summary statement of main trends/changes,
  whether in second paragraph or conclusion) supports TA Band 7+.
- A weak/partial overview (mentions some main features but incomplete) consider TA Band 6.
- A completely absent overview is one Band 5 indicator, but other strengths
  (data accuracy, comparisons, structure) can still support TA Band 6.
- Defer to the official band descriptors and the calibrated examples above
  rather than mechanical rules.
- Penalize materially incorrect trend descriptions, but a single small
  misreading does not cap TA.
META;
        }

        $task2Instructions = "";
        if (str_contains($taskType, 'Task 2')) {
            $task2Instructions = <<<TASK2
STRICT TASK 2 RULES:
- Clear position must be maintained throughout.
- At least 2 developed ideas required.
- Each idea must include explanation + example or consequence.
- If position is unclear or ideas underdeveloped -> TR <= 6.0.
TASK2;
        }

        return <<<PROMPT
You are a trained IELTS Writing examiner with 15+ years of examining experience, certified by Cambridge Assessment English. You apply the official IELTS Writing Band Descriptors with the same precision and consistency as a live examiner, having undergone regular standardisation training.

Your task: score the following writing response against the four official IELTS Writing criteria using the OFFICIAL PUBLIC BAND DESCRIPTORS reproduced below.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
OFFICIAL IELTS WRITING BAND DESCRIPTORS
(Public Version — Cambridge Assessment English)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CRITERION 1A — TASK ACHIEVEMENT (TA) [Task 1 Academic & General]
Band 9: Fully satisfies all requirements of the task. Clearly presents a fully developed response.
Band 8: Covers all requirements of the task sufficiently. Presents, highlights and illustrates key features / bullet points clearly and appropriately.
Band 7: Covers the requirements of the task; clearly presents and highlights key features / bullet points but could be more fully extended.
Band 6: Addresses the requirements of the task; key features are selected and highlighted but could be more clearly described; may include some irrelevant, inappropriate or inaccurate information.
Band 5: Recounts detail mechanically with no clear overview; there may be some attempt to compare data but it is not clearly presented; there may be irrelevant information present.
Band 4: The prompt is inadequately addressed or the response is tangentially related to the task; key features may be selected but not highlighted; the format may be inappropriate.

CRITERION 1B — TASK RESPONSE (TR) [Task 2 Academic & General]
Band 9: Fully addresses all parts of the task. Presents a fully developed position with relevant, fully extended and well-supported ideas.
Band 8: Sufficiently addresses all parts of the task. Presents a well-developed response with relevant, extended and supported ideas.
Band 7: Addresses all parts of the task. Presents a clear position throughout; extends and supports main ideas, but there may be a tendency to over-generalise and/or supporting ideas may lack focus.
Band 6: Addresses all parts of the task although some parts may be more fully covered than others. Presents a relevant position although the conclusions may become unclear or repetitive; presents relevant main ideas but some may be inadequately developed/unclear.
Band 5: Addresses the task only partially. Expresses a position but the development is not always clear; limited relevant ideas are presented and these may be inadequately supported.
Band 4: Responds to the task only in a minimal way or the answer is tangential. A position is discernible but the reader has to read carefully to find it; limited ideas are presented and these may be repetitive or inadequately supported.

CRITERION 2 — COHERENCE AND COHESION (CC)
Band 9: Uses cohesion in such a way that it attracts no attention; skilfully manages paragraphing.
Band 8: Sequences information and ideas logically; manages all aspects of cohesion well; uses paragraphing sufficiently and appropriately.
Band 7: Logically organises information and ideas; there is clear progression throughout; uses a range of cohesive devices appropriately although there may be some under-/over-use.
Band 6: Arranges information and ideas coherently and there is a clear overall progression; uses cohesive devices effectively but cohesion within and/or between sentences may be faulty or mechanical; uses paragraphing but not always logically.
Band 5: Presents information with some organisation but there may be a lack of overall progression; makes inadequate, inaccurate or over-use of cohesive devices; uses paragraphing but not always logically.
Band 4: Presents information and ideas but these are not arranged coherently and there is no clear progression; uses some basic cohesive devices but these may be inaccurate or repetitive; may not write in paragraphs or paragraphing may be inadequate.

CRITERION 3 — LEXICAL RESOURCE (LR)
Band 9: Uses a wide range of vocabulary with very natural and sophisticated control of lexical features; rare minor errors occur only as 'slips'.
Band 8: Uses a wide range of vocabulary fluently and flexibly to convey precise meanings; skilfully uses uncommon lexical items but there may be occasional inaccuracies in word choice and collocation; produces rare errors in spelling and/or word formation.
Band 7: Uses a sufficient range of vocabulary to allow some flexibility and precision; uses less common lexical items with some awareness of style and collocation; may produce occasional errors in word choice, spelling and/or word formation.
Band 6: Uses an adequate range of vocabulary for the task; attempts to use less common vocabulary but with some inaccuracy; makes some errors in spelling and/or word formation, but these do not impede communication.
Band 5: Uses a limited range of vocabulary, but this is minimally adequate for the task; may make noticeable errors in spelling and/or word formation that may cause some difficulty for the reader; over-relies on particular words and phrases.
Band 4: Uses only basic vocabulary which may be used repetitively or which may be inappropriate for the task; has limited control of word formation and/or spelling; errors may cause strain for the reader.

CRITERION 4 — GRAMMATICAL RANGE AND ACCURACY (GRA)
Band 9: Uses a wide range of structures with full flexibility and accuracy; rare minor errors occur only as 'slips'.
Band 8: Uses a wide range of structures; the majority of sentences are error-free; makes only very occasional errors or inappropriacies.
Band 7: Uses a variety of complex structures; produces frequent error-free sentences; has good control of grammar and punctuation but may make a few errors.
Band 6: Uses a mix of simple and complex sentence forms; makes some errors in grammar and punctuation but they rarely impede communication.
Band 5: Uses only a limited range of structures; attempts complex sentences but these tend to be less accurate than simple sentences; may make frequent grammatical errors and punctuation may be faulty; errors can cause some difficulty for the reader.
Band 4: Uses only a very limited range of structures with only rare use of subordinate clauses; some structures are accurate but errors predominate; punctuation is often faulty.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CALIBRATION ANCHORS (Examiner Standardisation Reference)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Band 5.0 essay anchor: Task partially addressed, no clear overview (Task 1) or unclear position (Task 2); limited range of structures; frequent grammatical errors; limited vocabulary range.
Band 6.0 essay anchor: Task adequately addressed with overview/position; mix of simple and complex sentences with some errors; adequate vocabulary with attempts at less common items; some errors in cohesion.
Band 6.5 anchor: Clearly better than Band 6 on most criteria but not consistently meeting Band 7 — award when oscillating.
Band 7.0 essay anchor: Task fully addressed; variety of complex structures with FREQUENT error-free sentences; less common vocabulary used with some precision; clear logical organisation with appropriate cohesive devices.
Band 8.0 essay anchor: All parts addressed fully; wide range of structures with MAJORITY error-free; wide vocabulary with precision and only occasional inaccuracies; seamless cohesion.
{$fewShotBlock}
{$signalsBlock}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EXAMINER SCORING RULES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Score each criterion independently against its descriptor — the four scores may differ.
2. Half-bands (5.5, 6.5, 7.5) are awarded when the response sits between two descriptors.
3. Overall band = arithmetic mean of 4 criteria, rounded to nearest 0.5.
4. Task 1: Award TA ≤ 5.0 if NO overview is present. A present-but-weak overview → TA 5.5–6.0.
5. Task 2: Award TR ≤ 5.0 if the position is absent or if only one idea is developed.
6. Word count below minimum (Task 1 < 150 words, Task 2 < 250 words) → reduce TA/TR by 0.5.
7. Band 7+ GRA requires: VARIETY of complex structures AND frequent error-free sentences. If the response has only isolated complex sentences or repeated complex forms, GRA ≤ 6.5.
8. Band 7+ LR requires: less common vocabulary used accurately AND few errors. If vocabulary is wide but frequently inaccurate, LR ≤ 6.5.
9. Do NOT penalise different-but-valid phrasings. Only flag genuine errors.

TASK TYPE: {$taskType}
QUESTION:
{$questionContent}

USER'S ANSWER:
{$answer}

{$specificCriteria}
{$task1MetadataInstructions}
{$task2Instructions}

ERROR DETECTION REQUIREMENTS (ACCURATE & HELPFUL):
- Identify REAL errors that actually impact clarity, accuracy, or IELTS band criteria.
- Focus on errors that help the student improve, not artificial quotas.
- Your goal is ACCURATE DIAGNOSTIC FEEDBACK that builds trust.
- Quality over quantity - only flag genuine errors.

GRAMMAR ERRORS - Detect EVERY instance (MANDATORY):
- Tense errors, subject-verb agreement (e.g., "people thinks" -> "people think")
- Articles (missing/incorrect 'the', 'a', 'an'):
  * "learning foreign language" -> "learning a foreign language"
  * "at early age" -> "at an early age"
- Word form errors:
  * Adjective used as adverb ("absorb easier" -> "absorb more easily")
  * Noun used as verb or vice versa
- Passive Voice Errors (Common in Task 1):
  * Missing 'd'/'ed' after 'be' ("is locate" -> "is located", "be replace" -> "be replaced")
  * Missing auxiliary verb ("it replaced by" -> "it is replaced by")
- "There is/are" Agreement:
  * "There is a shops" -> "There are shops" (MUST DETECT)
- Prepositions (in/on/at/of/to):
  * "tolerant to" -> "tolerant of"
  * "depend of" -> "depend on"
  * "in the main road" -> "on the main road"
- Missing commas after time phrases: "In 1950 about" -> "In 1950, about" (MUST DETECT)
- Singular/Plural mismatch: "many research" -> "much research" or "many studies"

VOCABULARY ERRORS - Flag only when word choice is clearly wrong or causes confusion:
- Incorrect word choice that changes meaning or causes confusion
- Informal language in academic context (only if clearly inappropriate)
- Spelling errors
- Collocations that are unnatural or incorrect
- NOTE: "number" vs "percentage" is acceptable if context is clear - only flag if it causes confusion
- NOTE: Simple verbs like "shows", "increased" are acceptable - only flag if variety is severely lacking
- NOTE: Word repetition is only an error if excessive (5+ times in short essay) AND better alternatives exist

CRITICAL — WORD-CONFUSION (HOMOPHONE/HOMONYM) ERRORS ARE LEXICAL, NOT GRAMMATICAL:
The following error patterns are LEXICAL RESOURCE issues (categorize as Vocabulary).
They MUST NOT be categorized as Grammar — they're word-choice mistakes, not
structural grammar errors, and the IELTS GRA descriptor explicitly covers
"structures + accuracy" not lexical confusion:
  • effect / affect          (verb confusion)
  • then / than              (comparison vs sequence)
  • their / there / they're   (pronoun/contraction confusion)
  • its / it's               (possessive vs contraction)
  • accept / except          (verb vs preposition)
  • loose / lose             (adjective vs verb)
  • principle / principal    (noun confusion)
  • complement / compliment  (verb confusion)
  • whose / who's            (possessive vs contraction)
  • your / you're            (possessive vs contraction)
  • lay / lie                (transitive vs intransitive — borderline; lean Vocabulary)
  • fewer / less             (countable vs uncountable — lean Vocabulary)
  • amount / number          (quantifier confusion)

These pull LR down (precision of word choice), NOT GRA. A candidate who
writes structurally varied complex sentences with one or two of these
confusions still meets the Band 7 GRA descriptor ("frequent error-free
sentences, may make a few errors"). Penalising them under Grammar
double-counts the same error and unfairly drops GRA.

Comparative/superlative WORD FORMATION (e.g. "more kind" → "kinder",
"more easy" → "easier", "most happiest" → "happiest"):
  • If isolated and rare → Vocabulary (word form, low severity)
  • If frequent across the essay → Grammar (systematic structural issue)

Apostrophe omissions in possessives ("students confidence" missing the
apostrophe) → Punctuation, NOT Grammar.

IMPORTANT - VOCABULARY vs TASK RESPONSE:
- "number" instead of "percentage" = VOCABULARY error (medium severity)
- "people" repeated multiple times = VOCABULARY error (low-medium severity)
- These are NOT task response errors - the task IS being addressed
- Task response errors are ONLY: missing overview, wrong trends, incorrect data interpretation

PUNCTUATION ERRORS - Flag only genuine punctuation mistakes:
- CRITICAL: Before flagging a missing comma, verify it is actually missing in the text
- Missing commas after introductory phrases ONLY if they are truly absent
- Incorrect capitalization, apostrophes, periods
- Run-on sentences or comma splices

COHESION ERRORS - Flag issues that affect logical flow and clarity:
- Unclear references or vague pronouns that cause confusion
- Missing or weak transitions between ideas
- Inappropriate linking words (e.g., "In conclusion" for Task 1 data description)
- Paragraph structure issues that affect coherence
- NOTE: Minor variations in phrasing are acceptable - only flag if clarity is affected

DETECTION GUIDELINES (QUALITY OVER QUANTITY):
- Focus on errors that genuinely impact the IELTS band score
- Prioritize errors affecting clarity, accuracy, and task achievement
- Do NOT fabricate errors to meet arbitrary quotas
- Better to miss a minor error than to flag something that isn't wrong

CRITICAL: ERROR DETECTION vs BAND SCORING:
- Finding 15-20 errors does NOT automatically mean Band 5.
- Band 6 essays CAN have 15-20 minor errors if:
  * The task is adequately addressed
  * The overview is present and clear
  * The main trends are correctly identified
  * The message is generally clear despite errors
  * Errors are mostly minor (vocabulary precision, repetition, punctuation)
- ONLY give Band 5 if:
  * Task response is inadequate (no overview, wrong trends)
  * Multiple major grammar errors that obscure meaning
  * Coherence is seriously affected
- Your job: DETECT all errors comprehensively, but SCORE based on overall task achievement and clarity.

Provide evaluation in JSON:
{
  "task_response": 0.0,
  "coherence_cohesion": 0.0,
  "lexical_resource": 0.0,
  "grammatical_range_accuracy": 0.0,
  "overall_band": 0.0,
  "band_confidence_range": "X.X – X.X",
  "descriptor_match": {
    "task_achievement": "Exact phrase from the official descriptor that best matches the awarded band",
    "coherence_cohesion": "Exact phrase from the official descriptor that best matches the awarded band",
    "lexical_resource": "Exact phrase from the official descriptor that best matches the awarded band",
    "grammatical_range_accuracy": "Exact phrase from the official descriptor that best matches the awarded band"
  },
  "examiner_comments": ["Evidence-based comment citing specific writing patterns that justify each criterion score."],
  "band_explanations": {
    "task_achievement": {
      "why": "2–3 sentence explanation citing specific evidence from the response (e.g., 'The overview identifies the two main trends — X and Y — which satisfies the Band 7 descriptor').",
      "tip": "Specific, actionable advice tied to the next band descriptor (e.g., 'To reach Band 7 TA, include a more precise overview that identifies the most significant feature across all data sets')."
    },
    "coherence_cohesion": {
      "why": "2–3 sentence explanation citing specific evidence from the response.",
      "tip": "Specific, actionable advice tied to the next band descriptor."
    },
    "lexical_resource": {
      "why": "2–3 sentence explanation citing specific vocabulary choices from the response.",
      "tip": "Specific, actionable advice tied to the next band descriptor."
    },
    "grammar": {
      "why": "2–3 sentence explanation citing specific grammatical patterns from the response.",
      "tip": "Specific, actionable advice tied to the next band descriptor."
    }
  },
  "error_summary": {
    "grammar_errors_per_100_words": 0,
    "repeated_errors": ["specific repeated error patterns observed"]
  },
  "band_9_rewrite": "A complete Band 9 model response to the same question.",
  "topic_vocabulary": ["5–7 topic-specific advanced and natural lexical items appropriate for this task"],
  "errors": [
    {
      "text": "EXACT 1–5 word span from the user response (character-for-character match)",
      "type": "Grammar|Vocabulary|Punctuation|Cohesion",
      "severity": "low|medium|high",
      "correction": "Corrected version of the extracted span",
      "explanation": "Examiner explanation of WHY this is an error, which descriptor it affects, and how fixing it would improve the band."
    }
  ]
}

MANDATORY — BAND EXPLANATIONS:
- You MUST generate detailed "why" and "tip" for ALL 4 criteria. No exceptions.
- The "why" field must cite SPECIFIC evidence from the response — quote or reference actual sentences/words.
- The "tip" field must be tied to what the NEXT band descriptor requires — tell the candidate exactly what to do differently.
- DO NOT use generic placeholders like "pending", "rationale pending", or "see above".
- Example for Task Achievement Band 6.0 (Task 1):
  "why": "The overview correctly identifies that urban populations increased while rural populations declined, satisfying the Band 6 descriptor of 'key features are selected and highlighted'. However, the overview lacks a comparative endpoint figure, which prevents it from being 'clearly presented' at Band 7."
  "tip": "At Band 7, your overview must identify the most significant feature AND include a key comparator (e.g., 'by 2020, urban residents outnumbered rural by 2:1'). Add one precise comparative statement to your overview paragraph."

CRITICAL ERROR EXTRACTION RULES:
- Extract ONLY the MINIMAL error portion (1-5 words maximum). DO NOT extract full sentences.
- Examples of CORRECT extraction:
  ✓ "was increased" (not "the population was increased steadily")
  ✓ "people living" (not "while the percentage of people living in rural areas declined")
  ✓ "about 40%" (not "the percentage of urban residents had increased to about 40%")
- You MUST use the EXACT character-for-character text from the user's answer.
- BEFORE flagging an error, verify it actually exists in the user's text.
- Example: If the text has "Overall," with a comma, do NOT flag it as missing a comma.
- Every error must be categorized into one of: Grammar, Vocabulary, Punctuation, or Cohesion.
- Focus on errors that actually impact the band score.
- The "explanation" must sound like a senior examiner.

Return ONLY valid JSON.
PROMPT;
    }

    /**
     * Determine task type from category
     */
    protected function determineTaskType($category): string
    {
        if (str_contains($category, 'academic_task1')) {
            return 'Academic Task 1 (Graph/Chart/Diagram Description)';
        } elseif (str_contains($category, 'academic_task2')) {
            return 'Academic Task 2 (Essay)';
        } elseif (str_contains($category, 'general_task1')) {
            return 'General Training Task 1 (Letter)';
        } elseif (str_contains($category, 'general_task2')) {
            return 'General Training Task 2 (Essay)';
        }

        return 'Unknown';
    }

    /**
     * Map the human-readable task label produced by determineTaskType() to the
     * enum value stored on calibrated_essays.task_type. Used so the prompt
     * builder can request matching few-shot examples from CalibrationService.
     */
    protected function mapToCalibrationTaskType(string $humanTaskType): string
    {
        if (str_contains($humanTaskType, 'Task 1') && str_contains($humanTaskType, 'Academic')) {
            return 'writing_task_1_academic';
        }
        if (str_contains($humanTaskType, 'Task 1') && str_contains($humanTaskType, 'General')) {
            return 'writing_task_1_general';
        }
        if (str_contains($humanTaskType, 'Task 2')) {
            return 'writing_task_2';
        }
        return 'writing_task_2';
    }

    /**
     * Render the GROUND-TRUTH LINGUISTIC SIGNALS block (Layer 4). Combines
     * LanguageTool grammar counts with LinguisticAnalyzer TTR / CEFR / cohesion
     * markers. Each measurement is computed deterministically; the LLM is
     * told NOT to re-count these features, only to use them as evidence.
     *
     * If LanguageTool is unreachable (Docker not running) the block still
     * renders with grammar/spelling marked "n/a" — TTR / CEFR / cohesion are
     * pure PHP and always available.
     */
    protected function buildGroundTruthSignalsBlock(string $essay): string
    {
        /** @var LinguisticAnalyzer $analyzer */
        $analyzer = app(LinguisticAnalyzer::class);
        $signals = $analyzer->analyze($essay);

        /** @var LanguageToolClient $lt */
        $lt = app(LanguageToolClient::class);
        $ltResult = $lt->check($essay);

        /** @var SyntacticComplexityAnalyzer $syntactic */
        $syntactic = app(SyntacticComplexityAnalyzer::class);
        $syntacticSignals = $syntactic->analyze($essay);
        $syntacticBlock = $syntactic->buildPromptBlock($syntacticSignals);

        $words = max(1, (int) $signals['word_count']);
        $ltLine = $ltResult['available']
            ? sprintf('%d grammar + %d spelling = %d errors (%.1f per 100 words) — Band 7+ typically <=3/100w, Band 8+ typically <=2/100w',
                $ltResult['grammar_errors'], $ltResult['spelling_errors'],
                $ltResult['grammar_errors'] + $ltResult['spelling_errors'],
                ($ltResult['grammar_errors'] + $ltResult['spelling_errors']) * 100 / $words)
            : 'n/a (LanguageTool service unreachable — fall back to your own assessment for GRA)';

        $cefr = $signals['cefr_distribution'];
        $cefrLine = sprintf(
            'A1 %.1f%% | A2 %.1f%% | B1 %.1f%% | B2 %.1f%% | C1 %.1f%% | C2/unknown %.1f%%',
            $cefr['A1'], $cefr['A2'], $cefr['B1'], $cefr['B2'], $cefr['C1'], $cefr['C2']
        );

        $cohesion = $signals['cohesion_markers'];
        $cohesionList = !empty($cohesion['found']) ? implode(', ', $cohesion['found']) : '(none detected)';

        $caveat = 'NOTE: The C2/unknown bucket includes both genuinely sophisticated vocabulary AND words outside our curated reference lists (proper nouns, topic-specific terms, less common forms). Treat a high C2 figure as "uses non-basic vocabulary" rather than as proof of mastery.';

        return <<<SIG

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
GROUND-TRUTH LINGUISTIC SIGNALS (computed, not estimated)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Use these factual measurements as evidence — do NOT re-count these features.

Word count: {$signals['word_count']} ({$signals['unique_word_count']} unique)
Grammar/spelling: {$ltLine}
Lexical diversity (TTR): {$signals['ttr']} (Band 7+ writing typically >= 0.50; below 0.40 indicates lexical poverty)
Average word length: {$signals['avg_word_length']} characters
CEFR vocabulary distribution: {$cefrLine}
Cohesion markers detected ({$cohesion['count']}): {$cohesionList}

{$syntacticBlock}

{$caveat}

SIG;
    }

    /**
     * Render the CALIBRATED REFERENCE EXAMPLES section for the writing prompt.
     * Returns an empty string if no examples are available so the prompt simply
     * omits the block.
     */
    protected function buildCalibratedExamplesBlock(string $answer, string $calibTaskType): string
    {
        // Feature-flagged so we can A/B compare with vs without few-shot.
        if (! config('services.calibration.few_shot_enabled', true)) {
            return '';
        }

        // L5-v1: pre-estimate the candidate band from deterministic linguistic
        // signals (LanguageTool error density + TTR + cohesion + word count)
        // and pass it to CalibrationService so the mid-bucket anchor lands
        // near the candidate's likely band — eliminates the dead-code path
        // where every essay got the same low/mid/high anchors regardless of
        // quality.
        $estimatedBand = $this->estimateBandFromSignals($answer);

        // L5-v1: 4 anchors (low + mid + high-student + ceiling) — the L4
        // 3-anchor setup left a 2-band gap between mid (~6.0) and the only
        // available ceiling (8.5), which encouraged the LLM to compress all
        // strong essays toward the visible mid. Adding a Band 7 student
        // anchor closes the gap and improves Band 7+ accuracy.
        $examples = $this->calibration->findSimilarExamples(
            $answer,
            $calibTaskType,
            4,
            $estimatedBand,
        );

        if ($examples->isEmpty()) {
            return '';
        }

        $blocks = $examples
            ->map(fn ($e) => $e->toFewShotBlock())
            ->implode("\n\n");

        return <<<FS

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CALIBRATED REFERENCE EXAMPLES (Cambridge-scored)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
The following essays were scored by certified Cambridge examiners.
Use them as concrete anchors when judging this candidate's response.
Compare features (overview, vocabulary range, error density, cohesion) against these benchmarks before assigning your scores.

{$blocks}

FS;
    }

    /**
     * Coarse band pre-estimator from deterministic linguistic signals. Used
     * ONLY to bias few-shot anchor selection (CalibrationService::findSimilarExamples)
     * — it never influences the final score. Returns a band in [4.0, 9.0] that
     * is intentionally noisy: the LLM is still the authoritative grader.
     *
     * Heuristic weights are calibrated against the Cambridge holdout set:
     *   - grammar errors per 100w: 0–2 → +0.5, 3–6 → 0, 7–10 → -0.5, >10 → -1.0
     *   - TTR: >=0.55 → +0.5, <0.40 → -0.5
     *   - cohesion markers: >=8 distinct → +0.5, <=2 → -0.5
     *   - C1+C2 share: >=25% → +0.5
     *   - word count: <min → -1.0 (Task 1<150, Task 2<250)
     * Base = 6.5 (median of the calibration pool).
     */
    protected function estimateBandFromSignals(string $essay): float
    {
        $analyzer = app(LinguisticAnalyzer::class);
        $signals  = $analyzer->analyze($essay);

        $lt = app(LanguageToolClient::class);
        $ltResult = $lt->check($essay);

        $band = 6.5;
        $words = max(1, (int) $signals['word_count']);

        // Grammar/spelling density (only when LT is available)
        if (($ltResult['available'] ?? false) === true) {
            $errPer100 = (($ltResult['grammar_errors'] ?? 0) + ($ltResult['spelling_errors'] ?? 0)) * 100 / $words;
            if ($errPer100 <= 2)       $band += 0.5;
            elseif ($errPer100 >= 11)  $band -= 1.0;
            elseif ($errPer100 >= 7)   $band -= 0.5;
        }

        $ttr = (float) ($signals['ttr'] ?? 0);
        if ($ttr >= 0.55)      $band += 0.5;
        elseif ($ttr < 0.40)   $band -= 0.5;

        $cohesion = (int) ($signals['cohesion_markers']['count'] ?? 0);
        if ($cohesion >= 8)    $band += 0.5;
        elseif ($cohesion <= 2) $band -= 0.5;

        $cefr = $signals['cefr_distribution'] ?? [];
        $advanced = (float) (($cefr['C1'] ?? 0) + ($cefr['C2'] ?? 0));
        if ($advanced >= 25.0) $band += 0.5;

        // Under-length penalty (rough — used only for anchor biasing)
        if ($words < 150) $band -= 1.0;

        // Clamp to plausible IELTS band range.
        return (float) max(4.0, min(9.0, round($band * 2) / 2));
    }

    /**
     * Get task-specific evaluation criteria
     */
    protected function getTaskSpecificCriteria($taskType): string
    {
        if (str_contains($taskType, 'Academic Task 1')) {
            return <<<CRITERIA
TASK 1 SPECIFIC REQUIREMENTS:
- Minimum 150 words
- Describe visual information (graphs, charts, diagrams, tables, processes)
- Overview statement required (most significant trends/features)
- Accurate data selection and reporting
- Appropriate comparison where relevant
- Clear organization: introduction, overview, body paragraphs
- No personal opinion required
CRITERIA;
        } elseif (str_contains($taskType, 'General Training Task 1')) {
            return <<<CRITERIA
TASK 1 LETTER REQUIREMENTS:
- Minimum 150 words
- Address all bullet points in the question
- Appropriate tone (formal/semi-formal/informal)
- Proper letter format (greeting, closing)
- Clear purpose statement
- Logical organization of ideas
CRITERIA;
        } else {
            return <<<CRITERIA
TASK 2 ESSAY REQUIREMENTS:
- Minimum 250 words
- Clear position/opinion throughout
- Address all parts of the question
- Well-developed arguments with examples
- Logical structure: introduction, body paragraphs, conclusion
- Balanced discussion if required by question
CRITERIA;
        }
    }

    /**
     * Calculate overall band score with examiner-calibrated logic.
     *
     * L5-v2: bias correction has been moved upstream to applyBiasCorrection()
     * (called inside scoreWriting / scoreSpeaking) so sub-scores and
     * overall_band stay in sync. This method now only computes the
     * descriptor-mean and runs the downward error-density nudge.
     */
    public function calculateOverallBand(array $scores): float
    {
        // For speaking: fluency, lexical, grammar, pronunciation
        if (isset($scores['fluency'])) {
            $ta = $scores['fluency'];
            $cc = $scores['lexical'];
            $lr = $scores['grammar'];
            $gra = $scores['pronunciation'];
        }
        // For writing: task_achievement, coherence_cohesion, lexical_resource, grammar
        else {
            $ta = $scores['task_achievement'];
            $cc = $scores['coherence_cohesion'];
            $lr = $scores['lexical_resource'];
            $gra = $scores['grammar'];
        }

        $average = round((($ta + $cc + $lr + $gra) / 4) * 2) / 2;

        // Stage 1 downward nudge only — Stage 2 upward shift now lives in
        // applyBiasCorrection() to keep sub-scores and overall consistent.
        $average = $this->calibrateScore($average, $scores);

        return $average;
    }

    /**
     * Single canonical bias-correction site (L5-v2).
     *
     * Applies the piecewise upward shift to EACH criterion and recomputes
     * overall_band as their mean, so the displayed report is internally
     * consistent (sub-scores average to overall). Stamps the raw uncorrected
     * values to {field}_raw for debugging / future regression work.
     *
     * The shift table is derived from the mini-tuned 10-essay holdout. It is
     * gated by services.calibration.bias_correction_enabled so we can A/B or
     * disable for non-mini providers (which have different bias profiles).
     */
    protected function applyBiasCorrection(array &$scoring, array $criterionFields): void
    {
        if (!config('services.calibration.bias_correction_enabled', true)) {
            return;
        }

        // Determine shift from the raw criterion mean — what the LLM intended
        // the overall to be, before its own rounding choice.
        $rawValues = [];
        foreach ($criterionFields as $f) {
            if (isset($scoring[$f])) {
                $rawValues[$f] = (float) $scoring[$f];
            }
        }
        if (empty($rawValues)) {
            return;
        }

        $rawMean = array_sum($rawValues) / count($rawValues);
        $shift = match (true) {
            $rawMean <= 6.0 => 1.0,
            $rawMean <= 6.5 => 0.5,
            default         => 0.0,
        };

        // Stamp raw mean + shift for traceability.
        $scoring['overall_band_raw'] = (float) round($rawMean * 2) / 2;
        $scoring['bias_shift']       = $shift;

        if ($shift === 0.0) {
            // No shift needed — still recompute overall from raw mean so
            // consumers see consistent overall/sub-score math.
            if (isset($scoring['overall_band'])) {
                $scoring['overall_band'] = (float) max(0.0, min(9.0, round($rawMean * 2) / 2));
            }
            return;
        }

        // Apply shift to each criterion + recompute overall.
        $correctedValues = [];
        foreach ($rawValues as $f => $v) {
            $corrected = max(0.0, min(9.0, round(($v + $shift) * 2) / 2));
            $scoring[$f . '_raw'] = $v;
            $scoring[$f]          = $corrected;
            $correctedValues[]    = $corrected;
        }

        $correctedMean = array_sum($correctedValues) / count($correctedValues);
        $scoring['overall_band'] = (float) max(0.0, min(9.0, round($correctedMean * 2) / 2));

        Log::info('Applied L5-v2 bias correction', [
            'raw_mean'       => round($rawMean, 2),
            'shift'          => $shift,
            'corrected_mean' => round($correctedMean, 2),
            'overall_band'   => $scoring['overall_band'],
        ]);
    }

    /**
     * High-band quality detector (L5-v2). Returns an additional shift to apply
     * on top of the baseline piecewise correction when ground-truth signals
     * indicate exceptional writing that mini's raw score is compressing.
     *
     * Five binary indicators, each tuned against Cambridge Band 7-8.5 essays:
     *   - TTR ≥ 0.55                    (lexical diversity)
     *   - Grammar+spelling ≤ 2/100w     (accuracy)
     *   - Cohesion markers ≥ 8 distinct (range of cohesive devices)
     *   - C1+C2 vocabulary share ≥ 30%  (lexical sophistication)
     *   - Word count ≥ 280              (developed response)
     *
     * 4-5 indicators = exceptional → +1.5 (lifts raw 6.0-6.5 to ~Band 8)
     * 3 indicators   = strong      → +1.0 (lifts raw 6.0-6.5 to ~Band 7.5)
     * ≤2 indicators  = no boost
     *
     * The detector silently degrades when LanguageTool is unavailable
     * (counts that indicator as not-met, so a weak signal blocks the boost
     * rather than over-rewarding it).
     */
    protected function detectHighBandBoost(string $essay): float
    {
        /** @var LinguisticAnalyzer $analyzer */
        $analyzer = app(LinguisticAnalyzer::class);
        $signals  = $analyzer->analyze($essay);

        /** @var LanguageToolClient $lt */
        $lt = app(LanguageToolClient::class);
        $ltResult = $lt->check($essay);

        $words = max(1, (int) ($signals['word_count'] ?? 0));
        $hits  = 0;

        if (($signals['ttr'] ?? 0) >= 0.55) $hits++;

        if (($ltResult['available'] ?? false) === true) {
            $errPer100 = (($ltResult['grammar_errors'] ?? 0) + ($ltResult['spelling_errors'] ?? 0)) * 100 / $words;
            if ($errPer100 <= 2.0) $hits++;
        }

        $cohesion = (int) ($signals['cohesion_markers']['count'] ?? 0);
        if ($cohesion >= 8) $hits++;

        $cefr     = $signals['cefr_distribution'] ?? [];
        $advanced = (float) (($cefr['C1'] ?? 0) + ($cefr['C2'] ?? 0));
        if ($advanced >= 30.0) $hits++;

        if ($words >= 280) $hits++;

        $boost = match (true) {
            $hits >= 4 => 1.5,
            $hits >= 3 => 1.0,
            default    => 0.0,
        };

        if ($boost > 0) {
            Log::info('High-band quality boost triggered', [
                'hits'  => $hits,
                'boost' => $boost,
                'ttr'   => $signals['ttr'] ?? null,
                'cohesion_count' => $cohesion,
                'cefr_advanced'  => round($advanced, 1),
                'word_count'     => $words,
            ]);
        }

        return $boost;
    }

    /**
     * Post-scoring adjustment — Stage 1 downward nudge only (L5-v2).
     *
     * Catches "polite over-scoring" of error-heavy essays at the Band 5.5–6.5
     * boundary: if grammar error density is high AND the average is in that
     * range, cap at 6.0.
     *
     * Stage 2 upward correction has moved to applyBiasCorrection() so sub-scores
     * and overall_band stay synchronised.
     */
    protected function calibrateScore(float $average, array $scores): float
    {
        if ($average >= 5.5 && $average <= 6.5) {
            $errorDensity = $scores['error_summary']['grammar_errors_per_100_words'] ?? 0;
            if ($errorDensity > 8 && $average > 6.0) {
                $average = 6.0;
            }
        }
        return $average;
    }
}