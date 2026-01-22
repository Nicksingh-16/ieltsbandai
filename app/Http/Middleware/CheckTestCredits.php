<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CreditService;

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

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has credits
        if (!$this->creditService->hasCredits($user)) {
            return redirect()->route('pricing')
                ->with('error', 'You have no test credits remaining. Please upgrade to continue.');
        }

        return $next($request);
    }
}
