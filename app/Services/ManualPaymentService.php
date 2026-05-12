<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Manual UPI payment lifecycle.
 *
 * Flow:
 *   1. createPendingPayment()         — user clicks "Pay" on the paywall
 *   2. buildUpiDeepLink()             — paywall view renders QR + copy buttons
 *   3. submitUtr()                    — user pays, returns, submits the UTR
 *   4. grant()                        — credits/subscription granted immediately
 *   5. verify() (admin, out-of-band)  — UTR matched against bank statement
 *   6. revoke() (admin, on fraud)     — reverses grant if UTR is fake
 */
class ManualPaymentService
{
    public function __construct(protected CreditService $credits)
    {
    }

    /**
     * Create a pending Payment row for a user picking a plan.
     * Returns the Payment with a stable, user-readable order_id reference.
     */
    public function createPendingPayment(User $user, string $planKey): Payment
    {
        $plan = $this->resolvePlan($planKey);
        if (!$plan) {
            throw new \InvalidArgumentException("Unknown plan: {$planKey}");
        }

        // Short, unambiguous reference. Excludes 0/O/I/1 to avoid confusion
        // when users hand-type the UTR-note field on their UPI app.
        $ref = 'IBAI-' . strtoupper(Str::random(6));
        while (Payment::where('order_id', $ref)->exists()) {
            $ref = 'IBAI-' . strtoupper(Str::random(6));
        }

        return Payment::create([
            'user_id'  => $user->id,
            'order_id' => $ref,
            'amount'   => $plan['price'],
            'currency' => config('plans.currency', 'INR'),
            'status'   => 'pending',
            'plan'     => $planKey,
            'method'   => 'manual',
        ]);
    }

    /**
     * Build the UPI deep-link URI for a payment. The QR on the paywall view
     * encodes this; pasting it into any UPI app should pre-fill amount + note.
     *
     * Spec: https://upiprotocols.com/upi-deep-link
     */
    public function buildUpiDeepLink(Payment $payment): string
    {
        $vpa  = (string) config('services.upi.vpa');
        $name = (string) config('services.upi.name', 'IELTS Band AI');

        $params = [
            'pa' => $vpa,
            'pn' => $name,
            'am' => number_format((float) $payment->amount, 2, '.', ''),
            'tn' => $payment->order_id,             // reference goes in the txn note
            'cu' => $payment->currency ?: 'INR',
        ];

        return 'upi://pay?' . http_build_query($params);
    }

