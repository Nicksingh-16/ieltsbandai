<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay {{ config('plans.currency_symbol') }}{{ $payment->amount }} &mdash; IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
</div>

<header class="sticky top-0 z-50 bg-surface-950/80 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('paywall.index') }}" class="btn-ghost text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Change plan
        </a>
        <span class="text-xs font-mono text-surface-500">{{ $payment->order_id }}</span>
    </div>
</header>

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    {{-- ── Order summary ───────────────────────────────────────────────── --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-surface-400 uppercase tracking-widest">Order summary</p>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-semibold">Awaiting payment</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-surface-50">{{ $plan['label'] }}</p>
                <p class="text-xs text-surface-400 leading-relaxed mt-0.5">{{ $plan['subtitle'] }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-2xl font-bold text-surface-50">{{ config('plans.currency_symbol') }}{{ number_format($payment->amount, 2) }}</p>
                <p class="text-[11px] text-surface-500">incl. all charges</p>
            </div>
        </div>
    </div>

    {{-- ── Payment steps ──────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">

        {{-- Step 1: scan/pay --}}
        <div class="p-6 border-b border-surface-700">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-7 h-7 rounded-full bg-brand-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
                <h2 class="text-sm font-semibold text-surface-100">Pay {{ config('plans.currency_symbol') }}{{ number_format($payment->amount, 2) }} via UPI</h2>
                <span class="ml-auto text-[10px] text-surface-500" id="countdownLabel" aria-live="polite"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-start">
                {{-- QR — static image of the merchant UPI QR. We don't use a
                     dynamic deep-link QR here because that requires the qrcode.js
                     CDN and was rendering blank when the CDN was slow / blocked.
                     The static QR pairs with the order_id note shown below, which
                     the user copies into their UPI app's note field manually. --}}
                <div class="flex flex-col items-center">
                    <div class="bg-white p-3 rounded-xl shadow-lg" id="qrFrame">
                        <img src="{{ asset('images/upi-qr.png') }}"
                             alt="UPI QR code — scan to pay"
                             width="220" height="220"
                             class="block w-[220px] h-[220px] object-contain">
                    </div>
                    <p class="text-[11px] text-surface-500 mt-3 text-center">Scan with PhonePe / GPay / Paytm / BHIM</p>
                </div>

                {{-- Manual fallback --}}
                <div class="space-y-3">
                    <p class="text-xs text-surface-400">Or pay manually using:</p>

                    <div class="space-y-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-surface-500 mb-1">UPI ID</p>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-surface-800 border border-surface-700">
                                <span class="text-sm font-mono text-surface-100 flex-1 truncate" id="upiVpa">{{ $upiVpa }}</span>
                                <button type="button" data-copy="upiVpa" class="btn-ghost text-[11px] px-2 py-1">Copy</button>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-surface-500 mb-1">Amount</p>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-surface-800 border border-surface-700">
                                <span class="text-sm font-mono text-surface-100 flex-1" id="upiAmount">{{ number_format($payment->amount, 2) }}</span>
                                <button type="button" data-copy="upiAmount" class="btn-ghost text-[11px] px-2 py-1">Copy</button>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-surface-500 mb-1">Note (important)</p>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-surface-800 border border-surface-700">
                                <span class="text-sm font-mono text-surface-100 flex-1" id="upiNote">{{ $payment->order_id }}</span>
                                <button type="button" data-copy="upiNote" class="btn-ghost text-[11px] px-2 py-1">Copy</button>
                            </div>
                            <p class="text-[10px] text-amber-400/80 mt-1">Include this note so we can match your payment instantly.</p>
                        </div>
                    </div>

                    {{-- Mobile-only: deep link to a UPI app directly --}}
                    <a href="{{ $upiUri }}" class="block sm:hidden btn-primary w-full justify-center text-sm py-2.5">
                        Open my UPI app
                    </a>
                </div>
            </div>
        </div>

        {{-- Step 2: enter UTR --}}
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-7 h-7 rounded-full bg-surface-700 text-surface-200 text-xs font-bold flex items-center justify-center shrink-0">2</div>
                <h2 class="text-sm font-semibold text-surface-100">Submit your transaction ID (UTR)</h2>
            </div>

            <p class="text-xs text-surface-400 leading-relaxed mb-4">
                After paying, your UPI app will show a 12&ndash;22 character
                <span class="font-semibold text-surface-200">UTR / transaction ID</span> on the receipt screen.
                Paste it below &mdash; your plan activates the moment you submit.
            </p>

            @if(session('error'))
            <div class="mb-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-xs">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('paywall.utr', $payment->order_id) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="utr" class="text-[11px] text-surface-400 uppercase tracking-wider">UTR / Transaction ID</label>
                    <input
                        type="text"
                        name="utr"
                        id="utr"
                        required
                        minlength="8"
                        maxlength="22"
                        pattern="[A-Za-z0-9]{8,22}"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="e.g. 442731829014"
                        value="{{ old('utr') }}"
                        class="w-full mt-1 px-3 py-2.5 rounded-lg bg-surface-800 border border-surface-700 focus:border-brand-500 focus:outline-none text-sm font-mono text-surface-100 placeholder:text-surface-600"
                    >
                </div>
                <button type="submit" class="btn-primary w-full justify-center text-sm py-2.5">
                    I have paid &mdash; activate my plan
                </button>
                <p class="text-[10px] text-surface-500 text-center">
                    By submitting you confirm you've transferred {{ config('plans.currency_symbol') }}{{ number_format($payment->amount, 2) }} to the UPI ID above.
                    Fake submissions will be reversed and the account suspended.
                </p>
            </form>
        </div>
    </div>

    {{-- ── How to find your UTR (collapsible help) ─────────────────────── --}}
    <details class="card p-4 group">
        <summary class="text-sm font-medium text-surface-200 cursor-pointer flex items-center gap-2">
            <svg class="w-4 h-4 text-surface-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            Where do I find the UTR / transaction ID?
        </summary>
        <div class="mt-3 text-xs text-surface-400 space-y-2 leading-relaxed pl-6">
            <p><span class="font-semibold text-surface-200">PhonePe:</span> Open the transaction &rarr; look for &ldquo;UPI Ref. No.&rdquo; (12 digits).</p>
            <p><span class="font-semibold text-surface-200">GPay:</span> Tap the transaction &rarr; &ldquo;UPI transaction ID&rdquo; near the bottom.</p>
            <p><span class="font-semibold text-surface-200">Paytm:</span> Order details page &rarr; &ldquo;UPI Ref ID&rdquo;.</p>
            <p><span class="font-semibold text-surface-200">BHIM:</span> Tap the txn in history &rarr; the 12-digit number next to &ldquo;UPI&rdquo;.</p>
        </div>
    </details>

</div>

<script>
(function() {
    // Copy-to-clipboard for VPA / amount / note.
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const targetId = btn.getAttribute('data-copy');
            const el = document.getElementById(targetId);
            if (!el) return;
            const text = el.textContent.trim();
            try {
                await navigator.clipboard.writeText(text);
                const orig = btn.textContent;
                btn.textContent = 'Copied';
                btn.classList.add('text-emerald-400');
                setTimeout(() => {
                    btn.textContent = orig;
                    btn.classList.remove('text-emerald-400');
                }, 1500);
            } catch (e) { /* clipboard blocked — silent */ }
        });
    });

    // Soft 10-minute countdown — informational, doesn't actually expire.
    const label = document.getElementById('countdownLabel');
    if (label) {
        let secs = 600;
        const tick = () => {
            if (secs <= 0) { label.textContent = 'Reference still valid'; return; }
            const m = Math.floor(secs / 60);
            const s = String(secs % 60).padStart(2, '0');
            label.textContent = `Ref expires in ${m}:${s}`;
            secs--;
        };
        tick();
        setInterval(tick, 1000);
    }
})();
</script>

</body>
</html>
