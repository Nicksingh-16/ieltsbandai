<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WritingScoringService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
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

        $response = Http::timeout(45)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'You are an IELTS examiner. Return JSON only. No markdown. No code blocks.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 3000,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (!$response->successful()) {
            Log::error('OpenAI error', ['body' => $response->body()]);
            return null;
        }

        $content = $response->json('choices.0.message.content');
        if (!$content) {
            Log::error('OpenAI empty response');
            return null;
        }

        if (env('IELTS_DEBUG')) {
            Log::debug('🧠 GPT raw response', ['content' => $content]);
        }

        $scoring = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid GPT JSON', ['raw' => $content]);
            return null;
        }

        // Required fields
        foreach (['task_achievement','coherence_cohesion','lexical_resource','grammar','feedback','errors'] as $f) {
            if (!isset($scoring[$f])) return null;
        }

        // Normalize scores
        foreach (['task_achievement','coherence_cohesion','lexical_resource','grammar'] as $f) {
            $scoring[$f] = round(((float)$scoring[$f]) * 2) / 2;
        }

        // Normalize errors
        $errors = [];
        foreach ($scoring['errors'] as $i => $e) {
            $errors[] = [
                'id'          => 'error_' . ($i + 1),
                'text'        => $e['text'] ?? '',
                'type'        => $e['type'] ?? 'grammar',
                'category'    => $e['category'] ?? ($e['type'] ?? 'grammar'),
                'severity'    => $e['severity'] ?? 'medium',
                'correction'  => $e['correction'] ?? '',
                'explanation' => $e['explanation'] ?? '',
            ];
        }

        $scoring['errors'] = $errors;
        $scoring['annotated_answer_html'] = null; // 🔒 NEVER from GPT

        return $scoring;

    } catch (\Throwable $e) {
        Log::error('Writing scoring crashed', ['msg' => $e->getMessage()]);
        return null;
    }
}



    // ... rest of the methods remain the same ...

   protected function buildWritingScoringPrompt(string $answer, $question): string
{
    $questionContent = $question->content ?? '';
    $taskType = $this->determineTaskType($question->category ?? '');

    return <<<PROMPT
You are a certified IELTS examiner. Score the candidate's writing and return ONLY valid JSON — no markdown, no code blocks.

TASK TYPE: {$taskType}
QUESTION: {$questionContent}

CANDIDATE ANSWER:
{$answer}

Use official IELTS band descriptors. Scores must be 0–9 in 0.5 increments.

Return this EXACT JSON structure (no extra keys, no missing keys):
{
  "task_achievement": <number>,
  "coherence_cohesion": <number>,
  "lexical_resource": <number>,
  "grammar": <number>,
  "feedback": "<2-3 sentence overall examiner comment in formal tone>",
  "summary": {
    "estimated_band": <number>,
    "strength": "<single most notable strength, 10 words max>",
    "weakness": "<single most critical weakness, 10 words max>",
    "tip": "<one actionable improvement tip, 12 words max>"
  },
  "band_explanations": {
    "task_achievement": {"why": "<why this band score, 1-2 sentences>", "tip": "<how to raise it by 0.5>"},
    "coherence_cohesion": {"why": "<why this band score, 1-2 sentences>", "tip": "<how to raise it by 0.5>"},
    "lexical_resource": {"why": "<why this band score, 1-2 sentences>", "tip": "<how to raise it by 0.5>"},
    "grammar": {"why": "<why this band score, 1-2 sentences>", "tip": "<how to raise it by 0.5>"}
  },
  "strengths": ["<strength 1>", "<strength 2>", "<strength 3>"],
  "improvements": ["<improvement area 1>", "<improvement area 2>", "<improvement area 3>"],
  "examiner_comments": ["<high-level insight 1 an experienced examiner would note>", "<high-level insight 2>"],
  "topic_vocabulary": ["<advanced topic word 1>", "<word 2>", "<word 3>", "<word 4>", "<word 5>", "<word 6>"],
  "error_summary": {"grammar": <count>, "vocabulary": <count>, "cohesion": <count>, "punctuation": <count>},
  "errors": [
    {
      "text": "<EXACT verbatim text copied from the candidate answer>",
      "type": "grammar",
      "category": "grammar",
      "severity": "high",
      "correction": "<corrected version>",
      "explanation": "<IELTS-style explanation of why this is wrong>"
    }
  ]
}

Rules for errors array:
- text field MUST be copied verbatim from the candidate answer (used for highlighting)
- Include 5–12 errors covering grammar, vocabulary, cohesion, and punctuation
- type and category must both be one of: grammar, vocabulary, cohesion, punctuation
- severity must be: low, medium, or high
PROMPT;
}


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

    public function calculateOverallBand(array $scores): float
    {
        $average = (
            $scores['task_achievement'] +
            $scores['coherence_cohesion'] +
            $scores['lexical_resource'] +
            $scores['grammar']
        ) / 4;

        return round($average * 2) / 2;
    }
}