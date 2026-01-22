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
    $query = Test::where('user_id', $userId)
        ->orderBy('created_at', 'desc');

    return $paginate
        ? $query->paginate(8)     // you can change 8 to any number
        : $query->get();
}


}
