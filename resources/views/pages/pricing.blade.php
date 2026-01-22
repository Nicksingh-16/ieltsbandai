{{-- resources/views/pricing.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Pro - IELTS Band AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

<!-- Header -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="{{ route('home') }}" class="text-gray-700 hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <span class="text-lg sm:text-xl font-bold text-gray-900">Upgrade to Pro</span>
            <div class="w-8"></div>
        </div>
    </div>
</header>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full mb-4">
            <i class="fas fa-crown text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Upgrade to Pro</h1>
        <p class="text-lg sm:text-xl text-gray-600">Unlimited Speaking + Writing AI Tests</p>
    </div>

    <!-- Pricing Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 sm:p-12 text-white text-center mb-8 shadow-2xl">
        <p class="text-lg mb-2 opacity-90">Monthly Subscription</p>
        <div class="flex items-center justify-center gap-2 mb-6">
            <span class="text-6xl sm:text-7xl font-bold">₹99</span>
            <span class="text-2xl opacity-90">/month</span>
        </div>
        <p class="text-base opacity-90">Cancel anytime • No hidden fees</p>
    </div>

    <!-- Benefits List -->
    <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Pro Benefits</h2>
        <div class="space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Unlimited Tests</h3>
                    <p class="text-gray-600">Take as many Speaking and Writing tests as you want every day</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Full Correction Details</h3>
                    <p class="text-gray-600">Get comprehensive error analysis with explanations for every mistake</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">PDF Reports</h3>
                    <p class="text-gray-600">Download detailed PDF reports of your test results and progress</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Progress Analytics</h3>
                    <p class="text-gray-600">Track your improvement over time with detailed charts and insights</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Priority Support</h3>
                    <p class="text-gray-600">Get faster responses from our support team</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Button -->
    <form action="{{ route('payment.initiate') }}" method="POST" id="payment-form">
        @csrf
        <input type="hidden" name="plan" value="monthly">
        <input type="hidden" name="amount" value="9900">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-5 rounded-xl text-xl font-bold hover:from-indigo-700 hover:to-purple-700 shadow-xl hover:shadow-2xl transition-all mb-4">
            <i class="fas fa-lock mr-2"></i>Pay with Razorpay
        </button>
    </form>

    <!-- Trust Badges -->
    <div class="flex items-center justify-center gap-6 text-gray-500 text-sm">
        <div class="flex items-center gap-2">
            <i class="fas fa-shield-alt"></i>
            <span>Secure Payment</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-undo"></i>
            <span>Cancel Anytime</span>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="mt-12 bg-white rounded-2xl shadow-md p-6 sm:p-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 text-center">What Our Pro Users Say</h2>
        <div class="space-y-6">
            <div class="border-l-4 border-indigo-600 pl-4">
                <p class="text-gray-700 mb-2">"The unlimited tests helped me improve from 6.5 to 7.5 in just 2 months!"</p>
                <p class="text-sm text-gray-500 font-medium">- Rahul Sharma, Mumbai</p>
            </div>
            <div class="border-l-4 border-indigo-600 pl-4">
                <p class="text-gray-700 mb-2">"Detailed feedback on every mistake made all the difference in my preparation."</p>
                <p class="text-sm text-gray-500 font-medium">- Priya Patel, Ahmedabad</p>
            </div>
            <div class="border-l-4 border-indigo-600 pl-4">
                <p class="text-gray-700 mb-2">"Best investment for IELTS prep. The AI feedback is incredibly accurate!"</p>
                <p class="text-sm text-gray-500 font-medium">- Amit Kumar, Delhi</p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-12 bg-white rounded-2xl shadow-md p-6 sm:p-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 text-center">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <h3 class="font-bold text-gray-900 mb-2">Can I cancel anytime?</h3>
                <p class="text-gray-600">Yes, you can cancel your subscription at any time from your account settings. No questions asked.</p>
            </div>
            <div class="border-b border-gray-200 pb-4">
                <h3 class="font-bold text-gray-900 mb-2">What payment methods do you accept?</h3>
                <p class="text-gray-600">We accept all major credit/debit cards, UPI, net banking, and digital wallets through Razorpay.</p>
            </div>
            <div class="border-b border-gray-200 pb-4">
                <h3 class="font-bold text-gray-900 mb-2">Is there a refund policy?</h3>
                <p class="text-gray-600">Yes, we offer a 7-day money-back guarantee if you're not satisfied with the Pro features.</p>
            </div>
            <div class="pb-4">
                <h3 class="font-bold text-gray-900 mb-2">How accurate is the AI scoring?</h3>
                <p class="text-gray-600">Our AI is trained on thousands of IELTS responses and provides band scores with 90%+ accuracy compared to human examiners.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    button.disabled = true;
    
    // Send request to backend
    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            plan: 'monthly',
            amount: 9900
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Initialize Razorpay
            const options = {
                key: data.razorpay_key,
                amount: data.amount,
                currency: 'INR',
                name: 'IELTS Band AI',
                description: 'Pro Monthly Subscription',
                order_id: data.order_id,
                handler: function(response) {
                    // Payment successful
                    window.location.href = '/payment/success?payment_id=' + response.razorpay_payment_id;
                },
                prefill: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}'
                },
                theme: {
                    color: '#4F46E5'
                }
            };
            
            const razorpay = new Razorpay(options);
            razorpay.open();
            
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        } else {
            alert('Payment initiation failed. Please try again.');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>

</body>
</html>