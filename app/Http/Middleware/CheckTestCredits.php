<?php

namespace App\Http\Middleware;

use App\Services\CreditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTestCredits
{
    protected $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Mock test in progress: the user pays a single 2-credit unlock
        // charge at the end via mock-test.paywall, so per-module credit
        // gates are bypassed here. The mock paywall is what actually
        // protects the LLM evaluation cost — see MockTest::UNLOCK_COST_CREDITS.
        if (session()->has('mock_test_id')) {
            return $next($request);
        }

        // Out of credits → paywall (richer than the static pricing page,
        // captures intent via ?from= so the matching single-test pack
        // gets a visual highlight on the way in).
        if (! $this->creditService->hasCredits($user)) {
            $from = match (true) {
                str_contains($request->path(), 'writing') => 'writing',
                str_contains($request->path(), 'speaking') => 'speaking',
                str_contains($request->path(), 'listening') => 'listening',
                str_contains($request->path(), 'reading') => 'reading',
                default => null,
            };

            return redirect()->route('paywall.index', $from ? ['from' => $from] : [])
                ->with('error', "You're out of free test credits. Pick a plan below to keep practising.");
        }

        return $next($request);
    }
}
