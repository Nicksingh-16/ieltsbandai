<?php

namespace App\Services;

use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Check if user has available credits
     */
    public function hasCredits(User $user): bool
    {
        // Pro users always have credits
        if ($this->isPro($user)) {
            return true;
        }

        // Free users check credit count
        return $user->test_credits > 0;
    }

    /**
     * Deduct one credit from user (legacy, non-atomic).
     * Prefer chargeForTest() which is race-safe and idempotent.
     */
    public function deductCredit(User $user): void
    {
        if ($this->isPro($user)) {
            return;
        }

        if ($user->test_credits > 0) {
            $user->decrement('test_credits');
        }
    }

    /**
     * Atomically charge a user one credit for a specific test, locking the
     * user row to prevent races and marking the test as charged so the same
     * test can never be double-charged or double-refunded.
     *
     * Throws if user has no credits. Caller should check hasCredits() first
     * (or rely on the check.credits middleware) for a graceful redirect.
     */
    public function chargeForTest(User $user, Test $test): void
    {
        // Pro / institute users — record marker for accounting parity, no debit.
        if ($this->isPro($user)) {
            if (! $test->credit_charged_at) {
                $test->forceFill(['credit_charged_at' => now()])->save();
            }

            return;
        }

        DB::transaction(function () use ($user, $test) {
            // Lock the test row first to enforce idempotency on this single
            // test. If another request already charged it, no-op.
            $lockedTest = Test::whereKey($test->id)->lockForUpdate()->first();
            if (! $lockedTest || $lockedTest->credit_charged_at) {
                return;
            }

            // Lock user row — defeats the parallel-start race condition.
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->first();
            if (! $lockedUser) {
                throw new \RuntimeException('User vanished mid-charge');
            }

            if ($lockedUser->test_credits <= 0) {
                throw new \RuntimeException('Insufficient credits');
            }

            $lockedUser->decrement('test_credits');
            $lockedTest->forceFill(['credit_charged_at' => now()])->save();
        });

        // Refresh in-memory model so callers see latest credits.
        $user->refresh();
        $test->refresh();
    }

    /**
     * Idempotently refund a charged test. Safe to call multiple times — only
     * the first call actually credits the user.
     */
    public function refundForTest(Test $test): bool
    {
        return DB::transaction(function () use ($test) {
            $lockedTest = Test::whereKey($test->id)->lockForUpdate()->first();
            if (! $lockedTest) {
                return false;
            }
            // Already refunded, or never charged — nothing to do.
            if ($lockedTest->credit_refunded_at || ! $lockedTest->credit_charged_at) {
                return false;
            }

            $user = User::whereKey($lockedTest->user_id)->lockForUpdate()->first();
            if (! $user) {
                return false;
            }

            // Pro users were never debited — mark refunded for audit, no credit.
            if (! $this->isPro($user)) {
                $user->increment('test_credits');
            }

            $lockedTest->forceFill(['credit_refunded_at' => now()])->save();
            Log::info('Credit refunded for test', [
                'test_id' => $lockedTest->id,
                'user_id' => $user->id,
            ]);

            return true;
        });
    }

    /**
     * Add credits to user account
     */
    public function addCredits(User $user, int $amount): void
    {
        $user->increment('test_credits', $amount);
    }

    /**
     * Activate pro subscription
     */
    public function activatePro(User $user, int $durationDays, ?int $credits = null): void
    {
        $user->update([
            'is_pro' => true,
            'pro_expires_at' => Carbon::now()->addDays($durationDays),
        ]);

        if ($credits) {
            $this->addCredits($user, $credits);
        }
    }

    /**
     * Check if user is pro — Subscription model is the single source of truth.
     * `users.is_pro` is a cached convenience field; always verify against subscriptions table.
     */
    public function isPro(User $user): bool
    {
        // Institute students get unlimited access via their institute seat plan
        if ($this->hasSeatAccess($user)) {
            return true;
        }

        $sub = $user->subscription;

        if (! $sub || $sub->status !== 'active' || ! $sub->ends_at || $sub->ends_at->isPast()) {
            // Sync cached field if stale
            if ($user->is_pro) {
                $user->update(['is_pro' => false]);
            }

            return false;
        }

        // Sync cached field if needed
        if (! $user->is_pro) {
            $user->update(['is_pro' => true, 'pro_expires_at' => $sub->ends_at]);
        }

        return true;
    }

    /**
     * Institute students get unlimited test access via their institute's seat plan.
     */
    public function hasSeatAccess(User $user): bool
    {
        if (! $user->institute_id || ! in_array($user->institute_role, ['student', 'teacher', 'owner'])) {
            return false;
        }

        $institute = $user->institute;

        return $institute && $institute->is_active;
    }

    /**
     * Get remaining credits for user
     */
    public function getRemainingCredits(User $user): int|string
    {
        if ($this->isPro($user)) {
            return 'Unlimited';
        }

        return $user->test_credits;
    }

    /**
     * Reset monthly credits for pro users
     */
    public function resetMonthlyCredits(User $user, int $amount = 50): void
    {
        if ($this->isPro($user)) {
            $user->update(['test_credits' => $amount]);
        }
    }
}
