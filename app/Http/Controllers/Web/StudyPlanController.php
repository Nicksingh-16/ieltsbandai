<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class StudyPlanController extends Controller
{
    public function show(Test $test)
    {
        if ($test->user_id !== Auth::id()) abort(403);

        $scores   = $this->extractScores($test);
        $cacheKey = "study_plan_{$test->id}";

        $plan = Cache::remember($cacheKey, now()->addDays(30), function () use ($test, $scores) {
            return $this->generatePlan($test, $scores);
        });

        return view('pages.study-plan.show', compact('test', 'scores', 'plan'));
    }

    public function regenerate(Test $test)
    {
        if ($test->user_id !== Auth::id()) abort(403);
        Cache::forget("study_plan_{$test->id}");
        return redirect()->route('study-plan.show', $test)->with('success', 'Study plan regenerated.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function extractScores(Test $test): array
    {
        $module = $test->type ?? 'writing';

        if ($module === 'writing') {
            $ts = $test->testScores ?? collect();
            $criteria = [
                'task_response'      => $ts->where('criterion', 'task_response')->first()?->score ?? 0,
                'coherence_cohesion' => $ts->where('criterion', 'coherence_cohesion')->first()?->score ?? 0,
                'lexical_resource'   => $ts->where('criterion', 'lexical_resource')->first()?->score ?? 0,
                'grammar'            => $ts->where('criterion', 'grammatical_range_accuracy')->first()?->score ?? 0,
            ];
        } elseif ($module === 'speaking') {
            $meta = is_array($test->metadata) ? $test->metadata : json_decode($test->metadata ?? '{}', true);
            $criteria = [
                'fluency'       => $meta['fluency_coherence'] ?? 0,
                'vocabulary'    => $meta['lexical_resource'] ?? 0,
                'grammar'       => $meta['grammatical_range_accuracy'] ?? 0,
                'pronunciation' => $meta['pronunciation'] ?? 0,
            ];
        } else {
            $criteria = ['band' => $test->overall_band ?? 0];
        }

        return array_merge($criteria, [
            'overall' => (float) ($test->overall_band ?? 0),
            'module'  => $module,
        ]);
    }

    private function generatePlan(Test $test, array $scores): array
    {
        $module  = $scores['module'];
        $overall = $scores['overall'];

        $scoreLines = collect($scores)
            ->except(['module', 'overall'])
            ->map(fn($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . $v)
            ->implode(', ');

        $prompt = <<<PROMPT
You are an expert IELTS coach. Student completed IELTS {$module} band {$overall}. Scores: {$scoreLines}.

Create a 4-week improvement plan. Return JSON:
{
  "target_band": number,
  "focus_areas": ["area1","area2","area3"],
  "weekly_hours": number,
  "weeks": [
    {
      "week": 1,
      "theme": "string",
      "goals": ["goal1","goal2"],
      "daily_tasks": [
        {"day":"Mon-Tue","task":"description","duration":"30 min"},
        {"day":"Wed-Thu","task":"description","duration":"45 min"},
        {"day":"Fri-Sat","task":"description","duration":"60 min"},
        {"day":"Sun","task":"full practice test","duration":"90 min"}
      ],
      "resources": ["resource1","resource2"]
    }
  ],
  "tips": ["tip1","tip2","tip3"]
}
PROMPT;

        try {
            $response = OpenAI::chat()->create([
                'model'           => config('services.openai.model', 'gpt-4o'),
                'max_tokens'      => 2000,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => 'You are an expert IELTS coach. Return valid JSON only.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]);

            $data = json_decode($response->choices[0]->message->content, true);
            return $data ?? $this->fallbackPlan($overall);
        } catch (\Exception $e) {
            Log::error('StudyPlan generation failed', ['error' => $e->getMessage()]);
            return $this->fallbackPlan($overall);
        }
    }

    private function fallbackPlan(float $overall): array
    {
        return [
            'target_band'  => min(9.0, $overall + 0.5),
            'focus_areas'  => ['Task Response', 'Vocabulary Range', 'Grammar Accuracy'],
            'weekly_hours' => 8,
            'weeks'        => [],
            'tips'         => [
                'Practice with authentic Cambridge IELTS materials (Books 13–18)',
                'Record yourself speaking 2 minutes daily and listen back critically',
                'Read one IELTS-level article daily and write a 150-word summary',
            ],
        ];
    }
}
