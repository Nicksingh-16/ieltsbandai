<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\LLMRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WritingCheckerController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'essay'     => 'required|string|min:50|max:1000',
            'task_type' => 'required|in:task1,task2',
        ]);

        $essay    = $request->essay;
        $taskType = $request->task_type;
        $wordCount = str_word_count($essay);

        try {
            // Tight schema with concrete example — Groq (llama-3.3-70b)
            // adheres better when the EXACT shape is shown rather than
            // described in prose.
            $minWords = $taskType === 'task1' ? 150 : 250;
            $systemPrompt = 'You are an IELTS examiner. Return a SINGLE JSON object with EXACTLY these keys and no others. No prose, no markdown, no code fences.';
            $userPrompt = "Evaluate this IELTS Writing {$taskType} excerpt and respond with JSON in this exact shape:

{
  \"task_response_band\": 6.5,
  \"lexical_resource_band\": 6.5,
  \"task_response_comment\": \"one short sentence\",
  \"lexical_comment\": \"one short sentence\",
  \"top_improvement\": \"single most important improvement\",
  \"word_count_ok\": true
}

Rules:
- Bands are numbers 1–9, half-bands allowed (5.5, 6.0, 6.5…)
- word_count_ok is true if the essay has at least {$minWords} words (it has {$wordCount}).
- All keys are mandatory.

Essay:
{$essay}";

            $body = app(LLMRouter::class)
                ->withContext(auth()->id(), null, 'writing_checker_seo')
                ->chatCompletion([
                    'max_tokens'  => 400,
                    'temperature' => 0.1,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userPrompt],
                    ],
                ]);
            $content = $body['choices'][0]['message']['content'] ?? '';
            // Strip code fences if present; also extract the first {...} block
            // in case the model added a stray sentence.
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($content));
            if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
                $content = $m[0];
            }
            $data = json_decode($content, true) ?: [];

            return response()->json([
                'success'              => true,
                'word_count'           => $wordCount,
                'task_response_band'   => $data['task_response_band'] ?? null,
                'lexical_band'         => $data['lexical_resource_band'] ?? null,
                'task_comment'         => $data['task_response_comment'] ?? null,
                'lexical_comment'      => $data['lexical_comment'] ?? null,
                'top_improvement'      => $data['top_improvement'] ?? null,
                'word_count_ok'        => $data['word_count_ok'] ?? false,
                'requires_signup'      => !auth()->check(), // full feedback requires account
            ]);

        } catch (\Throwable $e) {
            Log::error('WritingChecker failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Analysis failed. Please try again.'], 500);
        }
    }
}
