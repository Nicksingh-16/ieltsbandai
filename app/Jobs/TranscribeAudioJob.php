<?php

namespace App\Jobs;

use App\Models\AudioFile;
use App\Models\Test;
use App\Services\TranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Transcribes a single audio file and, once all parts are done,
 * triggers SpeakingScoreJob. Dispatched immediately on each audio upload
 * so all 3 parts are transcribed IN PARALLEL rather than sequentially.
 */
class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $timeout = 200; // AssemblyAI: up to 90s upload + poll per file
    public $backoff = [20, 60];

    public function __construct(
        private int $audioFileId,
        private int $testId
    ) {}

    public function handle(TranscriptionService $transcriptionService): void
    {
        $audioFile = AudioFile::findOrFail($this->audioFileId);

        // Skip if already transcribed (job retried after partial success)
        if (!empty($audioFile->transcript)) {
            Log::info("TranscribeAudioJob: already transcribed, skipping", [
                'audio_file_id' => $this->audioFileId,
            ]);
            $this->checkAndDispatchScoring();
            return;
        }

        Log::info("TranscribeAudioJob: starting transcription", [
            'audio_file_id' => $this->audioFileId,
            'test_id'       => $this->testId,
            'file'          => $audioFile->file_url,
        ]);

        $transcript = $transcriptionService->transcribe($audioFile->file_url);

        if (!$transcript) {
            // Will retry up to $tries times
            throw new \RuntimeException(
                "Transcription returned null for audio file {$this->audioFileId}"
            );
        }

        $audioFile->update(['transcript' => $transcript]);

        Log::info("TranscribeAudioJob: transcription saved", [
            'audio_file_id'     => $this->audioFileId,
            'transcript_length' => strlen($transcript),
        ]);

        $this->checkAndDispatchScoring();
    }

    /**
     * After each transcription completes, atomically check whether all 3 parts
     * are done. Only the job that crosses the threshold dispatches SpeakingScoreJob
     * — preventing duplicate scoring dispatches.
     */
    private function checkAndDispatchScoring(): void
    {
        DB::transaction(function () {
            // Lock the test row to avoid race conditions between concurrent jobs
            $test = Test::lockForUpdate()->find($this->testId);

            if (!$test || $test->status !== 'processing') {
                return; // already scoring or completed
            }

            $doneCount = $test->audioFiles()
                ->whereNotNull('transcript')
                ->where('transcript', '!=', '')
                ->count();

            $totalParts = $test->audioFiles()->count();

            Log::info("TranscribeAudioJob: completion check", [
                'test_id'    => $this->testId,
                'done'       => $doneCount,
                'total'      => $totalParts,
            ]);

            if ($doneCount >= $totalParts && $totalParts >= 3) {
                // Mark as 'scoring' so no other job re-dispatches
                $test->update(['status' => 'scoring']);
                SpeakingScoreJob::dispatch($this->testId)->onQueue('scoring');

                Log::info("TranscribeAudioJob: all parts done — SpeakingScoreJob dispatched", [
                    'test_id' => $this->testId,
                ]);
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error("TranscribeAudioJob: permanently failed after {$this->tries} attempts", [
            'audio_file_id' => $this->audioFileId,
            'test_id'       => $this->testId,
            'error'         => $e->getMessage(),
        ]);

        // Mark test failed only if no scoring job was dispatched yet
        $test = Test::find($this->testId);
        if ($test && $test->status === 'processing') {
            $test->update([
                'status'   => 'failed',
                'feedback' => 'Audio transcription failed. Please try again.',
            ]);
        }
    }
}
