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

        $response = Http::timeout(60)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'You are an IELTS examiner. Return JSON only.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
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
Evaluate the IELTS Writing response.

Return JSON ONLY.

DO NOT include HTML.
DO NOT include positions.
DO NOT rewrite the essay.

Provide:
- Band scores (0–9, 0.5 steps)
- Examiner-style feedback
- Strengths & improvements
- Errors using EXACT incorrect text

ERROR FORMAT:
{
  "text": "exact wrong phrase",
  "type": "grammar|vocabulary|cohesion",
  "severity": "low|medium|high",
  "correction": "correct form",
  "explanation": "IELTS-style explanation"
}

ESSAY:
{$answer}
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