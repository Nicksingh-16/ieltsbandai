<?php

namespace App\Jobs;

use App\Http\Controllers\Web\AssignedTestController;
use App\Models\Test;
use App\Services\WritingTestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WritingEvaluationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $timeout = 300; // 5 minutes — GPT-4 writing scoring can take 60-90s
    public $backoff = [30, 60];

    public function __construct(protected int $testId) {}

    public function handle(WritingTestService $writingService): void
    {
        Log::info('WritingEvaluationJob started', ['test_id' => $this->testId]);

        $result = $writingService->scoreAndComplete($this->testId);

        if (!($result['success'] ?? false)) {
            Log::error('WritingEvaluationJob scoring failed', [
                'test_id' => $this->testId,
                'error'   => $result['error'] ?? 'unknown',
            ]);
            // fail() will trigger retry via $backoff
            $this->fail(new \RuntimeException($result['error'] ?? 'Scoring failed'));
            return;
        }

        Log::info('WritingEvaluationJob completed', [
            'test_id'      => $this->testId,
            'overall_band' => $result['result']['overall_band'] ?? null,
        ]);

        // Mark assigned test student record as completed (if this was an institute assignment)
        AssignedTestController::markCompleted($this->testId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('WritingEvaluationJob permanently failed', [
            'test_id' => $this->testId,
            'error'   => $e->getMessage(),
        ]);

        Test::where('id', $this->testId)->update(['status' => 'failed']);
    }
}
