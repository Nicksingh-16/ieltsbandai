<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ManualPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaywallController extends Controller
{
    public function __construct(protected ManualPaymentService $payments)
    {
    }

    /**
     * The pricing page. Renders the full plan ladder + conversion psychology.
     * Reachable directly (/get-credits) or as a redirect target when a user
     * runs out of credits in the test flow.
     */
    public function index(Request $request)
    {
        $oneTime      = config('plans.one_time', []);
        $subscription = config('plans.subscription', []);
        $beta         = config('plans.beta', []);
        $symbol       = config('plans.currency_symbol', '₹');

        // ?from=writing etc. — optional context to subtly highlight the most
        // relevant single-test pack. Doesn't gate anything, just visual hint.
        $context      = $request->query('from');

        // Razorpay key (publishable). If unset (e.g. no RAZORPAY_KEY in .env),
        // the view falls back to the manual UPI form-POST flow so the paywall
        // never goes dark — local dev and emergency rollback both work.
        $razorpayKey  = config('services.razorpay.key');

        return view('pages.paywall.index', compact('oneTime', 'subscription', 'beta', 'symbol', 'context', 'razorpayKey'));
    }

    /**
     * User clicked "Pay" on a plan. Create the pending payment row and
     * redirect to the UPI payment screen.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'plan' => 'required|string|max:64',
        ]);

        $plan = config("plans.one_time.{$data['plan']}")
             ?? config("plans.subscription.{$data['plan']}");

        if (!$plan) {
            return back()->with('error', 'Unknown plan. Please try again.');
        }

        $payment = $this->payments->createPendingPayment($request->user(), $data['plan']);

        return redirect()->route('paywall.pay', ['ref' => $payment->order_id]);
    }

    /**
     * The UPI payment screen — QR + UPI ID + countdown + "I have paid" CTA.
     */
    public function pay(string $ref)
    {
        $payment = Payment::where('order_id', $ref)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Already done — short-circuit to receipt.
        if (in_array($payment->status, ['pending_verification', 'completed'], true)) {
            return redirect()->route('paywall.receipt', ['ref' => $payment->order_id]);
        }

        $plan        = $payment->planConfig();
        $upiUri      = $this->payments->buildUpiDeepLink($payment);
        $upiVpa      = config('services.upi.vpa');
        $upiName     = config('services.upi.name');

        return view('pages.paywall.pay', compact(
            'payment', 'plan', 'upiUri', 'upiVpa', 'upiName'
        ));
    }

    /**
     * User submits the UTR from their UPI app receipt. We trust-then-verify:
     * credits/sub granted immediately, status flips to pending_verification.
     */
    public function submitUtr(Request $request, string $ref)
    {
        $data = $request->validate([
            'utr' => 'required|string|min:8|max:22',
        ]);

        $payment = Payment::where('order_id', $ref)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $this->payments->submitUtr($payment, $data['utr']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('paywall.receipt', ['ref' => $payment->order_id])
            ->with('success', 'Payment received — your plan is now active.');
    }

    /**
     * The receipt / success page. Shows the granted plan + verification
     * status. Reachable any time after UTR submission via the order_id.
     */
    public function receipt(string $ref)
    {
        $payment = Payment::where('order_id', $ref)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $plan = $payment->planConfig();
        $user = $payment->user;

        return view('pages.paywall.receipt', compact('payment', 'plan', 'user'));
    }
}
