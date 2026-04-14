<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

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
     * Deduct one credit from user
     */
    public function deductCredit(User $user): void
    {
        // Don't deduct from pro users
        if ($this->isPro($user)) {
            return;
        }

        if ($user->test_credits > 0) {
            $user->decrement('test_credits');
        }
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
    public function activatePro(User $user, int $durationDays, int $credits = null): void
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

        if (!$sub || $sub->status !== 'active' || !$sub->ends_at || $sub->ends_at->isPast()) {
            // Sync cached field if stale
            if ($user->is_pro) {
                $user->update(['is_pro' => false]);
            }
            return false;
        }

        // Sync cached field if needed
        if (!$user->is_pro) {
            $user->update(['is_pro' => true, 'pro_expires_at' => $sub->ends_at]);
        }

        return true;
    }

    /**
     * Institute students get unlimited test access via their institute's seat plan.
     */
    public function hasSeatAccess(User $user): bool
    {
        if (!$user->institute_id || !in_array($user->institute_role, ['student', 'teacher', 'owner'])) {
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
