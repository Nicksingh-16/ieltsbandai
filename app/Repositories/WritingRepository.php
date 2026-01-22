<?php

namespace App\Repositories;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class WritingRepository
{
    /**
     * Get a random writing question by exact category
     * 
     * @param string $category e.g., 'writing_academic_task1'
     * @return Question|null
     */
    public function getWritingQuestionByCategory($category)
    {
        return Question::where('type', 'writing')
            ->where('category', $category)
            ->where('active', true)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Get all writing questions by test type
     * 
     * @param string $testType 'academic' or 'general'
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWritingQuestionsByType($testType)
    {
        return Question::where('type', 'writing')
            ->where('category', 'like', "writing_{$testType}%")
            ->where('active', true)
            ->get();
    }

    /**
     * Get all writing questions by test type and task
     * 
     * @param string $testType 'academic' or 'general'
     * @param string $task 'task1' or 'task2'
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWritingQuestionsByTypeAndTask($testType, $task)
    {
        $category = "writing_{$testType}_{$task}";
        
        return Question::where('type', 'writing')
            ->where('category', $category)
            ->where('active', true)
            ->get();
    }

    /**
     * Get question by ID
     * 
     * @param int $questionId
     * @return Question|null
     */
    public function getQuestionById($questionId)
    {
        return Question::find($questionId);
    }

    /**
     * Get user's writing test history
     * 
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserWritingTests($userId, $limit = 10)
    {
        return Test::where('user_id', $userId)
            ->where('type', 'writing')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's writing statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getUserWritingStats($userId)
    {
        $tests = Test::where('user_id', $userId)
            ->where('type', 'writing')
            ->where('status', 'completed')
            ->get();

        if ($tests->isEmpty()) {
            return [
                'total_tests' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'latest_score' => 0,
                'tests_by_type' => [
                    'academic_task1' => 0,
                    'academic_task2' => 0,
                    'general_task1' => 0,
                    'general_task2' => 0,
                ],
                'average_by_criteria' => [
                    'task_achievement' => 0,
                    'coherence_cohesion' => 0,
                    'lexical_resource' => 0,
                    'grammar' => 0,
                ],
            ];
        }

        $testsByType = $tests->groupBy('category');
        $scores = $tests->pluck('score')->filter();
        
        // Calculate average scores by criteria
        $criteriaScores = [
            'task_achievement' => [],
            'coherence_cohesion' => [],
            'lexical_resource' => [],
            'grammar' => [],
        ];

        foreach ($tests as $test) {
            if ($test->result) {
                $result = json_decode($test->result, true);
                foreach ($criteriaScores as $key => &$values) {
                    if (isset($result[$key])) {
                        $values[] = $result[$key];
                    }
                }
            }
        }

        $averageByCriteria = [];
        foreach ($criteriaScores as $key => $values) {
            $averageByCriteria[$key] = count($values) > 0 
                ? round(array_sum($values) / count($values), 1)
                : 0;
        }

        return [
            'total_tests' => $tests->count(),
            'average_score' => round($scores->avg(), 1),
            'highest_score' => $scores->max() ?? 0,
            'latest_score' => $tests->first()->score ?? 0,
            'tests_by_type' => [
                'academic_task1' => $testsByType->get('writing_academic_task1', collect())->count(),
                'academic_task2' => $testsByType->get('writing_academic_task2', collect())->count(),
                'general_task1' => $testsByType->get('writing_general_task1', collect())->count(),
                'general_task2' => $testsByType->get('writing_general_task2', collect())->count(),
            ],
            'average_by_criteria' => $averageByCriteria,
            'progress_trend' => $this->calculateProgressTrend($tests),
        ];
    }

    /**
     * Calculate progress trend from recent tests
     * 
     * @param \Illuminate\Database\Eloquent\Collection $tests
     * @return string 'improving', 'stable', 'declining', or 'insufficient_data'
     */
    protected function calculateProgressTrend($tests)
    {
        if ($tests->count() < 3) {
            return 'insufficient_data';
        }

        $recentTests = $tests->sortBy('completed_at')->take(5);
        $scores = $recentTests->pluck('score')->toArray();

        // Calculate simple linear trend
        $n = count($scores);
        $xSum = array_sum(range(0, $n - 1));
        $ySum = array_sum($scores);
        $xySum = 0;
        $x2Sum = 0;

        foreach ($scores as $i => $score) {
            $xySum += $i * $score;
            $x2Sum += $i * $i;
        }

        $slope = ($n * $xySum - $xSum * $ySum) / ($n * $x2Sum - $xSum * $xSum);

        if ($slope > 0.2) {
            return 'improving';
        } elseif ($slope < -0.2) {
            return 'declining';
        }

        return 'stable';
    }

    /**
     * Get sample questions for practice (without creating a test)
     * 
     * @param string $category
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSampleQuestions($category = null, $limit = 5)
    {
        $query = Question::where('type', 'writing')
            ->where('active', true);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user has any incomplete writing tests
     * 
     * @param int $userId
     * @return Test|null
     */
    public function getUserIncompleteTest($userId)
    {
        return Test::where('user_id', $userId)
            ->where('type', 'writing')
            ->where('status', 'in_progress')
            ->orderBy('started_at', 'desc')
            ->first();
    }
}