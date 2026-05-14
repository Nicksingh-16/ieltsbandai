<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Writing & speaking scoring service.
 *
 * Provider routing (lives in LLMRouter, see app/Services/LLMRouter.php):
 *   - Pro Plus subscribers (users.model_tier='premium'): OpenRouter → openai/gpt-4o
 *   - Everyone else: OpenRouter → openai/gpt-4o-mini
 *   - Fallback chain: Groq Llama 3.3 70B → Gemini 2.5 Flash (round-robin keys)
 *
 * What this service owns:
 *   1. Prompt construction — official Cambridge band descriptors + L3
 *      few-shot calibration anchors retrieved via CalibrationService +
 *      L4 ground-truth signals block (LinguisticAnalyzer + LanguageToolClient
 *      + SyntacticComplexityAnalyzer).
 *   2. Post-LLM correction pipeline (L5-v6, current):
 *        a. applyBiasCorrection         — confidence-range cap + downward nudge
 *                                          for error-heavy optimism.
 *        b. enforceLengthCaps           — descriptor-based ceilings for short
 *                                          responses.
 *        c. enforceQuestionPartCoverage — TR cap when multi-part prompts have
 *                                          unaddressed sub-questions.
 *        d. recomputeOverallFromCriteria — keeps headline = mean(sub-scores).
 *   3. Validation & normalization of LLM JSON output (renaming
 *      task_response→task_achievement, etc., enforcing 0..9 range,
 *      stripping markdown fences, validating error spans).
 *
 * Calibration history:
 *   L5-v2: piecewise UPWARD bias shift (+1.0/+0.5) — REPLACED.
 *          Was based on the assumption that the LLM under-scored; empirically
 *          GPT-4-class models over-score Writing on language criteria
 *          (Hentschel et al. LAK '25, Mizumoto & Eguchi 2023), so the upward
 *          shift compounded the bias and produced "everyone gets 6.5".
 *   L5-v6: downward-only nudge for error-heavy + confidence-range cap.
 *          See applyBiasCorrection() docstring.
 */
class ScoringService
{
    /**
     * Pinned to every benchmark JSON so prompt regressions are diff-able.
     * Bump on any material change to the prompt body or retrieval strategy
     * (e.g. L3-v2 = topic-keyword-ranked few-shot, L4-v1 = LanguageTool block).
     */
    public const PROMPT_VERSION = 'L5-v6';

    protected CalibrationService $calibration;

    protected LLMRouter $router;

    public function __construct(?CalibrationService $calibration = null, ?LLMRouter $router = null)
    {
        $this->calibration = $calibration ?? app(CalibrationService::class);
        $this->router = $router ?? app(LLMRouter::class);
    }

    /**
     * Score IELTS speaking transcript using OpenAI API directly
     *
     * @param  string  $transcript  Combined transcript text
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

            if (! $content) {
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
                if (! isset($scoring[$field])) {
                    Log::error('Missing scoring field: '.$field);

                    return null;
                }
            }

            // Validate scores are numeric and in range
            foreach ($required as $field) {
                $score = (float) $scoring[$field];
                if ($score < 0 || $score > 9) {
                    Log::error('Invalid score range for '.$field.': '.$score);

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
            Log::error('Speaking scoring failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Score IELTS writing using OpenAI API
     *
     * @param  string  $answer  User's written response
     * @param  object  $question  Question object with type and content
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

            if (! $content) {
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
                if (! isset($scoring[$field])) {
                    Log::error('Missing scoring field: '.$field);

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
                    Log::error('Invalid score range for '.$field.': '.$score);

                    return null;
                }
                $scoring[$field] = $score;
            }

            $wordCount = str_word_count(trim($answer));
            $category = (string) ($question->category ?? '');
            $isTask2 = str_contains($category, 'task2');

            // L5-v6 post-LLM correction pipeline. Order matters:
            //   1. applyBiasCorrection — confidence-range cap + downward nudge
            //      for error-heavy essays the LLM rated optimistically.
            //   2. enforceLengthCaps   — hard descriptor-based ceilings for
            //      under-length responses (TR/LR/GRA can't be Band 7+ if the
            //      essay is too short to demonstrate the descriptor).
            //   3. enforceQuestionPartCoverage — if a multi-part Task 2 prompt
            //      had any sub-question unaddressed, push TR to descriptor
            //      Band 5 ("incompletely addressed").
            // Each step recomputes overall_band as the mean of the (capped)
            // sub-scores so the displayed math is always internally consistent.
            $this->applyBiasCorrection($scoring, [
                'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
            ]);

            $this->enforceLengthCaps($scoring, $wordCount, $isTask2);

            $this->enforceTopicRelevance($scoring);

            $this->enforceQuestionPartCoverage($scoring, $question);

            // Final recompute so callers and persisted JSON agree.
            $this->recomputeOverallFromCriteria($scoring, [
                'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
            ]);

            // ── Error pipeline (hybrid LLM + LanguageTool) ───────────────
            // The LLM produces judgement-based errors (vocabulary, cohesion,
            // collocation) with examiner-style explanations. LanguageTool
            // produces deterministic mechanical errors (grammar, spelling,
            // punctuation) with exact char offsets and verified replacements.
            // We merge both, prefer LT positions when both flag the same span,
            // then collapse repeated patterns into one entry with a count.
            $normalizedErrors = $this->normaliseLlmErrors($scoring['errors'] ?? []);
            $normalizedErrors = $this->validateAndCleanErrors($normalizedErrors, $answer);

            $ltErrors = $this->collectLanguageToolErrors($answer);
            $merged = $this->mergeErrorSources($normalizedErrors, $ltErrors);
            $scoring['errors'] = $this->groupRepeatedErrors($merged);

            return $scoring;

        } catch (\Exception $e) {
            Log::error('Writing scoring failed: '.$e->getMessage(), [
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
        if (! str_starts_with(trim($trimmed), '{') && preg_match('/\{.*\}/s', $trimmed, $m)) {
            $trimmed = $m[0];
        }

        return trim($trimmed);
    }

    /**
     * Validate and clean AI-detected errors to prevent false positives
     *
     * @param  array  $errors  Array of errors from AI
     * @param  string  $userAnswer  Original user's answer text
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
                        'category' => $error['category'] ?? 'unknown',
                    ]);

                    continue;
                }
            }

            $validated[] = $error;
        }

        Log::info('Error validation complete', [
            'original_count' => count($errors),
            'validated_count' => count($validated),
            'removed_count' => count($errors) - count($validated),
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
        if (! empty($words)) {
            /** @var SpeakingAcousticAnalyzer $analyzer */
            $analyzer = app(SpeakingAcousticAnalyzer::class);
            $signals = $analyzer->analyze($words);
            $acousticBlock = $analyzer->buildPromptBlock($signals);
        }

        return <<<PROMPT
You are a trained IELTS Speaking examiner with 15+ years of examining experience, certified by the British Council and IDP. You have been standardised against the official IELTS Speaking Band Descriptors (public version) and apply them with the same precision as a live examiner.

Your task: score the following speaking transcript against the four official IELTS Speaking criteria using the OFFICIAL PUBLIC BAND DESCRIPTORS reproduced verbatim below.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
OFFICIAL IELTS SPEAKING BAND DESCRIPTORS
(Public Version — British Council / IDP / Cambridge, verbatim)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CRITERION 1 — FLUENCY AND COHERENCE (FC)

Band 9:
• Fluent with only very occasional repetition or self-correction.
• Any hesitation that occurs is used only to prepare the content of the next utterance and not to find words or grammar.
• Speech is situationally appropriate and cohesive features are fully acceptable.
• Topic development is fully coherent and appropriately extended.

Band 8:
• Fluent with only very occasional repetition or self-correction.
• Hesitation may occasionally be used to find words or grammar, but most will be content related.
• Topic development is coherent, appropriate and relevant.

Band 7:
• Able to keep going and readily produce long turns without noticeable effort.
• Some hesitation, repetition and/or self-correction may occur, often mid-sentence and indicate problems with accessing appropriate language. However, these will not affect coherence.
• Flexible use of spoken discourse markers, connectives and cohesive features.

Band 6:
• Able to keep going and demonstrates a willingness to produce long turns.
• Coherence may be lost at times as a result of hesitation, repetition and/or self-correction.
• Uses a range of spoken discourse markers, connectives and cohesive features though not always appropriately.

Band 5:
• Usually able to keep going, but relies on repetition and self-correction to do so and/or on slow speech.
• Hesitations are often associated with mid-sentence searches for fairly basic lexis and grammar.
• Overuse of certain discourse markers, connectives and other cohesive features.
• More complex speech usually causes disfluency but simpler language may be produced fluently.

Band 4:
• Unable to keep going without noticeable pauses.
• Speech may be slow with frequent repetition.
• Often self-corrects.
• Can link simple sentences but often with repetitious use of connectives.
• Some breakdowns in coherence.

Band 3:
• Frequent, sometimes long, pauses occur while candidate searches for words.
• Limited ability to link simple sentences and go beyond simple responses to questions.
• Frequently unable to convey basic message.

Band 2:
• Lengthy pauses before nearly every word.
• Isolated words may be recognisable but speech is of virtually no communicative significance.

Band 1:
• Essentially none.
• Speech is totally incoherent.

CRITERION 2 — LEXICAL RESOURCE (LR)

Band 9:
• Total flexibility and precise use in all contexts.
• Sustained use of accurate and idiomatic language.

Band 8:
• Wide resource, readily and flexibly used to discuss all topics and convey precise meaning.
• Skilful use of less common and idiomatic items despite occasional inaccuracies in word choice and collocation.
• Effective use of paraphrase as required.

Band 7:
• Resource flexibly used to discuss a variety of topics.
• Some ability to use less common and idiomatic items and an awareness of style and collocation is evident though inappropriacies occur.
• Effective use of paraphrase as required.

Band 6:
• Resource sufficient to discuss topics at length.
• Vocabulary use may be inappropriate but meaning is clear.
• Generally able to paraphrase successfully.

Band 5:
• Resource sufficient to discuss familiar and unfamiliar topics but there is limited flexibility.
• Attempts paraphrase but not always with success.

Band 4:
• Resource sufficient for familiar topics but only basic meaning can be conveyed on unfamiliar topics.
• Frequent inappropriacies and errors in word choice.
• Rarely attempts paraphrase.

Band 3:
• Resource limited to simple vocabulary used primarily to convey personal information.
• Vocabulary inadequate for unfamiliar topics.

Band 2:
• Very limited resource. Utterances consist of isolated words or memorised utterances.
• Little communication possible without the support of mime or gesture.

Band 1:
• No resource bar a few isolated words.
• No communication possible.

CRITERION 3 — GRAMMATICAL RANGE AND ACCURACY (GRA)

Band 9:
• Structures are precise and accurate at all times, apart from 'mistakes' characteristic of native speaker speech.

Band 8:
• Wide range of structures, flexibly used.
• The majority of sentences are error free.
• Occasional inappropriacies and non-systematic errors occur. A few basic errors may persist.

Band 7:
• A range of structures flexibly used. Error-free sentences are frequent.
• Both simple and complex sentences are used effectively despite some errors. A few basic errors persist.

Band 6:
• Produces a mix of short and complex sentence forms and a variety of structures with limited flexibility.
• Though errors frequently occur in complex structures, these rarely impede communication.

Band 5:
• Basic sentence forms are fairly well controlled for accuracy.
• Complex structures are attempted but these are limited in range, nearly always contain errors and may lead to the need for reformulation.

Band 4:
• Can produce basic sentence forms and some short utterances are error-free.
• Subordinate clauses are rare and, overall, turns are short, structures are repetitive and errors are frequent.

Band 3:
• Basic sentence forms are attempted but grammatical errors are numerous except in apparently memorised utterances.

Band 2:
• No evidence of basic sentence forms.

Band 1:
• No rateable language unless memorised.

CRITERION 4 — PRONUNCIATION (PRON)

Band 9:
• Uses a full range of phonological features to convey precise and/or subtle meaning.
• Flexible use of features of connected speech is sustained throughout.
• Can be effortlessly understood throughout.
• Accent has no effect on intelligibility.

Band 8:
• Uses a wide range of phonological features to convey precise and/or subtle meaning.
• Can sustain appropriate rhythm. Flexible use of stress and intonation across long utterances, despite occasional lapses.
• Can be easily understood throughout.
• Accent has minimal effect on intelligibility.

Band 7:
• Displays all the positive features of band 6, and some, but not all, of the positive features of band 8.

Band 6:
• Uses a range of phonological features, but control is variable.
• Chunking is generally appropriate, but rhythm may be affected by a lack of stress-timing and/or a rapid speech rate.
• Some effective use of intonation and stress, but this is not sustained.
• Individual words or phonemes may be mispronounced but this causes only occasional lack of clarity.
• Can generally be understood throughout without much effort.

Band 5:
• Displays all the positive features of band 4, and some, but not all, of the positive features of band 6.

Band 4:
• Uses some acceptable phonological features, but the range is limited.
• Produces some acceptable chunking, but there are frequent lapses in overall rhythm.
• Attempts to use intonation and stress, but control is limited.
• Individual words or phonemes are frequently mispronounced, causing lack of clarity.
• Understanding requires some effort and there may be patches of speech that cannot be understood.

Band 3:
• Displays some features of band 2, and some, but not all, of the positive features of band 4.

Band 2:
• Uses few acceptable phonological features (possibly because sample is insufficient).
• Overall problems with delivery impair attempts at connected speech.
• Individual words and phonemes are mainly mispronounced and little meaning is conveyed.
• Often unintelligible.

Band 1:
• Can produce occasional individual words and phonemes that are recognisable, but no overall meaning is conveyed.
• Unintelligible.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
OFFICIAL EXAMINER NOTES (from the public descriptors)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
(i)  A candidate must FULLY FIT the positive features of the descriptor at a particular level. If they only partially fit Band N's descriptors, award Band N − 0.5 (or N − 1 if they fail multiple bullet points).
(ii) A candidate will be rated on their AVERAGE performance across all parts of the test (Part 1 + Part 2 + Part 3 combined transcript below).
(iii) "Band 7 PRON" and "Band 5 PRON" are defined relatively — they require ALL features of the lower band PLUS SOME (not all) of the next band up. Be strict: missing even one core feature pushes the score down.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CALIBRATION ANCHORS (Examiner Standardisation Reference)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Band 5.0 anchor: Speaks on familiar topics with simple fluency; mid-sentence searches for basic lexis/grammar; overuses discourse markers; complex speech causes disfluency but simpler language flows.
Band 6.0 anchor: Willing to speak at length; coherence wobbles from hesitation/repetition; mixes short and complex sentence forms with limited flexibility; vocabulary covers topics at length though sometimes inappropriate; rhythm variable.
Band 6.5 anchor: Sits between Band 6 and Band 7 — fits Band 6 on all four criteria PLUS displays SOME (not all) Band 7 features. Award 6.5 when candidate oscillates between descriptors.
Band 7.0 anchor: Keeps going and produces long turns without noticeable effort; flexible discourse markers; error-free sentences are FREQUENT (not just present); uses less common/idiomatic items with awareness of style; accent non-intrusive.
Band 8.0 anchor: Near-expert control; hesitation is content-driven not language-driven; MAJORITY of sentences are error-free; idiomatic vocabulary used with skill; rhythm and intonation flexible across long utterances.
{$acousticBlock}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EXAMINER SCORING RULES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1.  Score each criterion INDEPENDENTLY against its descriptor — do NOT average first.
2.  Half-bands (5.5, 6.5, 7.5, 8.5) are awarded when the candidate sits between two descriptors. The descriptor for a half-band is "all of Band N + some (not all) of Band N+1".
3.  Overall band = arithmetic mean of the 4 criteria, rounded to nearest 0.5 (per Note (ii) above, this is averaged across all parts of the test).
4.  "Fully fits" rule (Note (i)): if a candidate fits MOST but not ALL bullet points of a band, drop 0.5. If they fit fewer than half, drop a full band.
5.  Band 8+ requires the candidate to MEET the Band 8 descriptor on ALL 4 criteria. A single Band 6 criterion prevents Band 8 overall.
6.  Fillers ("uh", "um", "er", "like", "you know") and long pauses are FLUENCY signals. When the GROUND-TRUTH ACOUSTIC SIGNALS block above is present, USE its computed filler-per-100-words and pause-density figures as PRIMARY evidence — do NOT re-count from the transcript.
7.  >15 fillers per 100 words OR frequent mid-sentence pauses → FC ≤ 5.5 (matches the Band 5 descriptor "Hesitations are often associated with mid-sentence searches").
8.  Memorised or rehearsed-sounding responses that lack spontaneous development → FC ≤ 5.5 and LR ≤ 6.0 (Note: memorisation rules from Band 2 & 3 descriptors).
9.  Pronunciation: since you cannot HEAR the audio, infer PRON from (a) the acoustic signals block if present (sustained rhythm, varied pitch, intelligibility proxies), (b) the spelling/word choice quality of the auto-transcript (low ASR confidence proxies mispronunciation), and (c) any pronunciation_notes in the acoustic block. Do NOT default PRON to the average of the other 3 criteria — apply the descriptor explicitly.
10. If the transcript is very short (< 80 words across all 3 parts) or the candidate refuses to engage, cap all criteria at Band 5.0 (insufficient sample to award higher).
11. If the transcript shows no rateable language (random words, off-topic, foreign language), award Band 1 across the board.

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

        $task1MetadataInstructions = '';
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

        $task2Instructions = '';
        if (str_contains($taskType, 'Task 2')) {
            $task2Instructions = <<<'TASK2'
STRICT TASK 2 RULES:
- Clear position must be maintained throughout.
- At least 2 developed ideas required.
- Each idea must include explanation + example or consequence.
- If position is unclear or ideas underdeveloped -> TR <= 6.0.

TOPIC RELEVANCE CHECK (mandatory):
- Score the candidate's overall on-topicness as an integer 0-100 in the
  topic_relevance field. Be calibrated:
    100 = directly addresses the prompt with no off-topic digressions
    75  = addresses the prompt; one or two paragraphs drift into adjacent territory
    50  = partially relevant — half the response is on-topic, half meanders
    25  = tangential — touches the topic but mostly discusses something else
    0   = wholly off-topic (e.g. essay about climate change for a prompt about education)
- The post-LLM pipeline will cap Task Response based on this number per the
  Band 3 descriptor ("the prompt is tackled in a minimal way, or the answer
  is tangential"). Be honest; an off-topic Band 9-quality essay still scores
  Band 3 on TR per the official rules.

QUESTION-PART COVERAGE CHECK (mandatory):
- Read the prompt above and break it into its distinct sub-questions or
  instructions. A two-part prompt like "Why is this happening? Do you think
  this is a positive or negative development?" has TWO parts. A three-part
  prompt like "Discuss both views and give your own opinion" has THREE parts
  (view A, view B, your opinion).
- For EACH part, decide whether the candidate's response addresses it in
  any substantive way (not just a passing mention). Populate the
  question_parts[] array in the JSON output with one entry per part:
  { "part": "verbatim sub-question text", "addressed": true|false,
    "evidence": "1-line evidence from the essay if addressed, else why not" }
- Be honest. An essay that ignores half of a two-part question CANNOT score
  above Band 5 on Task Response per the official descriptor ("the main parts
  of the prompt are incompletely addressed"). The post-LLM pipeline will
  enforce this cap automatically based on question_parts[].addressed.
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
  "topic_relevance": 0,
  "question_parts": [
    { "part": "first sub-question or instruction", "addressed": true,  "evidence": "1-line evidence" },
    { "part": "second sub-question or instruction", "addressed": false, "evidence": "what was missing" }
  ],
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
        $cohesionList = ! empty($cohesion['found']) ? implode(', ', $cohesion['found']) : '(none detected)';

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
        $signals = $analyzer->analyze($essay);

        $lt = app(LanguageToolClient::class);
        $ltResult = $lt->check($essay);

        $band = 6.5;
        $words = max(1, (int) $signals['word_count']);

        // Grammar/spelling density (only when LT is available)
        if (($ltResult['available'] ?? false) === true) {
            $errPer100 = (($ltResult['grammar_errors'] ?? 0) + ($ltResult['spelling_errors'] ?? 0)) * 100 / $words;
            if ($errPer100 <= 2) {
                $band += 0.5;
            } elseif ($errPer100 >= 11) {
                $band -= 1.0;
            } elseif ($errPer100 >= 7) {
                $band -= 0.5;
            }
        }

        $ttr = (float) ($signals['ttr'] ?? 0);
        if ($ttr >= 0.55) {
            $band += 0.5;
        } elseif ($ttr < 0.40) {
            $band -= 0.5;
        }

        $cohesion = (int) ($signals['cohesion_markers']['count'] ?? 0);
        if ($cohesion >= 8) {
            $band += 0.5;
        } elseif ($cohesion <= 2) {
            $band -= 0.5;
        }

        $cefr = $signals['cefr_distribution'] ?? [];
        $advanced = (float) (($cefr['C1'] ?? 0) + ($cefr['C2'] ?? 0));
        if ($advanced >= 25.0) {
            $band += 0.5;
        }

        // Under-length penalty (rough — used only for anchor biasing)
        if ($words < 150) {
            $band -= 1.0;
        }

        // Clamp to plausible IELTS band range.
        return (float) max(4.0, min(9.0, round($band * 2) / 2));
    }

    /**
     * Get task-specific evaluation criteria
     */
    protected function getTaskSpecificCriteria($taskType): string
    {
        if (str_contains($taskType, 'Academic Task 1')) {
            return <<<'CRITERIA'
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
            return <<<'CRITERIA'
TASK 1 LETTER REQUIREMENTS:
- Minimum 150 words
- Address all bullet points in the question
- Appropriate tone (formal/semi-formal/informal)
- Proper letter format (greeting, closing)
- Clear purpose statement
- Logical organization of ideas
CRITERIA;
        } else {
            return <<<'CRITERIA'
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
     * Calculate overall band as the mean of the 4 criterion scores, rounded
     * to the nearest 0.5 (official IELTS rounding rule). All correction logic
     * — confidence-range cap, error-density nudge, length caps, question-part
     * caps — runs inside scoreWriting() before this is called, against the
     * sub-scores. This keeps the displayed overall = mean(displayed sub-scores).
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

        return round((($ta + $cc + $lr + $gra) / 4) * 2) / 2;
    }

    /**
     * Parse the upper bound from a "X.X – Y.Y" confidence range string the LLM
     * emits. Accepts en-dash, em-dash, or hyphen. Returns null on any oddness
     * so callers fall back to the un-clamped behaviour.
     */
    protected function parseConfidenceMax(mixed $range): ?float
    {
        if (! is_string($range) || trim($range) === '') {
            return null;
        }
        $normalised = str_replace(['–', '—'], '-', $range);
        if (! preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/', $normalised, $m)) {
            return null;
        }
        $max = (float) $m[2];

        return ($max > 0 && $max <= 9.0) ? $max : null;
    }

    /**
     * Single canonical bias-correction site (L5-v6).
     *
     * Prior L5-v2 applied a +1.0 / +0.5 UPWARD shift below rawMean 6.0 / 6.5
     * on the theory that the LLM under-scored. Empirically (and per the
     * 2024–25 LLM-essay-scoring literature, e.g. Hentschel et al. LAK '25)
     * GPT-4-class models *over-score* IELTS Writing by 0.5–1.0 band on the
     * language criteria, so the +1.0 shift compounded the bias. This version:
     *
     *   1. Computes the raw criterion mean.
     *   2. Applies a small DOWNWARD nudge for "error-heavy but optimistically
     *      rated" essays (high grammar/100w density combined with a mid-band
     *      rating). The official descriptors at Band 5 explicitly cite
     *      frequent grammatical errors that "may cause some difficulty"; at
     *      Band 6 errors "rarely impede communication". An LLM that gave 6+
     *      to an essay with 12+ errors/100w needs to be corrected down.
     *   3. Caps the result at the LLM's own confidence-range maximum, so the
     *      headline can never disagree with the range printed beneath it.
     *
     * Each criterion is shifted by the same amount as overall, so the report
     * stays internally consistent (sub-scores average to overall). Raw values
     * stamped to {field}_raw for benchmark deltas.
     *
     * Gated by services.calibration.bias_correction_enabled (env BIAS_CORRECTION_ENABLED).
     */
    protected function applyBiasCorrection(array &$scoring, array $criterionFields): void
    {
        if (! config('services.calibration.bias_correction_enabled', true)) {
            return;
        }

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

        // Step 1 — downward nudge for error-heavy optimism. The LLM is asked
        // to self-report grammar_errors_per_100_words; in practice it tends
        // to underestimate this, so the threshold is conservative.
        $errorsPer100 = (float) ($scoring['error_summary']['grammar_errors_per_100_words'] ?? 0);
        $shift = 0.0;
        if ($errorsPer100 >= 18 && $rawMean >= 5.5) {
            $shift = -1.0; // severe error density at any mid-band
        } elseif ($errorsPer100 >= 12 && $rawMean >= 6.0) {
            $shift = -0.5; // moderate-high errors but rated Band 6+
        }

        // Step 2 — clamp to the LLM's own confidence-range maximum. If the
        // model said "this is probably 5.0–6.0", any rawMean+shift above 6.0
        // is an internal contradiction; pull shift down further to match.
        $confMax = $this->parseConfidenceMax($scoring['band_confidence_range'] ?? null);
        if ($confMax !== null) {
            $confCap = (float) (round($confMax * 2) / 2);
            $postShiftMean = $rawMean + $shift;
            if ($postShiftMean > $confCap) {
                $shift = $confCap - $rawMean; // can be negative; that's the point
            }
        }

        // Stamp raw mean + shift for traceability.
        $scoring['overall_band_raw'] = (float) round($rawMean * 2) / 2;
        $scoring['bias_shift'] = $shift;

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
            $scoring[$f.'_raw'] = $v;
            $scoring[$f] = $corrected;
            $correctedValues[] = $corrected;
        }

        $correctedMean = array_sum($correctedValues) / count($correctedValues);
        $scoring['overall_band'] = (float) max(0.0, min(9.0, round($correctedMean * 2) / 2));

        Log::info('Applied L5-v6 bias correction', [
            'raw_mean' => round($rawMean, 2),
            'errors_per_100w' => $errorsPer100,
            'shift' => $shift,
            'corrected_mean' => round($correctedMean, 2),
            'overall_band' => $scoring['overall_band'],
        ]);
    }

    /**
     * Enforce IELTS descriptor-based hard ceilings for under-length responses.
     *
     * Official rule (post-2018): there is no fixed under-length penalty.
     * Instead, the descriptors at lower bands explicitly cite short length:
     *   • LR Band 3: "the response being significantly underlength"
     *   • GRA Band 3: "Length may be insufficient to provide evidence of
     *     control of sentence forms"
     *   • TR Band 9: requires ideas "fully extended" (impossible in 100 words)
     *
     * In practice human examiners pull TR/LR/GRA down for short scripts, but
     * GPT-4-class LLMs reliably miss this. The buckets below mirror the
     * descriptor ceiling for each length band:
     *
     *   Task 2 (250-word minimum)
     *     • < 100 words → TR ≤ 4.0, LR ≤ 5.0, GRA ≤ 5.0  (Band 3-4 territory)
     *     • 100–149     → TR ≤ 4.5, LR ≤ 5.5, GRA ≤ 5.5
     *     • 150–219     → TR ≤ 5.5
     *     • 220–249     → TR ≤ 6.0
     *     • ≥ 250       → no length cap
     *
     *   Task 1 (150-word minimum)
     *     • < 75 words  → TA ≤ 4.0, LR ≤ 5.0, GRA ≤ 5.0
     *     • 75–99       → TA ≤ 4.5, LR ≤ 5.5, GRA ≤ 5.5
     *     • 100–149     → TA ≤ 6.0
     *     • ≥ 150       → no length cap
     *
     * Hard cap from descriptors (≤20 words → Band 1) is enforced upstream by
     * the controller's min:50-char validation, so we don't need it here.
     */
    protected function enforceLengthCaps(array &$scoring, int $wordCount, bool $isTask2): void
    {
        if ($wordCount <= 0) {
            return;
        }

        $caps = [];
        if ($isTask2) {
            if ($wordCount < 100) {
                $caps = ['task_achievement' => 4.0, 'lexical_resource' => 5.0, 'grammar' => 5.0];
            } elseif ($wordCount < 150) {
                $caps = ['task_achievement' => 4.5, 'lexical_resource' => 5.5, 'grammar' => 5.5];
            } elseif ($wordCount < 220) {
                $caps = ['task_achievement' => 5.5];
            } elseif ($wordCount < 250) {
                $caps = ['task_achievement' => 6.0];
            }
        } else {
            if ($wordCount < 75) {
                $caps = ['task_achievement' => 4.0, 'lexical_resource' => 5.0, 'grammar' => 5.0];
            } elseif ($wordCount < 100) {
                $caps = ['task_achievement' => 4.5, 'lexical_resource' => 5.5, 'grammar' => 5.5];
            } elseif ($wordCount < 150) {
                $caps = ['task_achievement' => 6.0];
            }
        }

        if (empty($caps)) {
            return;
        }

        $applied = [];
        foreach ($caps as $field => $cap) {
            if (! isset($scoring[$field])) {
                continue;
            }
            $before = (float) $scoring[$field];
            if ($before > $cap) {
                $scoring[$field] = $cap;
                $applied[$field] = ['from' => $before, 'to' => $cap];
            }
        }

        if (! empty($applied)) {
            Log::info('Applied under-length descriptor caps', [
                'word_count' => $wordCount,
                'is_task2' => $isTask2,
                'caps' => $applied,
            ]);
        }
    }

    /**
     * Hard-cap Task Response based on the LLM's `topic_relevance` (0-100)
     * self-assessment. Mirrors the official descriptors:
     *
     *   ≥75 → no cap (response addresses the prompt)
     *   50-74 → TR ≤ 6.0 ("main parts addressed; some more fully than others")
     *   25-49 → TR ≤ 5.0 ("the prompt is incompletely addressed")
     *   <25  → TR ≤ 3.5 ("tackled in a minimal way / tangential")
     *
     * This catches the AI failure mode where a beautifully-written but
     * off-topic essay gets Band 7+ from the LLM because it can't help but
     * reward fluent prose. Real examiners pull TR straight to Band 3.
     *
     * Skipped if topic_relevance is missing or out of range — the LLM may
     * legitimately omit it on certain task types.
     */
    protected function enforceTopicRelevance(array &$scoring): void
    {
        $relevance = $scoring['topic_relevance'] ?? null;
        if (! is_numeric($relevance)) {
            return;
        }

        $relevance = (int) $relevance;
        if ($relevance < 0 || $relevance > 100) {
            return;
        }

        $cap = match (true) {
            $relevance >= 75 => null,
            $relevance >= 50 => 6.0,
            $relevance >= 25 => 5.0,
            default => 3.5,
        };

        if ($cap === null) {
            return;
        }

        if (isset($scoring['task_achievement']) && $scoring['task_achievement'] > $cap) {
            $before = (float) $scoring['task_achievement'];
            $scoring['task_achievement'] = $cap;

            Log::info('Applied topic-relevance cap', [
                'topic_relevance' => $relevance,
                'cap' => $cap,
                'ta_before' => $before,
                'ta_after' => $cap,
            ]);
        }
    }

    /**
     * Push TR/TA down to Band 5 ("the main parts of the prompt are
     * incompletely addressed") when a multi-part Task 2 prompt has any
     * sub-question unaddressed.
     *
     * The LLM is asked to populate scoring.parts_addressed[] alongside the
     * main scores; this method enforces the descriptor ceiling deterministically
     * because the model frequently scores high TR despite flagging missing
     * parts in its own explanation.
     */
    protected function enforceQuestionPartCoverage(array &$scoring, object $question): void
    {
        $parts = $scoring['question_parts'] ?? null;
        if (! is_array($parts) || count($parts) < 2) {
            return; // single-part prompt — nothing to enforce
        }

        $unaddressed = 0;
        foreach ($parts as $p) {
            if (! is_array($p)) {
                continue;
            }
            if (($p['addressed'] ?? true) === false) {
                $unaddressed++;
            }
        }

        if ($unaddressed === 0) {
            return;
        }

        // One missed part → cap TR at 5.5 (Band 5 "incompletely addressed").
        // Two or more missed → cap at 4.5 (Band 4 "tangential / minimal").
        $cap = $unaddressed >= 2 ? 4.5 : 5.5;

        if (isset($scoring['task_achievement']) && $scoring['task_achievement'] > $cap) {
            $before = (float) $scoring['task_achievement'];
            $scoring['task_achievement'] = $cap;

            Log::info('Applied question-part coverage cap', [
                'parts_total' => count($parts),
                'parts_unaddressed' => $unaddressed,
                'cap' => $cap,
                'ta_before' => $before,
                'ta_after' => $cap,
            ]);
        }
    }

    /**
     * Recompute overall_band as the mean of the 4 criterion scores, rounded
     * to the nearest 0.5 band (official IELTS rounding rule). Called as the
     * last step of the post-LLM pipeline so the displayed overall always
     * agrees with the displayed sub-scores.
     */
    protected function recomputeOverallFromCriteria(array &$scoring, array $criterionFields): void
    {
        $values = [];
        foreach ($criterionFields as $f) {
            if (isset($scoring[$f])) {
                $values[] = (float) $scoring[$f];
            }
        }
        if (count($values) !== count($criterionFields)) {
            return; // missing fields — leave overall alone
        }
        $mean = array_sum($values) / count($values);
        $scoring['overall_band'] = (float) max(0.0, min(9.0, round($mean * 2) / 2));
    }

    // ─── Error detection pipeline ────────────────────────────────────────────

    /**
     * Normalise the LLM-emitted errors[] array into our internal shape.
     * Filters out entries with empty text, lowercases category/severity,
     * tags each as `source: 'llm'` for downstream merging.
     */
    protected function normaliseLlmErrors(mixed $rawErrors): array
    {
        if (! is_array($rawErrors)) {
            return [];
        }

        $out = [];
        foreach ($rawErrors as $index => $error) {
            if (! is_array($error) || empty($error['text'])) {
                continue;
            }
            $type = ucfirst(strtolower((string) ($error['type'] ?? 'Grammar')));
            $out[] = [
                'id' => 'llm_'.($index + 1).'_'.substr(md5((string) $error['text']), 0, 6),
                'text' => trim((string) $error['text']),
                'type' => $type,
                'category' => $type,
                'severity' => strtolower((string) ($error['severity'] ?? 'medium')),
                'correction' => (string) ($error['correction'] ?? ''),
                'explanation' => (string) ($error['explanation'] ?? ''),
                'source' => 'llm',
            ];
        }

        return $out;
    }

    /**
     * Run LanguageTool against the essay and map each match into our error
     * shape. Returns an empty array if LT is unavailable (Docker not running,
     * etc.) — the LLM errors still carry the report.
     *
     * Categorisation:
     *   - rule.category.id contains TYPO/SPELL → "Vocabulary" (per IELTS LR
     *     descriptors, spelling errors live under Lexical Resource)
     *   - PUNCTUATION → "Punctuation"
     *   - everything else (GRAMMAR / TYPOGRAPHY / STYLE / etc.) → "Grammar"
     */
    protected function collectLanguageToolErrors(string $answer): array
    {
        try {
            /** @var LanguageToolClient $lt */
            $lt = app(LanguageToolClient::class);
            $result = $lt->check($answer);
        } catch (\Throwable $e) {
            return []; // never let LT failures break scoring
        }

        if (($result['available'] ?? false) !== true) {
            return [];
        }

        $matches = is_array($result['raw'] ?? null) ? $result['raw'] : [];
        $out = [];

        foreach ($matches as $i => $m) {
            $offset = (int) ($m['offset'] ?? -1);
            $length = (int) ($m['length'] ?? 0);
            if ($offset < 0 || $length <= 0 || ($offset + $length) > strlen($answer)) {
                continue;
            }

            $text = substr($answer, $offset, $length);
            if (trim($text) === '') {
                continue;
            }

            $catId = strtoupper((string) ($m['rule']['category']['id'] ?? ''));
            $type = match (true) {
                str_contains($catId, 'TYPO') || str_contains($catId, 'SPELL') => 'Vocabulary',
                str_contains($catId, 'PUNCTUATION') => 'Punctuation',
                default => 'Grammar',
            };

            $replacement = $m['replacements'][0]['value'] ?? '';
            $message = (string) ($m['message'] ?? '');

            $out[] = [
                'id' => 'lt_'.($i + 1).'_'.substr(md5($catId.$offset.$text), 0, 6),
                'text' => $text,
                'type' => $type,
                'category' => $type,
                'severity' => 'medium', // LT doesn't grade severity; downgrade to medium
                'correction' => (string) $replacement,
                'explanation' => $message,
                'source' => 'languagetool',
                'offset' => $offset,
                'length' => $length,
            ];
        }

        return $out;
    }

    /**
     * Merge LLM and LanguageTool error lists. When both flag the same span
     * (case-insensitive text match), keep LT's exact position+correction
     * but carry over the LLM's richer examiner-style explanation. Result is
     * order-stable: LLM-first since they tend to be the more diagnostic
     * (vocabulary, cohesion); LT-only entries appended after.
     */
    protected function mergeErrorSources(array $llmErrors, array $ltErrors): array
    {
        $byKey = static fn (array $e) => strtolower(trim($e['text']));

        $llmKeys = [];
        foreach ($llmErrors as $e) {
            $llmKeys[$byKey($e)] = true;
        }

        $merged = $llmErrors;

        foreach ($ltErrors as $lt) {
            $k = $byKey($lt);
            if (isset($llmKeys[$k])) {
                // Same-span overlap — find the matching LLM entry and enrich
                // it with LT's verified position + replacement.
                foreach ($merged as &$llm) {
                    if ($byKey($llm) !== $k) {
                        continue;
                    }
                    $llm['offset'] = $llm['offset'] ?? $lt['offset'];
                    $llm['length'] = $llm['length'] ?? $lt['length'];
                    if (empty($llm['correction']) && ! empty($lt['correction'])) {
                        $llm['correction'] = $lt['correction'];
                    }
                    $llm['source'] = 'llm+languagetool';
                    break;
                }
                unset($llm); // break the by-ref binding

                continue;
            }
            $merged[] = $lt;
        }

        return $merged;
    }

    /**
     * Collapse repeated error patterns into one entry per text. Used because
     * a candidate who misspells "peoples" five times should see one error
     * card with a "Repeated 5 times" badge, not five identical cards. We
     * keep the first occurrence's data and add `repeated_count`.
     */
    protected function groupRepeatedErrors(array $errors): array
    {
        $byText = [];
        $order = [];

        foreach ($errors as $e) {
            $key = strtolower(trim((string) ($e['text'] ?? '')));
            if ($key === '') {
                continue;
            }
            if (! isset($byText[$key])) {
                $byText[$key] = $e;
                $byText[$key]['repeated_count'] = 1;
                $order[] = $key;
            } else {
                $byText[$key]['repeated_count']++;
                // Promote severity if a later occurrence is graver.
                $rank = ['low' => 1, 'medium' => 2, 'high' => 3];
                if (($rank[$e['severity'] ?? 'medium'] ?? 2) > ($rank[$byText[$key]['severity'] ?? 'medium'] ?? 2)) {
                    $byText[$key]['severity'] = $e['severity'];
                }
            }
        }

        return array_values(array_map(fn ($k) => $byText[$k], $order));
    }
}
