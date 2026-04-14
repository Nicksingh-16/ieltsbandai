<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MockTest;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MockTestController extends Controller
{
    // Module sequence and time limits (seconds)
    const MODULE_TIMES = [
        'listening' => 40 * 60,   // 30 min + 10 min transfer
        'reading'   => 60 * 60,
        'writing'   => 60 * 60,
        'speaking'  => 14 * 60,
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
            'user_id'        => Auth::id(),
            'test_type'      => $request->test_type,
            'status'         => 'in_progress',
            'current_module' => 'listening',
            'started_at'     => now(),
        ]);

        session(['mock_test_id' => $mock->id, 'mock_test_type' => $request->test_type]);

        return redirect()->route('mock-test.module', ['mock' => $mock->id, 'module' => 'listening']);
    }

    // ─── Module Bridge — renders the appropriate module start form ─────────────

    public function module(MockTest $mock, string $module)
    {
        $this->authorizeMock($mock);

        if (!in_array($module, MockTest::MODULES)) abort(404);

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
        $bandField = $module . '_band';
        $testField = $module . '_test_id';

        $mock->update([
            $testField => $test->id,
            $bandField => $test->overall_band,
        ]);

        $next = $mock->nextModule();

        if ($next) {
            $mock->update(['current_module' => $next]);
            return redirect()->route('mock-test.module', ['mock' => $mock->id, 'module' => $next]);
        }

        // All modules done — compute overall and complete
        $mock->refresh();
        $mock->update([
            'overall_band' => $mock->calculateOverall(),
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        session()->forget(['mock_test_id', 'mock_test_type']);

        return redirect()->route('mock-test.result', $mock);
    }

    // ─── Result ───────────────────────────────────────────────────────────────

    public function result(MockTest $mock)
    {
        $this->authorizeMock($mock);
        $mock->load('listening', 'reading', 'writing', 'speaking');

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
        if ($mock->user_id !== Auth::id()) abort(403);
    }
}
