<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\AudioFile;
use App\Repositories\SpeakingRepository;
use App\Jobs\TranscribeAudioJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SpeakingTestService
{
    protected $speakingRepo;

    public function __construct(SpeakingRepository $speakingRepo)
    {
        $this->speakingRepo = $speakingRepo;
    }

    public function getOrCreateSpeakingTest($userId, $questions)
    {
        // Check if there's an incomplete test first
        $incompleteTest = $this->speakingRepo->findIncompleteSpeakingTest($userId);
        
        if ($incompleteTest) {
            // Return existing incomplete test with its questions
            $incompleteTest->load('testQuestions.question');
            return $incompleteTest;
        }

        // Create new test if no incomplete test exists
        return $this->createSpeakingTest($userId, $questions);
    }

    public function createSpeakingTest($userId, $questions)
    {
        $test = Test::create([
            'user_id' => $userId,
            'type' => 'speaking',
            'status' => 'processing'
        ]);

        foreach ($questions as $part => $question) {
            TestQuestion::create([
                'test_id' => $test->id,
                'question_id' => $question->id,
                'part' => (int) filter_var($part, FILTER_SANITIZE_NUMBER_INT)
            ]);
        }

        return $test;
    }

    public function uploadAudio($testId, UploadedFile $audioFile, $duration)
    {
        // Validate test exists
        $test = Test::findOrFail($testId);

        // Store audio file with .webm extension to preserve format
        $extension = $audioFile->getClientOriginalExtension() ?: 'webm';
        $filename = uniqid() . '.' . $extension;
        $path = $audioFile->storeAs('audio', $filename, 'public');
        
        // Calculate file size in KB
        $sizeKb = round($audioFile->getSize() / 1024);

        // Create audio file record
        $audioFile = AudioFile::create([
            'test_id'          => $testId,
            'file_url'         => $path,
            'duration_seconds' => $duration,
            'size_kb'          => $sizeKb,
        ]);

        // Dispatch transcription IMMEDIATELY for this part — runs in parallel with other parts
        TranscribeAudioJob::dispatch($audioFile->id, $testId)->onQueue('transcription');

        $audioCount = $this->speakingRepo->countAudioFilesForTest($testId);
        $isLast     = $audioCount >= 3;

        return [
            'success' => true,
            'next'    => !$isLast,
            'is_last' => $isLast,
        ];
    }
}