    /**
     * User submits the UTR after paying. Trust-then-verify: we immediately
     * grant credits/subscription and mark the payment pending_verification.
     * An admin later confirms the UTR against the bank statement.
     *
     * Returns the Payment with status=pending_verification.
     *
     * @throws \RuntimeException on validation failure
     */
    public function submitUtr(Payment $payment, string $utr): Payment
    {
        $utr = preg_replace('/\s+/', '', $utr);
        if (!preg_match('/^[A-Za-z0-9]{8,22}$/', $utr)) {
            throw new \RuntimeException('UTR must be 8–22 alphanumeric characters. Check your UPI app receipt.');
        }
        if ($payment->status !== 'pending') {
            throw new \RuntimeException("Payment already has status {$payment->status} — cannot re-submit.");
        }

        return DB::transaction(function () use ($payment, $utr) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if (!$locked || $locked->status !== 'pending') {
                throw new \RuntimeException('Payment was already processed.');
            }

            $locked->proof_id = $utr;
            $locked->status   = 'pending_verification';
            $locked->save();

            $this->grant($locked);

            Log::info('Manual UPI payment submitted', [
                'payment_id' => $locked->id,
                'user_id'    => $locked->user_id,
                'plan'       => $locked->plan,
                'amount'     => $locked->amount,
                'utr'        => $utr,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Grant credits / activate subscription based on the plan. Idempotent —
     * checks granted_at before applying. Called automatically by submitUtr;
     * exposed publicly so admin "force-grant" actions can call it directly.
     */
    public function grant(Payment $payment): void
    {
        if ($payment->granted_at) {
            return;
        }

        $plan = $payment->planConfig();
        if (!$plan) {
            Log::error('grant() called with unknown plan', ['payment_id' => $payment->id, 'plan' => $payment->plan]);
            return;
        }

        $user = $payment->user()->lockForUpdate()->first();
        if (!$user) {
            return;
        }

        if (!empty($plan['duration_days'])) {
            // Subscription plan — extend or create.
            $sub = Subscription::firstOrNew(['user_id' => $user->id]);
            $base = ($sub->exists && $sub->ends_at && $sub->ends_at->isFuture())
                ? $sub->ends_at
                : Carbon::now();
            $sub->user_id = $user->id;
            $sub->plan       = $payment->plan;
            $sub->status     = 'active';
            $sub->starts_at  = $sub->starts_at ?: Carbon::now();
            $sub->ends_at    = $base->copy()->addDays((int) $plan['duration_days']);
            $sub->payment_id = $payment->id;
            $sub->save();

            $user->forceFill([
                'is_pro'         => true,
                'pro_expires_at' => $sub->ends_at,
                'model_tier'     => $plan['model_tier'] ?? 'standard',
            ])->save();

            $payment->credits_granted = null;
        } elseif (!empty($plan['credits'])) {
            // One-time pack — route to the right credit pool.
            $pool = $plan['credits_pool'] ?? 'test';   // 'test' or 'self_eval'
            $amount = (int) $plan['credits'];
            if ($pool === 'self_eval') {
                $user->increment('self_eval_credits', $amount);
            } else {
                $this->credits->addCredits($user, $amount);
            }
            $payment->credits_granted = $amount;
        }

        $payment->granted_at = Carbon::now();
        $payment->save();
    }

    /**
     * Admin marks the UTR verified against the bank statement.
     */
    public function verify(Payment $payment, User $admin, ?string $note = null): void
    {
        $payment->verified_at = Carbon::now();
        $payment->verified_by = $admin->id;
        $payment->status      = 'completed';
        if ($note) {
            $payment->admin_note = $note;
        }
        $payment->save();
    }

    /**
     * Admin reverses a grant (fraud / fake UTR / chargeback).
     */
    public function revoke(Payment $payment, User $admin, string $reason): void
    {
        DB::transaction(function () use ($payment, $admin, $reason) {
            $user = $payment->user()->lockForUpdate()->first();
            if (!$user) {
                return;
            }

            $plan = $payment->planConfig();

            if ($plan && !empty($plan['duration_days'])) {
                // Subscription — cancel it if it traces back to this payment.
                $sub = Subscription::where('user_id', $user->id)
                    ->where('payment_id', $payment->id)
                    ->first();
                if ($sub) {
                    $sub->status  = 'cancelled';
                    $sub->ends_at = Carbon::now();
                    $sub->save();
                }
                $user->forceFill([
                    'is_pro'         => false,
                    'pro_expires_at' => null,
                    'model_tier'     => 'standard',
                ])->save();
            } elseif ($payment->credits_granted) {
                // Credits — claw back from the same pool we granted to.
                $pool = $plan['credits_pool'] ?? 'test';
                $field = $pool === 'self_eval' ? 'self_eval_credits' : 'test_credits';
                $clawback = min($payment->credits_granted, $user->{$field});
                if ($clawback > 0) {
                    $user->decrement($field, $clawback);
                }
            }

            $payment->status     = 'refunded';
            $payment->admin_note = ($payment->admin_note ? $payment->admin_note . "\n---\n" : '')
                . "REVOKED by admin #{$admin->id} on " . Carbon::now()->toIso8601String() . ": {$reason}";
            $payment->save();
        });
    }

    protected function resolvePlan(string $planKey): ?array
    {
        return config("plans.one_time.{$planKey}")
            ?? config("plans.subscription.{$planKey}");
    }
}
