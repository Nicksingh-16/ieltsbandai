<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * If the request URL contains a ?ref= query param and beta tracking is on,
 * stash it in the session so RegisteredUserController / SocialAuthController
 * can persist it to users.ref_source on signup. The session value sticks for
 * the lifetime of the visitor's session, so they can land on /, browse for
 * a while, then sign up — and we still know which Telegram group sent them.
 *
 * Disabled outside beta mode (config('beta.track_ref') = false), so this
 * middleware no-ops in production.
 */
class CaptureRefSource
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('beta.track_ref') && $request->filled('ref') && !$request->session()->has('ref_source')) {
            $ref = substr(preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $request->query('ref')), 0, 64);
            if ($ref !== '') {
                $request->session()->put('ref_source', $ref);
            }
        }

        return $next($request);
    }
}
