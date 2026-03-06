<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScoringService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Score IELTS speaking transcript using OpenAI API directly
     * 
     * @param string $transcript Combined transcript text
     * @return array|null Scoring data or null on failure
     */
    public function scoreSpeaking(string $transcript): ?array
    {
        try {
            $prompt = $this->buildSpeakingScoringPrompt($transcript);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert IELTS speaking examiner. You must respond ONLY with valid JSON, no other text.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('OpenAI response missing content');
                return null;
            }

            // Parse JSON response
            $scoring = json_decode($content, true);

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
    public function scoreWriting(string $answer, $question): ?array
    {
        try {
            $prompt = $this->buildWritingScoringPrompt($answer, $question);

            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert IELTS writing examiner certified by Cambridge. Evaluate writing samples according to official IELTS band descriptors. You must respond ONLY with valid JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error for writing scoring', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('OpenAI response missing content');
                return null;
            }

            $scoring = json_decode($content, true);

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
     * Build the scoring prompt for speaking AI evaluation
     */
    protected function buildSpeakingScoringPrompt(string $transcript): string
    {
        return <<<PROMPT
Role: Senior IELTS Examiner (10+ years experience).
Objective: Evaluate the transcript with strict, conservative, examiner-accurate judgment.
Scoring Philosophy:
- Band 8–9 scores are RARE and must be awarded ONLY in exceptional cases (near-native, effortless control).
- If there is ANY doubt, assign the LOWER band.
- Most candidates fall between Band 5.5–6.5.
- Trust over encouragement. Accuracy > range.

Transcript:
{$transcript}

Provide your evaluation in JSON format:
{
  "fluency": X.X,
  "lexical": X.X,
  "grammatical_range_accuracy": X.X,
  "pronunciation": X.X,
  "overall_band": X.X,
  "band_confidence_range": "X.X – X.X",
  "examiner_comments": [
    "Examiner-style comments explaining score limitations."
  ],
  "error_summary": {
    "grammar_errors_per_100_words": X,
    "repeated_errors": ["..."]
  },
  "metadata": {
    "fillers_detected": X,
    "repetition_flags": true|false,
    "pronunciation_notes": ["..."]
  }
}

STRICT HIGH-BAND GATES (🚫 Band 7+ GATE):
- Position/Response must be clear, nuanced, and consistently maintained.
- Ideas must be fully developed beyond obvious/memorised points.
- NO repeated grammar errors.
- Vocabulary is natural/flexible, NOT forced or memorised.
- If ANY generic ideas, slight misuse of vocab, or repeated minor errors are present -> CAP Overall Band at 6.5.

STRICT SPEAKING RULES:
- Fluency matters MORE than complexity.
- Excessive fillers ("uh", "um", "you know") -> Fluency <= 6.0.
- Repetition or reformulation of the same idea -> Coherence/Fluency <= 6.0.
- Pronunciation: Causes listener effort -> <= 6.0; Inconsistent -> <= 6.5.
- Memorised answers or rehearsed phrases must be penalized.

Return ONLY valid JSON.
PROMPT;
    }

    /**
     * Build the scoring prompt for writing AI evaluation based on task type
     */
    protected function buildWritingScoringPrompt(string $answer, $question): string
    {
        $questionContent = $question->content ?? '';
        $category = $question->category ?? '';
        $metadata = json_decode($question->metadata ?? '{}', true);
        
        // Determine task type and specific criteria
        $taskType = $this->determineTaskType($category);
        $specificCriteria = $this->getTaskSpecificCriteria($taskType);

        $task1MetadataInstructions = "";
        if (str_contains($taskType, 'Task 1') && str_contains($taskType, 'Academic')) {
            $metaJson = json_encode($metadata, JSON_PRETTY_PRINT);
            $task1MetadataInstructions = <<<META
CRITICAL - DATA SOURCES:
The following is the ground truth metadata for the graph/chart. 
Evaluate ONLY based on this data. Do NOT interpret or hallucinate from any external knowledge.
METADATA:
{$metaJson}

STRICT TASK 1 RULES:
- Overview is mandatory. No overview or unclear overview -> TA <= 5.5.
- Correct overview + major trends -> TA >= 6.5.
- Penalize incorrect trend descriptions.
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
Role: Senior IELTS Examiner (10+ years experience).
Objective: Evaluate the response ACCURATELY based on official IELTS public band descriptors.
Scoring Philosophy:
- Award Band 7+ if the user meets the descriptors (e.g., "frequent error-free sentences", "range of complex structures").
- Do NOT artificially cap scores. If the English is good, award the score.
- Be fair: isolated errors should not pull down a high-quality essay significantly.

TASK TYPE: {$taskType}
QUESTION:
{$questionContent}

USER'S ANSWER:
{$answer}

{$specificCriteria}
{$task1MetadataInstructions}
{$task2Instructions}

HIGH-BAND GATES (Band 7+ Guidance):
- Band 7 requires: "frequent error-free sentences", "good control of grammar/punctuation but may make a few errors".
- Band 8 requires: "wide range of structures", "the majority of sentences are error-free", "occasional inaccuracies".
- Key difference: Band 6 has "mix of simple and complex forms", Band 7 has "variety of complex structures".
- Vocabulary: Band 7 allows "occasional errors in word choice".

SCORING RULES:
- Accuracy > Range (but do not penalize attempts at complexity too harshly).
- Deduct for REPEATED errors that impede communication.
- Reward natural language and clear progression of ideas.

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
  "examiner_comments": ["Examiner-style comments explaining specific scoring limitations based on highlighted patterns."],
  "band_explanations": {
    "task_achievement": {
      "why": "Specific examiner explanation of why this band was awarded for Task Achievement.",
      "tip": "Actionable tip to improve this criterion."
    },
    "coherence_cohesion": {
      "why": "Specific examiner explanation of why this band was awarded for Coherence & Cohesion.",
      "tip": "Actionable tip to improve this criterion."
    },
    "lexical_resource": {
      "why": "Specific examiner explanation of why this band was awarded for Lexical Resource.",
      "tip": "Actionable tip to improve this criterion."
    },
    "grammar": {
      "why": "Specific examiner explanation of why this band was awarded for Grammatical Range & Accuracy.",
      "tip": "Actionable tip to improve this criterion."
    }
  },
  "error_summary": {
    "grammar_errors_per_100_words": 0,
    "repeated_errors": ["..."]
  },
  "band_9_rewrite": "Full Band 9 model response.",
  "topic_vocabulary": ["5–7 topic-specific advanced but natural words"],
  "errors": [
    {
      "text": "EXACT incorrect text from user essay",
      "type": "Grammar|Vocabulary|Punctuation|Cohesion",
      "severity": "low|medium|high",
      "correction": "Corrected version",
      "explanation": "Senior examiner explanation of WHY this is an error and HOW it affects the band."
    }
  ]
}

MANDATORY - BAND EXPLANATIONS:
- You MUST generate detailed "why" and "tip" for EACH of the 4 criteria.
- The "why" field must explain the specific reasons for the band score (2-3 sentences).
- The "tip" field must provide actionable advice to improve (1-2 sentences).
- DO NOT use generic placeholders like "pending" or "rationale pending".
- Example for Task Achievement Band 6.0:
  "why": "The response addresses all parts of the task with a clear overview and main trends identified. However, the overview lacks specificity and some data points are not fully developed."
  "tip": "Provide more specific details in your overview and ensure all data points are fully supported with accurate figures."

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
     * Calculate overall band score with examiner-calibrated logic
     * Average of 4 criteria, rounded to nearest 0.5.
     * Capped at 6.0 if any criterion <= 5.0.
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

        // Apply Calibration Adjustment
        $average = $this->calibrateScore($average, $scores);

        if (min($ta, $cc, $lr, $gra) <= 5.0) {
            return min($average, 6.0);
        }

        return $average;
    }

    /**
     * Post-scoring adjustment layer to maintain examiner realism.
     */
    protected function calibrateScore(float $average, array $scores): float
    {
        // Example: Systematic over-scoring correction at Bands 5.5-6.5
        if ($average >= 5.5 && $average <= 6.5) {
            // If error density is high but score is high, nudge down
            $errorDensity = $scores['error_summary']['grammar_errors_per_100_words'] ?? 0;
            if ($errorDensity > 8 && $average > 6.0) {
                return 6.0;
            }
        }

        return $average;
    }
}