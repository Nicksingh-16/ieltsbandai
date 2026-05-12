<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Pro — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Razorpay JS removed: paywall is manual UPI for beta. Restore once
         the domain is verified and Razorpay is reactivated. --}}
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

@include('partials.beta-banner')

{{-- Ambient glow --}}
<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-600/6 rounded-full blur-3xl"></div>
</div>

{{-- Header --}}
<header class="sticky top-0 z-50 bg-surface-950/80 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="btn-ghost text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="w-16"></div>
    </div>
</header>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-8">

    {{-- Hero --}}
    <div class="text-center">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center mx-auto mb-5 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 1L9 9H1l6.5 5-2.5 8L12 17l7 5-2.5-8L23 9h-8z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-surface-50 mb-2">Upgrade to Pro</h1>
        <p class="text-surface-400 text-lg">Unlimited IELTS practice · All four skills · AI-powered</p>
    </div>

    {{-- Pricing card --}}
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-brand-900/60 via-surface-800 to-purple-900/40 border border-brand-700/40 p-8 sm:p-12 text-center">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-24 bg-brand-500/15 rounded-full blur-2xl pointer-events-none"></div>
        <p class="text-surface-400 text-sm uppercase tracking-widest mb-3">Monthly Subscription</p>
        <div class="flex items-end justify-center gap-2 mb-2">
            <span class="text-6xl sm:text-7xl font-extrabold text-surface-50">₹99</span>
            <span class="text-xl text-surface-400 mb-3">/mo</span>
        </div>
        <p class="text-surface-500 text-sm mb-6">Cancel anytime · No hidden fees · 7-day refund</p>
        <div class="flex items-center justify-center gap-4 text-xs text-surface-400">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Secure UPI · 7-day refund
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Cancel anytime
            </div>
        </div>
    </div>

    {{-- Benefits --}}
    <div class="card p-6 sm:p-8">
        <h2 class="text-surface-50 font-bold text-xl mb-6">Everything in Pro</h2>
        <div class="space-y-4">
            @foreach([
                ['Unlimited tests per day', 'Take as many Speaking, Writing, Listening and Reading tests as you want every day — no daily cap.', 'brand'],
                ['Full error analysis', 'Every grammatical, lexical and coherence error highlighted with exact corrections and explanations.', 'brand'],
                ['Band 9 model answers', 'See a complete Band 9 rewrite of your essay or speaking response to understand exactly what top scores look like.', 'brand'],
                ['PDF reports & history', 'Download a beautiful PDF report of every test. Track your band score improvement over time.', 'brand'],
                ['Vocabulary booster', 'Topic-specific vocabulary lists generated by AI for every essay you write.', 'brand'],
                ['Priority support', 'Get faster responses from our support team for any technical issues.', 'brand'],
            ] as [$title, $desc, $color])
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-surface-100 mb-0.5">{{ $title }}</p>
                    <p class="text-surface-400 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA Button — single entry point to the live UPI paywall. Razorpay
         remains gated until a verified domain is attached. --}}
    <div>
        <a href="{{ route('paywall.index') }}" class="btn-primary w-full py-4 text-base font-bold shadow-glow-lg text-lg flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            See all plans &amp; pay via UPI
        </a>
        <div class="flex items-center justify-center gap-6 mt-4 text-xs text-surface-500">
            <span>Pay via any UPI app &middot; PhonePe, GPay, Paytm, BHIM &middot; ₹9&ndash;₹199 plans</span>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="card p-6 sm:p-8">
        <h2 class="text-surface-50 font-bold text-lg mb-6 text-center">Frequently Asked Questions</h2>
        <div class="space-y-5">
            @foreach([
                ['Can I cancel anytime?', 'Yes, cancel from your account settings at any time. No questions asked, no penalties.'],
                ['What payment methods do you accept?', 'All major credit/debit cards, UPI, net banking, and digital wallets via Razorpay.'],
                ['Is there a refund policy?', 'Yes — 7-day money-back guarantee if you are not satisfied with Pro features.'],
                ['How accurate is the AI scoring?', 'Our scoring is calibrated against published Cambridge IELTS model answers and standardised against the official public band descriptors. Beta scores are best treated as a strong indicator of your level — use them to identify gaps, not as a substitute for an official test.'],
            ] as [$q, $a])
            <div class="border-b border-surface-700 pb-5 last:border-0 last:pb-0">
                <p class="font-semibold text-surface-100 mb-2">{{ $q }}</p>
                <p class="text-surface-400 text-sm leading-relaxed">{{ $a }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

<footer class="border-t border-surface-700/50 py-6 text-center">
    <p class="text-surface-600 text-sm">&copy; {{ date('Y') }} IELTS Band AI. All rights reserved.</p>
</footer>

<script>
const _payForm = document.getElementById('payment-form');
if (_payForm) _payForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('pay-btn');
    const original = btn.innerHTML;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Processing...';
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ plan: 'monthly', amount: 9900 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const rzp = new Razorpay({
                key: data.razorpay_key,
                amount: data.amount,
                currency: 'INR',
                name: 'IELTS Band AI',
                description: 'Pro Monthly Subscription',
                order_id: data.order_id,
                handler: function(response) {
                    window.location.href = '/payment/success?payment_id=' + response.razorpay_payment_id;
                },
                prefill: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}'
                },
                theme: { color: '#06b6d4' }
            });
            rzp.open();
            btn.innerHTML = original;
            btn.disabled = false;
        } else {
            alert('Payment initiation failed. Please try again.');
            btn.innerHTML = original;
            btn.disabled = false;
        }
    })
    .catch(() => {
        alert('Something went wrong. Please try again.');
        btn.innerHTML = original;
        btn.disabled = false;
    });
});
</script>
</body>
</html>
