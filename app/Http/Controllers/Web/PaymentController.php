<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReceiptMail;
use App\Models\Institute;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    private function api(): Api
    {
        return new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Institute B2B plan page
    // ─────────────────────────────────────────────────────────────────────────
    public function institutePricing()
    {
        $plans = config('packages.institute', []);
        $institute = Auth::user()->institute ?? null;

        return view('pages.institute.pricing', compact('plans', 'institute'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Institute B2B plan initiation — creates Razorpay order for institute plan
    // ─────────────────────────────────────────────────────────────────────────
    public function initiateInstitute(Request $request)
    {
        $request->validate([
            'plan'   => 'required|string',
            'amount' => 'required|integer|min:100',
        ]);

        $user      = Auth::user();
        $institute = $user->institute;

        if (!$institute || !in_array($user->institute_role, ['owner', 'teacher'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $razorpayOrder = $this->api()->order->create([
                'receipt'         => 'inst_' . $institute->id . '_' . uniqid(),
                'amount'          => $request->amount,
                'currency'        => 'INR',
                'payment_capture' => 1,
                'notes'           => [
                    'institute_id'   => $institute->id,
                    'institute_name' => $institute->name,
                    'plan'           => $request->plan,
                ],
            ]);

            Payment::create([
                'user_id'      => $user->id,
                'order_id'     => $razorpayOrder['id'],
                'amount'       => $request->amount / 100,
                'currency'     => 'INR',
                'status'       => 'pending',
                'plan'         => 'institute_' . $request->plan,
                'metadata'     => json_encode(['institute_id' => $institute->id]),
            ]);

            return response()->json([
                'success'      => true,
                'order_id'     => $razorpayOrder['id'],
                'amount'       => $request->amount,
                'razorpay_key' => config('services.razorpay.key'),
                'prefill'      => [
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Institute payment initiation failed', ['error' => $e->getMessage(), 'institute_id' => $institute->id]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed. Please try again.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Institute B2B success — activates institute plan after payment
    // ─────────────────────────────────────────────────────────────────────────
    public function successInstitute(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $orderId   = $request->get('order_id');

        if (!$paymentId || !$orderId) {
            return redirect()->route('institute.pricing')->with('error', 'Invalid payment response.');
        }

        try {
            $rzPayment = $this->api()->payment->fetch($paymentId);

            if ($rzPayment->status !== 'captured') {
                return redirect()->route('institute.pricing')->with('error', 'Payment was not captured. Please try again.');
            }

            $dbPayment = Payment::where('order_id', $rzPayment->order_id)
                ->where('status', 'pending')
                ->first();

            if ($dbPayment) {
                $this->activateInstitutePlan($dbPayment, $paymentId);
            }

            return redirect()->route('institute.dashboard')
                ->with('success', 'Payment successful! Your institute plan is now active.');

        } catch (\Exception $e) {
            Log::error('Institute payment success failed', ['error' => $e->getMessage()]);
            return redirect()->route('institute.pricing')->with('error', 'Something went wrong. Contact support with ref: ' . $paymentId);
        }
    }

    private function activateInstitutePlan(Payment $dbPayment, string $paymentId): void
    {
        \DB::transaction(function () use ($dbPayment, $paymentId) {
            $locked = Payment::where('id', $dbPayment->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$locked) return;

            $locked->update(['payment_id' => $paymentId, 'status' => 'completed']);

            $metadata    = json_decode($locked->metadata ?? '{}', true);
            $instituteId = $metadata['institute_id'] ?? null;
            if (!$instituteId) return;

            // Strip 'institute_' prefix to get the plan key
            $planKey     = str_replace('institute_', '', $locked->plan);
            $planConfig  = config("packages.institute.{$planKey}");
            if (!$planConfig) return;

            Institute::where('id', $instituteId)->update([
                'plan'       => $planKey,
                'is_active'  => true,
                'seat_limit' => $planConfig['seat_limit'],
            ]);

            Log::info('Institute plan activated', ['institute_id' => $instituteId, 'plan' => $planKey]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Initiate — create Razorpay order and return order_id to frontend JS
    // ─────────────────────────────────────────────────────────────────────────
    public function initiate(Request $request)
    {
        $request->validate([
            'plan'   => 'required|string',
            'amount' => 'required|integer|min:100', // minimum 100 paise = ₹1
        ]);

        try {
            $razorpayOrder = $this->api()->order->create([
                'receipt'          => 'order_' . uniqid(),
                'amount'           => $request->amount,
                'currency'         => 'INR',
                'payment_capture'  => 1,
            ]);

            Payment::create([
                'user_id'  => Auth::id(),
                'order_id' => $razorpayOrder['id'],
                'amount'   => $request->amount / 100,
                'currency' => 'INR',
                'status'   => 'pending',
                'plan'     => $request->plan,
            ]);

            return response()->json([
                'success'      => true,
                'order_id'     => $razorpayOrder['id'],
                'amount'       => $request->amount,
                'razorpay_key' => config('services.razorpay.key'),
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed. Please try again.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Success — browser redirect after Razorpay checkout completes
    // Verifies payment with Razorpay API, then activates subscription.
    // ─────────────────────────────────────────────────────────────────────────
    public function success(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $orderId   = $request->get('order_id');

        if (!$paymentId || !$orderId) {
            return view('payment.failed', ['message' => 'Invalid payment response. Please contact support.']);
        }

        try {
            $rzPayment = $this->api()->payment->fetch($paymentId);

            if ($rzPayment->status !== 'captured') {
                return view('payment.failed', ['message' => 'Payment was not captured. Please try again.']);
            }

            $dbPayment = Payment::where('order_id', $rzPayment->order_id)
                ->where('status', 'pending') // only process once — prevents double-activation
                ->first();

            if (!$dbPayment) {
                // Already processed (e.g., webhook beat us here) — just show success
                return view('payment.success', [
                    'message' => 'Payment successful! Your subscription is now active.',
                    'payment' => Payment::where('order_id', $rzPayment->order_id)->first(),
                ]);
            }

            $this->activateSubscription($dbPayment, $paymentId);

            return view('payment.success', [
                'message' => 'Payment successful! Your subscription is now active.',
                'payment' => $dbPayment->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Payment success handling failed', ['error' => $e->getMessage(), 'payment_id' => $paymentId]);
            return view('payment.failed', ['message' => 'Something went wrong verifying your payment. Please contact support with reference: ' . $paymentId]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Webhook — Razorpay server-to-server event notification
    // Handles cases where browser redirect doesn't fire (network issues, etc.)
    // ─────────────────────────────────────────────────────────────────────────
    public function webhook(Request $request)
    {
        $webhookBody      = $request->getContent();
        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookSecret    = config('services.razorpay.webhook_secret');

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
        if (!hash_equals($expectedSignature, (string) $webhookSignature)) {
            Log::warning('Razorpay webhook signature mismatch');
            return response()->json(['status' => 'invalid signature'], 400);
        }

        try {
            $payload = json_decode($webhookBody, true);
            $event   = $payload['event'] ?? '';

            match ($event) {
                'payment.captured'        => $this->webhookPaymentCaptured($payload),
                'payment.failed'          => $this->webhookPaymentFailed($payload),
                'subscription.cancelled'  => $this->webhookSubscriptionCancelled($payload),
                default                   => null,
            };

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared activation — called by both success() and webhookPaymentCaptured()
    // Uses DB transaction + status guard to prevent double-activation.
    // ─────────────────────────────────────────────────────────────────────────
    private function activateSubscription(Payment $dbPayment, string $paymentId): void
    {
        \DB::transaction(function () use ($dbPayment, $paymentId) {
            // Re-fetch inside transaction with a lock to prevent race conditions
            $locked = Payment::where('id', $dbPayment->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                return; // Already activated by concurrent request
            }

            $locked->update([
                'payment_id' => $paymentId,
                'status'     => 'completed',
            ]);

            $packageConfig = config('packages.' . $locked->plan);
            if (!$packageConfig) {
                Log::error('Unknown plan on activation', ['plan' => $locked->plan, 'payment_id' => $paymentId]);
                return;
            }

            // Activate credits + subscription
            $user = \App\Models\User::find($locked->user_id);
            if ($user) {
                app(CreditService::class)->activatePro(
                    $user,
                    $packageConfig['duration_days'],
                    $packageConfig['credits']
                );

                Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'plan'       => $locked->plan,
                        'status'     => 'active',
                        'starts_at'  => now(),
                        'ends_at'    => now()->addDays($packageConfig['duration_days']),
                        'payment_id' => $locked->id,
                    ]
                );

                Log::info('Subscription activated', ['user_id' => $user->id, 'plan' => $locked->plan]);

                // Send payment receipt email (queued)
                Mail::to($user)->queue(new PaymentReceiptMail($locked, $user));
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Webhook event handlers
    // ─────────────────────────────────────────────────────────────────────────
    private function webhookPaymentCaptured(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $orderId     = $paymentData['order_id'] ?? null;
        $paymentId   = $paymentData['id'] ?? null;

        if (!$orderId || !$paymentId) return;

        $dbPayment = Payment::where('order_id', $orderId)->where('status', 'pending')->first();
        if ($dbPayment) {
            $this->activateSubscription($dbPayment, $paymentId);
        }
    }

    private function webhookPaymentFailed(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $orderId     = $paymentData['order_id'] ?? null;

        if ($orderId) {
            Payment::where('order_id', $orderId)->where('status', 'pending')->update(['status' => 'failed']);
        }
    }

    private function webhookSubscriptionCancelled(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'] ?? [];
        $rzSubId          = $subscriptionData['id'] ?? null;

        if (!$rzSubId) return;

        $sub = Subscription::where('razorpay_subscription_id', $rzSubId)->first();
        if ($sub) {
            $sub->update(['status' => 'cancelled']);
            // Sync cached field on User
            \App\Models\User::where('id', $sub->user_id)
                ->update(['is_pro' => false]);
        }
    }
}
