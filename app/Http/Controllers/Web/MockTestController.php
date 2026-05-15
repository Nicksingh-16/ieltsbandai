<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\SpeakingScoreJob;
use App\Jobs\WritingEvaluationJob;
use App\Models\MockTest;
use App\Models\Test;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MockTestController extends Controller
{
    // Module sequence and time limits (seconds)
    const MODULE_TIMES = [
        'listening' => 40 * 60,   // 30 min + 10 min transfer
        'reading' => 60 * 60,
        'writing' => 60 * 60,
        'speaking' => 14 * 60,
    ];

    // ─── Landing ──────────────────────────────────────────────────────────────

    public function index()
    {
        // Check if user has an in-progress mock test
        $active = MockTest::where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        return view('pages.mock-test.index', compact('active'));
    }

    // ─── Start ────────────────────────────────────────────────────────────────

    public function start(Request $request)
    {
        $request->validate(['test_type' => 'required|in:academic,general']);

        // Abandon any existing in-progress mock test
        MockTest::where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        $mock = MockTest::create([
            'user_id' => Auth::id(),
            'test_type' => $request->test_type,
            'status' => 'in_progress',
            'current_module' => 'listening',
            'started_at' => now(),
        ]);

        session(['mock_test_id' => $mock->id, 'mock_test_type' => $request->test_type]);

        return redirect()->route('mock-test.module', ['mock' => $mock->id, 'module' => 'listening']);
    }

    // ─── Module Bridge — renders the appropriate module start form ─────────────

    public function module(MockTest $mock, string $module)
    {
        $this->authorizeMock($mock);

        if (! in_array($module, MockTest::MODULES)) {
            abort(404);
        }

        // If this module is already completed, skip to result or next
        if ($mock->moduleTestId($module)) {
            return redirect()->route('mock-test.result', $mock);
        }

        session(['mock_test_id' => $mock->id, 'mock_test_type' => $mock->test_type]);

        return view('pages.mock-test.module-bridge', compact('mock', 'module'));
    }

    // ─── Advance — called after each module submit, hooks into result pages ────

    public function advance(Request $request, MockTest $mock, string $module)
    {
        $this->authorizeMock($mock);

        $request->validate(['test_id' => 'required|integer']);

        $test = Test::where('id', $request->test_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Record this module's test and band
        $bandField = $module.'_band';
        $testField = $module.'_test_id';

        $mock->update([
            $testField => $test->id,
            $bandField => $test->overall_band,
        ]);

        $next = $mock->nextModule();

        if ($next) {
            $mock->update(['current_module' => $next]);

            return redirect()->route('mock-test.module', ['mock' => $mock->id, 'module' => $next]);
        }

        // All 4 modules submitted — the writing & speaking evals were deferred
        // (LLM cost protection per business decision). The mock test is now
        // gated behind the 2-credit unlock fee. Mark complete on the flow side
        // so the user can't redo modules, but DON'T compute overall_band yet
        // — eval scores aren't in for writing/speaking until they pay.
        $mock->refresh();
        $mock->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Keep mock_test_id in session so middleware bypasses still apply if
        // they navigate back to a module page; cleared in unlock() after pay.
        return redirect()->route('mock-test.paywall', $mock);
    }

    // ─── Paywall (GET) — shown after all 4 modules submitted, before unlock ──

    public function paywall(MockTest $mock)
    {
        $this->authorizeMock($mock);

        // Already paid — straight to results.
        if ($mock->results_unlocked) {
            return redirect()->route('mock-test.result', $mock);
        }

        // Pro / institute users — free unlock, skip the paywall.
        if (app(CreditService::class)->isPro($mock->user)) {
            return $this->dispatchUnlock($mock);
        }

        $cost     = MockTest::UNLOCK_COST_CREDITS;
        $userCred = (int) $mock->user->test_credits;

        return view('pages.mock-test.paywall', compact('mock', 'cost', 'userCred'));
    }

    // ─── Unlock (POST) — deducts 2 credits, fires deferred eval jobs ─────────

    public function unlock(MockTest $mock)
    {
        $this->authorizeMock($mock);

        if ($mock->results_unlocked) {
            return redirect()->route('mock-test.result', $mock);
        }

        $cost = MockTest::UNLOCK_COST_CREDITS;
        $user = $mock->user;

        // Pro / institute users — free unlock, no debit.
        if (app(CreditService::class)->isPro($user)) {
            return $this->dispatchUnlock($mock);
        }

        // Atomic credit debit
        $debited = DB::transaction(function () use ($user, $cost) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            if (! $locked || $locked->test_credits < $cost) {
                return false;
            }
            $locked->decrement('test_credits', $cost);

            return true;
        });

        if (! $debited) {
            return redirect()->route('paywall.index', ['from' => 'mock-test'])
                ->with('error', "You need {$cost} credits to unlock your full mock test results. Pick a plan or top-up below.");
        }

        return $this->dispatchUnlock($mock);
    }

    /**
     * Common path after a paid (or Pro-free) unlock: flip the flag, fire
     * deferred writing/speaking eval jobs, drop mock context from session.
     */
    private function dispatchUnlock(MockTest $mock)
    {
        $mock->update(['results_unlocked' => true]);

        // Writing — re-dispatch the LLM eval that we deferred at submit time.
        if ($mock->writing_test_id) {
            $writing = Test::find($mock->writing_test_id);
            if ($writing && empty($writing->overall_band)) {
                WritingEvaluationJob::dispatch($writing->id);
                Log::info('mock-test.unlock: WritingEvaluationJob dispatched', [
                    'mock_id'    => $mock->id,
                    'writing_id' => $writing->id,
                ]);
            }
        }

        // Speaking — transcripts were saved during the test, only the scoring
        // was deferred. Flip the metadata flag so any future TranscribeAudioJob
        // retries don't re-defer, then dispatch the score job.
        if ($mock->speaking_test_id) {
            $speaking = Test::find($mock->speaking_test_id);
            if ($speaking && empty($speaking->overall_band)) {
                $meta = is_array($speaking->metadata)
                    ? $speaking->metadata
                    : (json_decode($speaking->metadata ?? '{}', true) ?: []);
                unset($meta['mock_deferred_eval']);
                $speaking->forceFill([
                    'metadata' => json_encode($meta),
                    'status'   => 'scoring',
                ])->save();

                SpeakingScoreJob::dispatch($speaking->id)->onQueue('scoring');
                Log::info('mock-test.unlock: SpeakingScoreJob dispatched', [
                    'mock_id'     => $mock->id,
                    'speaking_id' => $speaking->id,
                ]);
            }
        }

        // Listening + Reading are scored synchronously at submit (no LLM),
        // so their bands are already on the Test records. Sync them onto the
        // MockTest row for the result page.
        $bands = [];
        foreach (['listening', 'reading', 'writing', 'speaking'] as $m) {
            $tid = $mock->{$m.'_test_id'};
            if ($tid) {
                $t = Test::find($tid);
                if ($t && $t->overall_band) {
                    $bands[$m.'_band'] = $t->overall_band;
                }
            }
        }
        if (! empty($bands)) {
            $mock->update($bands);
            $mock->refresh();
            $mock->update(['overall_band' => $mock->calculateOverall()]);
        }

        session()->forget(['mock_test_id', 'mock_test_type']);

        return redirect()->route('mock-test.result', $mock);
    }

    // ─── Result ───────────────────────────────────────────────────────────────

    public function result(MockTest $mock)
    {
        $this->authorizeMock($mock);

        // Gate: must pay the unlock fee before seeing the combined result.
        if (! $mock->results_unlocked) {
            return redirect()->route('mock-test.paywall', $mock);
        }

        // Writing & speaking evals may still be running — the result blade
        // polls and shows a spinner until those bands land.
        $mock->load('listening', 'reading', 'writing', 'speaking');

        // Keep overall_band in sync if a deferred eval just landed.
        $mock->refresh();
        $currentOverall = $mock->calculateOverall();
        if ($currentOverall > 0 && abs(($mock->overall_band ?? 0) - $currentOverall) > 0.01) {
            $mock->update(['overall_band' => $currentOverall]);
        }

        return view('pages.mock-test.result', compact('mock'));
    }

    // ─── History ──────────────────────────────────────────────────────────────

    public function history()
    {
        $mocks = MockTest::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest()
            ->paginate(10);

        return view('pages.mock-test.history', compact('mocks'));
    }

    // ─── Abandon ──────────────────────────────────────────────────────────────

    public function abandon(MockTest $mock)
    {
        $this->authorizeMock($mock);
        $mock->update(['status' => 'abandoned']);
        session()->forget(['mock_test_id', 'mock_test_type']);

        return redirect()->route('mock-test.index')->with('success', 'Mock test abandoned.');
    }

    private function authorizeMock(MockTest $mock): void
    {
        if ($mock->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
