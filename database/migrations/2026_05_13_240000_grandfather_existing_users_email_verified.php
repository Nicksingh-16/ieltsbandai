<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill email_verified_at for users created BEFORE we started enforcing
 * email verification. Without this, the User -> MustVerifyEmail change in
 * this same deploy would suddenly lock every existing user out of routes
 * gated by the `verified` middleware (writing, speaking, etc.) — even though
 * they signed up under the prior contract where verification wasn't required.
 *
 * Only touches rows where email_verified_at IS NULL — does not overwrite
 * already-verified users (e.g. Google OAuth signups).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Intentionally a no-op. Reverting would re-lock legitimate users out
        // of paid features, which we never want even on a rollback.
    }
};
