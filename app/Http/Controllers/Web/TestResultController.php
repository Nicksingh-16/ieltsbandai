<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;




class TestResultController extends Controller
{
    public function show(Test $test)
    {
        // Ensure user can only view their own tests
        if ($test->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $test->load([
            'testScores',
            'audioFiles' => function($query) {
                $query->orderBy('created_at');
            },
            'testQuestions' => function($query) {
                $query->orderBy('part')->with('question');
            }
        ]);

        // Prepare scores array for the view
        if ($test->type === 'writing') {
            // For writing tests, extract from result JSON
            $result = json_decode($test->result, true) ?? [];
            
            $scores = [
                'task_achievement' => $result['task_achievement'] ?? 0,
                'coherence_cohesion' => $result['coherence_cohesion'] ?? 0,
                'lexical_resource' => $result['lexical_resource'] ?? 0,
                'grammar' => $result['grammar'] ?? 0,
                'overall_band' => $result['overall_band'] ?? $test->overall_band ?? 0,
            ];
            
            $feedback = $result['feedback'] ?? '';
            $strengths = $result['strengths'] ?? [];
            $improvements = $result['improvements'] ?? [];
            $word_count = $result['word_count'] ?? 0;
            $errors = $result['errors'] ?? [];
            $unpositioned_errors = $result['unpositioned_errors'] ?? [];
            $band_explanations = $result['band_explanations'] ?? [];
            $summary = $result['summary'] ?? null;
            $originalAnswer = $result['original_answer'] ?? '';
            $highlightedEssay = $result['highlightedEssay'] ?? htmlspecialchars($originalAnswer);
            
            // Generate task info
            $task_info = $this->getTaskInfo($test->category ?? '');
            
        } else {
            // For speaking/listening tests, extract from testScores relationship
            $scores = [
                'overall_band' => $test->overall_band ?? 0,
            ];
            
            // Group scores by criteria
            foreach ($test->testScores as $testScore) {
                $scores[$testScore->criteria] = $testScore->band_score;
            }
            
            $feedback = $test->feedback ?? '';
            $strengths = [];
            $improvements = [];
            $word_count = null;
            $errors = [];
            $originalAnswer = null;
            $highlightedEssay = null;
            $task_info = ['title' => 'Speaking/Listening Test'];
        }

        return view('pages.results.index', compact(
            'test',
            'scores',
            'feedback',
            'strengths',
            'improvements',
            'word_count',
            'errors',
            'unpositioned_errors',
            'band_explanations',
            'summary',
            'originalAnswer',
            'highlightedEssay',
            'task_info'
        ));
    }

    /**
     * Get task information based on category
     */
    protected function getTaskInfo($category)
    {
        // Determine task type
        if (str_contains($category, 'academic_task1')) {
            $taskType = 'task1_academic';
        } elseif (str_contains($category, 'academic_task2')) {
            $taskType = 'task2_academic';
        } elseif (str_contains($category, 'general_task1')) {
            $taskType = 'task1_general';
        } elseif (str_contains($category, 'general_task2')) {
            $taskType = 'task2_general';
        } else {
            $taskType = 'unknown';
        }

        $info = [
            'type' => $taskType,
            'time_limit' => str_contains($category, 'task1') ? 20 : 40,
            'word_limit' => str_contains($category, 'task1') ? 150 : 250,
        ];

        switch ($taskType) {
            case 'task1_academic':
                $info['title'] = 'Academic Task 1';
                $info['description'] = 'Describe visual information (graphs, charts, diagrams, tables)';
                break;

            case 'task2_academic':
                $info['title'] = 'Academic Task 2';
                $info['description'] = 'Write an essay in response to a point of view, argument or problem';
                break;

            case 'task1_general':
                $info['title'] = 'General Training Task 1';
                $info['description'] = 'Write a letter (formal, semi-formal, or informal)';
                break;

            case 'task2_general':
                $info['title'] = 'General Training Task 2';
                $info['description'] = 'Write an essay in response to a point of view, argument or problem';
                break;

            default:
                $info['title'] = 'Writing Test';
                $info['description'] = '';
        }

        return $info;
    }

}
