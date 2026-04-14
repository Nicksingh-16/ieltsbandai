<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

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
            $response = OpenAI::chat()->create([
                'model'           => config('services.openai.model', 'gpt-4o'),
                'max_tokens'      => 400,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => 'You are an IELTS examiner. Evaluate the provided essay snippet and return JSON only.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Evaluate this IELTS Writing {$taskType} excerpt (partial — first check only) and return JSON with keys:
- task_response_band: number 1-9 (half bands ok, e.g. 6.5)
- lexical_resource_band: number 1-9
- task_response_comment: one short sentence on task achievement
- lexical_comment: one short sentence on vocabulary
- top_improvement: single most important improvement to make
- word_count_ok: boolean (task1 needs 150+, task2 needs 250+)

Essay ({$wordCount} words):
{$essay}",
                    ],
                ],
            ]);

            $data = json_decode($response->choices[0]->message->content, true);

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

        } catch (\Exception $e) {
            Log::error('WritingChecker failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Analysis failed. Please try again.'], 500);
        }
    }
}
