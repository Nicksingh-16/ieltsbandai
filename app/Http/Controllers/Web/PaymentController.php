<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $razorpayKey;
    private $razorpaySecret;

    public function __construct()
    {
        $this->razorpayKey = env('RAZORPAY_KEY');
        $this->razorpaySecret = env('RAZORPAY_SECRET');
    }

    public function initiate(Request $request)
    {
        try {
            $request->validate([
                'plan' => 'required|string',
                'amount' => 'required|integer'
            ]);

            $api = new Api($this->razorpayKey, $this->razorpaySecret);

            // Create Razorpay Order
            $orderData = [
                'receipt' => 'order_' . time(),
                'amount' => $request->amount, // Amount in paise (₹99 = 9900 paise)
                'currency' => 'INR',
                'payment_capture' => 1 // Auto capture
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Store order in database
            Payment::create([
                'user_id' => Auth::id(),
                'order_id' => $razorpayOrder['id'],
                'amount' => $request->amount / 100, // Store in rupees
                'currency' => 'INR',
                'status' => 'pending',
                'plan' => $request->plan
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $request->amount,
                'razorpay_key' => $this->razorpayKey
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed'
            ], 500);
        }
    }

    public function success(Request $request)
    {
        try {
            $paymentId = $request->get('payment_id');
            
            $api = new Api($this->razorpayKey, $this->razorpaySecret);
            $payment = $api->payment->fetch($paymentId);

            if ($payment->status === 'captured') {
                // Update payment status
                $dbPayment = Payment::where('order_id', $payment->order_id)->first();
                
                if ($dbPayment) {
                    $dbPayment->update([
                        'payment_id' => $paymentId,
                        'status' => 'completed'
                    ]);

                    // Get package config
                    $packageConfig = config('packages.' . $dbPayment->plan);
                    
                    if ($packageConfig) {
                        // Activate pro subscription with credits
                        $creditService = app(\App\Services\CreditService::class);
                        $creditService->activatePro(
                            Auth::user(),
                            $packageConfig['duration_days'],
                            $packageConfig['credits']
                        );

                        // Create or update subscription
                        Subscription::updateOrCreate(
                            ['user_id' => Auth::id()],
                            [
                                'plan' => $dbPayment->plan,
                                'status' => 'active',
                                'starts_at' => now(),
                                'ends_at' => now()->addDays($packageConfig['duration_days']),
                                'payment_id' => $dbPayment->id
                            ]
                        );
                    }
                }

                return view('payment.success', [
                    'payment' => $dbPayment,
                    'message' => 'Payment successful! Your Pro subscription is now active.'
                ]);
            }

            return view('payment.failed', [
                'message' => 'Payment verification failed'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment success handling failed: ' . $e->getMessage());
            return view('payment.failed', [
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function webhook(Request $request)
    {
        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
        $webhookBody = file_get_contents('php://input');

        try {
            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
            
            if ($expectedSignature === $webhookSignature) {
                $payload = json_decode($webhookBody, true);
                
                // Handle different webhook events
                switch ($payload['event']) {
                    case 'payment.captured':
                        $this->handlePaymentCaptured($payload['payload']['payment']['entity']);
                        break;
                    
                    case 'payment.failed':
                        $this->handlePaymentFailed($payload['payload']['payment']['entity']);
                        break;
                    
                    case 'subscription.cancelled':
                        $this->handleSubscriptionCancelled($payload['payload']['subscription']['entity']);
                        break;
                }

                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'invalid signature'], 400);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function handlePaymentCaptured($paymentData)
    {
        Payment::where('order_id', $paymentData['order_id'])->update([
            'payment_id' => $paymentData['id'],
            'status' => 'completed'
        ]);
    }

    private function handlePaymentFailed($paymentData)
    {
        Payment::where('order_id', $paymentData['order_id'])->update([
            'payment_id' => $paymentData['id'],
            'status' => 'failed'
        ]);
    }

    private function handleSubscriptionCancelled($subscriptionData)
    {
        // Handle subscription cancellation
        Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->update([
            'status' => 'cancelled'
        ]);
    }
}