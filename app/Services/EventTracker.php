<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Lightweight first-party analytics. Writes one row per event to user_events.
 * Designed to never break the calling flow — all writes are wrapped and
 * exceptions are logged but swallowed.
 *
 * Use sparingly: track conversions, drop-offs, and feature use, NOT every
 * page view. For that, add a JS tracker (Plausible/Pirsch) later.
 */
class EventTracker
{
    public function track(string $event, array $properties = [], ?User $user = null): void
    {
        try {
            $user = $user ?? Auth::user();
            $request = app('request');

            UserEvent::create([
                'user_id'    => $user?->id,
                'event'      => substr($event, 0, 64),
                'properties' => $properties ?: null,
                'session_id' => $request?->hasSession() ? substr($request->session()->getId(), 0, 64) : null,
                'ip'         => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('EventTracker failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
