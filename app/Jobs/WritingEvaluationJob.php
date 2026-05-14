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

    public $tries = 3;

    public $timeout = 300; // 5 minutes — GPT-4 writing scoring can take 60-90s

    public $backoff = [30, 60];

    public function __construct(protected int $testId) {}

    public function handle(WritingTestService $writingService): void
    {
        // When this job runs via dispatchAfterResponse() on the artisan dev
        // server (no fastcgi_finish_request), it executes inside the same
        // request lifecycle. We MUST NOT let any output or exception escape:
        //   - Output would corrupt the already-flushed JSON response.
        //   - An uncaught exception would let Laravel's exception renderer
        //     write an HTML error page to the same buffer.
        // So we suppress display, buffer-and-discard, and catch everything.
        // The test row + credit refund are handled by the existing failed()
        // path which we trigger by calling $this->fail() instead of throwing.
        $prevDisplay = @ini_set('display_errors', '0');
        ob_start();

        try {
            Log::info('WritingEvaluationJob started', ['test_id' => $this->testId]);

            // Idempotency on retry: if a prior attempt already scored this
            // test, don't re-run the LLM (3× cost + band can flip across
            // attempts). The job's failed() path handles credit refund for
            // genuinely failed scoring.
            $existing = \App\Models\Test::find($this->testId);
            if ($existing && $existing->status === 'completed') {
                Log::info('WritingEvaluationJob skipping — test already completed', [
                    'test_id' => $this->testId,
                ]);
                ob_end_clean();

                return;
            }

            $result = $writingService->scoreAndComplete($this->testId);

            if (! ($result['success'] ?? false)) {
                Log::error('WritingEvaluationJob scoring failed', [
                    'test_id' => $this->testId,
                    'error' => $result['error'] ?? 'unknown',
                ]);
                $this->fail(new \RuntimeException($result['error'] ?? 'Scoring failed'));

                return;
            }

            Log::info('WritingEvaluationJob completed', [
                'test_id' => $this->testId,
                'overall_band' => $result['result']['overall_band'] ?? null,
            ]);

            AssignedTestController::markCompleted($this->testId);
        } catch (\Throwable $e) {
            // Catch absolutely everything so the artisan dev server doesn't
            // render an HTML error page into the already-sent JSON response.
            // Mark failed via the framework's fail() pathway so failed() runs
            // (status update + credit refund). Don't rethrow.
            Log::error('WritingEvaluationJob caught exception', [
                'test_id' => $this->testId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            try {
                $this->fail($e);
            } catch (\Throwable $_) {
                // Last-resort: directly mark failed if fail() itself throws.
                try {
                    $this->failed($e);
                } catch (\Throwable $__) { /* swallow */
                }
            }
        } finally {
            $stray = ob_get_clean();
            if ($stray !== false && $stray !== '') {
                Log::warning('WritingEvaluationJob captured stray output (suppressed)', [
                    'test_id' => $this->testId,
                    'bytes' => strlen($stray),
                    'preview' => substr($stray, 0, 200),
                ]);
            }
            if ($prevDisplay !== false) {
                @ini_set('display_errors', $prevDisplay);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('WritingEvaluationJob permanently failed', [
            'test_id' => $this->testId,
            'error' => $e->getMessage(),
        ]);

        $test = Test::find($this->testId);
        if (! $test) {
            return;
        }

        $test->update(['status' => 'failed']);

        // Idempotent refund — safe to call repeatedly. The test row tracks
        // refunded_at, so a re-dispatched job (manual queue admin or the
        // exhausted-retries pathway) cannot credit-farm.
        try {
            app(\App\Services\CreditService::class)->refundForTest($test);
        } catch (\Throwable $refundError) {
            Log::error('Credit refund failed after evaluation failure', [
                'test_id' => $this->testId,
                'error' => $refundError->getMessage(),
            ]);
        }
    }
}
