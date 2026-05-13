<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund & Cancellation Policy — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

{{-- Nav --}}
<header class="sticky top-0 z-50 bg-surface-950/90 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('terms') }}"   class="btn-ghost text-sm hidden sm:inline-flex">Terms</a>
            <a href="{{ route('privacy') }}" class="btn-ghost text-sm hidden sm:inline-flex">Privacy</a>
            <a href="{{ route('contact') }}" class="btn-ghost text-sm">Contact</a>
            <a href="{{ route('home') }}"    class="btn-secondary text-sm px-4 py-2">← Home</a>
        </div>
    </div>
</header>

<div class="max-w-3xl mx-auto px-5 sm:px-8 py-12">

    {{-- Header --}}
    <div class="card p-7 sm:p-9 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div>
                <div class="tag-cyan inline-flex mb-3">Legal document</div>
                <h1 class="text-3xl sm:text-4xl font-black text-surface-50">Refund &amp; Cancellation Policy</h1>
            </div>
            <div class="bg-surface-900/60 border border-surface-700 rounded-xl px-4 py-3 text-right shrink-0">
                <p class="text-[10px] text-surface-500 uppercase tracking-widest">Last updated</p>
                <p class="text-sm font-semibold text-surface-100 mt-0.5">14 May 2026</p>
            </div>
        </div>
        <p class="text-surface-300 leading-relaxed">
            We want you to be confident trying IELTS Band AI. If our scoring doesn't help you, we'll refund you. This page explains exactly when refunds apply, how to request one, and how long it takes.
        </p>
    </div>

    {{-- ── 1. 7-day money-back guarantee ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">1. 7-day money-back guarantee</h2>
        <p class="text-surface-300 leading-relaxed mb-3">
            All paid plans &mdash; <strong>Pro Monthly (₹99)</strong>, <strong>Pro Plus Monthly</strong>, <strong>Full Mock (₹20)</strong>, and individual test packs (Writing ₹9 / Speaking ₹14 / Reading ₹9 / Listening ₹9) &mdash; come with a <strong class="text-emerald-300">7-day, no-questions-asked refund window</strong> from the date of purchase.
        </p>
        <p class="text-surface-400 text-sm leading-relaxed">
            If you're not satisfied with the scoring quality, feedback usefulness, or anything else within 7 days of payment, email us and we'll issue a full refund. You don't need to justify or explain.
        </p>
    </section>

    {{-- ── 2. How to request a refund ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">2. How to request a refund</h2>
        <ol class="list-decimal list-inside text-surface-300 leading-relaxed space-y-2 text-sm">
            <li>Email <a href="mailto:ronnie@vedcool.com" class="text-brand-400 hover:underline">ronnie@vedcool.com</a> from the email address on your IELTS Band AI account.</li>
            <li>Include your Razorpay <strong>payment ID</strong> (starts with <code class="bg-surface-800 px-1.5 py-0.5 rounded">pay_</code>) or <strong>order ID</strong> (starts with <code class="bg-surface-800 px-1.5 py-0.5 rounded">order_</code>) &mdash; both are visible in your receipt email.</li>
            <li>You don't need to give a reason. We may ask one optional question to improve the product, but answering is not required for the refund.</li>
        </ol>
        <p class="text-surface-400 text-xs mt-4">We aim to acknowledge every refund request within 24 hours and process it within 2 business days.</p>
    </section>

    {{-- ── 3. Processing time ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">3. When the money comes back</h2>
        <p class="text-surface-300 leading-relaxed mb-3 text-sm">
            Refunds are issued through Razorpay back to your original payment method:
        </p>
        <ul class="space-y-2 text-sm text-surface-300">
            <li class="flex gap-2"><span class="text-brand-400">•</span> <strong>UPI / wallets:</strong> typically <strong>2&ndash;3 business days</strong> after we initiate.</li>
            <li class="flex gap-2"><span class="text-brand-400">•</span> <strong>Credit / debit cards:</strong> typically <strong>5&ndash;7 business days</strong> &mdash; depends on your bank.</li>
            <li class="flex gap-2"><span class="text-brand-400">•</span> <strong>Net banking:</strong> typically <strong>3&ndash;5 business days</strong>.</li>
        </ul>
        <p class="text-surface-400 text-xs mt-4">If a refund doesn't reach you within the windows above plus 3 extra business days, reply to the refund acknowledgement email and we'll chase Razorpay on your behalf.</p>
    </section>

    {{-- ── 4. After 7 days ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">4. After the 7-day window</h2>
        <p class="text-surface-300 leading-relaxed text-sm mb-3">
            After 7 days from purchase, refunds are at our discretion and considered case-by-case for clear issues:
        </p>
        <ul class="space-y-1.5 text-sm text-surface-300">
            <li class="flex gap-2"><span class="text-emerald-400">✓</span> Service outage that prevented you from using credits for &gt;24 hours.</li>
            <li class="flex gap-2"><span class="text-emerald-400">✓</span> A scoring bug we acknowledge that materially affected your results.</li>
            <li class="flex gap-2"><span class="text-emerald-400">✓</span> Duplicate / accidental payment (we'll refund the duplicate in full).</li>
            <li class="flex gap-2"><span class="text-red-400">✗</span> Change of mind after substantially using the plan (e.g. taking 20 writing tests then asking for a refund 3 weeks in).</li>
        </ul>
    </section>

    {{-- ── 5. Subscription cancellation ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">5. Cancelling a subscription</h2>
        <p class="text-surface-300 leading-relaxed text-sm mb-3">
            <strong>Good news:</strong> our monthly plans do <strong class="text-emerald-300">not auto-renew</strong>. You'll never be charged a second time without explicitly initiating another payment. There's nothing to cancel &mdash; your plan simply ends after 30 days unless you actively renew.
        </p>
        <p class="text-surface-300 leading-relaxed text-sm">
            If we ever add auto-renewing subscriptions in the future, you'll be able to cancel at any time from your account settings, and we'll honour the 7-day refund window on each renewal.
        </p>
    </section>

    {{-- ── 6. Fraud / abuse ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">6. Fraud, abuse, chargebacks</h2>
        <p class="text-surface-300 leading-relaxed text-sm">
            Accounts that submit fraudulent transaction IDs, claim refunds while continuing to abuse the service, or initiate chargebacks without contacting us first will have their access suspended. We'd much rather resolve any issue by email &mdash; a chargeback costs both of us, and we will engage Razorpay's dispute process to defend it.
        </p>
    </section>

    {{-- ── 7. Contact ── --}}
    <section class="card p-7 mb-4">
        <h2 class="text-xl font-bold text-surface-50 mb-3">7. Questions</h2>
        <p class="text-surface-300 leading-relaxed text-sm">
            Email <a href="mailto:ronnie@vedcool.com" class="text-brand-400 hover:underline">ronnie@vedcool.com</a> or use the <a href="{{ route('contact') }}" class="text-brand-400 hover:underline">contact form</a>. We typically reply within 24 hours on business days.
        </p>
    </section>

    {{-- Footer links --}}
    <div class="flex flex-wrap gap-4 text-xs text-surface-500 justify-center mt-8">
        <a href="{{ route('terms') }}"   class="hover:text-surface-200">Terms of Use</a>
        <span>·</span>
        <a href="{{ route('privacy') }}" class="hover:text-surface-200">Privacy Policy</a>
        <span>·</span>
        <a href="{{ route('contact') }}" class="hover:text-surface-200">Contact</a>
    </div>

</div>

</body>
</html>
