<?php

namespace App\Repositories;

use App\Models\Question;

class SpeakingRepository
{
    public function getSpeakingQuestions()
{
    $part1 = Question::where('category', 'speaking_part1')->inRandomOrder()->first();
    $part2 = Question::where('category', 'speaking_part2')->inRandomOrder()->first();
    $part3 = Question::where('category', 'speaking_part3')->inRandomOrder()->first();

    if (!$part1 || !$part2 || !$part3) {
        throw new \Exception("Speaking questions missing in DB. Please seed at least 1 question per part.");
    }

    return [
        'part1' => $part1,
        'part2' => $part2,
        'part3' => $part3,
    ];
}

    public function countAudioFilesForTest($testId)
    {
        return \App\Models\AudioFile::where('test_id', $testId)->count();
    }

    public function findIncompleteSpeakingTest($userId)
    {
        return \App\Models\Test::where('user_id', $userId)
            ->where('type', 'speaking')
            ->where('status', 'processing')
            ->has('audioFiles', '<', 3)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
