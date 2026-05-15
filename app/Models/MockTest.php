<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockTest extends Model
{
    protected $fillable = [
        'user_id', 'test_type', 'status', 'current_module',
        'listening_test_id', 'reading_test_id', 'writing_test_id', 'speaking_test_id',
        'listening_band', 'reading_band', 'writing_band', 'speaking_band',
        'overall_band', 'results_unlocked', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'results_unlocked' => 'boolean',
    ];

    // Single charge taken at the end-of-mock paywall, in credits.
    // Cheaper than 4 individual tests (4 credits) but pricier than 1.
    const UNLOCK_COST_CREDITS = 2;

    // Module order for sequential flow
    const MODULES = ['listening', 'reading', 'writing', 'speaking'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listening()
    {
        return $this->belongsTo(Test::class, 'listening_test_id');
    }

    public function reading()
    {
        return $this->belongsTo(Test::class, 'reading_test_id');
    }

    public function writing()
    {
        return $this->belongsTo(Test::class, 'writing_test_id');
    }

    public function speaking()
    {
        return $this->belongsTo(Test::class, 'speaking_test_id');
    }

    public function nextModule(): ?string
    {
        $idx = array_search($this->current_module, self::MODULES);

        return self::MODULES[$idx + 1] ?? null;
    }

    public function isComplete(): bool
    {
        return $this->speaking_test_id !== null
            && $this->status === 'completed';
    }

    public function calculateOverall(): float
    {
        $bands = array_filter([
            $this->listening_band,
            $this->reading_band,
            $this->writing_band,
            $this->speaking_band,
        ]);

        if (empty($bands)) {
            return 0;
        }

        return round(array_sum($bands) / count($bands) * 2) / 2;
    }

    public function moduleTestId(string $module): ?int
    {
        return $this->{$module.'_test_id'};
    }

    public function routeForModule(string $module): string
    {
        $testId = $this->moduleTestId($module);
        if (! $testId) {
            return '#';
        }

        return match ($module) {
            'listening' => route('listening.result', $testId),   // after submit
            'reading' => route('reading.result', $testId),
            'writing' => route('writing.result', $testId),
            'speaking' => route('test.result', $testId),
            default => '#',
        };
    }

    /**
     * Records a completed module's test id + band on this mock row and returns
     * the URL the user should be redirected to next — either the next module's
     * bridge, or the mock-test paywall if this was the final module.
     *
     * Idempotent: re-recording the same module is a no-op so a double POST
     * from the test submit can't advance the flow twice. Called from both
     * MockTestController::advance() (the legacy click-to-continue route) and
     * the individual test submit controllers (which now redirect straight
     * here in mock context, skipping the per-module result page entirely).
     */
    public function recordModuleAndNextRoute(string $module, Test $test): string
    {
        if (! in_array($module, self::MODULES, true)) {
            return route('dashboard');
        }

        // Idempotency — if this module already recorded, just route to next.
        if (! $this->moduleTestId($module)) {
            $this->update([
                $module.'_test_id' => $test->id,
                $module.'_band'    => $test->overall_band,
            ]);
        }

        $next = $this->nextModule();
        if ($next) {
            $this->update(['current_module' => $next]);

            return route('mock-test.module', ['mock' => $this->id, 'module' => $next]);
        }

        // Final module just submitted — mark the flow complete (results are
        // still gated behind the unlock paywall) and send the user there.
        $this->refresh();
        if ($this->status !== 'completed') {
            $this->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        }

        return route('mock-test.paywall', $this);
    }
}
