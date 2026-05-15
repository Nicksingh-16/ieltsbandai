<?php

namespace App\Repositories;

use App\Models\Test;

class TestRepository implements TestRepositoryInterface
{
    // public function getUserTestHistory($userId)
    // {
    //     return Test::with('testScores')
    //         ->withCount('audioFiles')
    //         ->where('user_id', $userId)
    //         ->where(function($query) {
    //             // Only show completed tests:
    //             // - Status is not 'processing' (completed/graded)
    //             // - OR speaking tests with 3 audio files (completed even if status is processing)
    //             $query->where('status', '!=', 'processing')
    //                 ->orWhere(function($q) {
    //                     $q->where('type', 'speaking')
    //                       ->has('audioFiles', '>=', 3);
    //                 });
    //         })
    //         ->orderBy('created_at', 'desc')
    //         ->get();
    // }

    public function getUserTestHistory($userId, bool $paginate = false)
    {
        // Hide tests that are children of a full mock test — the dashboard
        // surfaces the parent MockTest row as a single line so the 4 child
        // tests would otherwise double-list. The mock test history page
        // (/mock-test/history) is where those drill-downs live.
        $mockChildIds = \App\Models\MockTest::where('user_id', $userId)
            ->get(['listening_test_id', 'reading_test_id', 'writing_test_id', 'speaking_test_id'])
            ->flatMap(fn ($m) => [
                $m->listening_test_id, $m->reading_test_id,
                $m->writing_test_id,   $m->speaking_test_id,
            ])
            ->filter()->unique()->values();

        $query = Test::where('user_id', $userId)
            ->when($mockChildIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $mockChildIds))
            ->orderBy('created_at', 'desc');

        return $paginate
            ? $query->paginate(8)     // you can change 8 to any number
            : $query->get();
    }
}
