<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->order_id }} &mdash; IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background: white !important; color: #111 !important; }
            .no-print { display: none !important; }
            .card { background: white !important; border: 1px solid #d1d5db !important; box-shadow: none !important; }
            .receipt-bg { background: white !important; }
            .receipt-text { color: #111 !important; }
            .receipt-muted { color: #555 !important; }
        }
    </style>
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

<header class="sticky top-0 z-50 bg-surface-950/80 backdrop-blur-md border-b border-surface-700/50 no-print">
    <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="btn-ghost text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <button onclick="window.print()" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
        </button>
    </div>
</header>

<div class="max-w-2xl mx-auto px-4 py-8 space-y-5">

    {{-- ── Success hero ───────────────────────────────────────────────── --}}
    @if($payment->granted_at)
    <div class="text-center py-6">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500/40 mx-auto mb-4 flex items-center justify-center">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-surface-50 mb-1 receipt-text">Payment received</h1>
        <p class="text-sm text-surface-400 receipt-muted">Your plan is active &mdash; start practising right now.</p>
    </div>
    @else
    <div class="text-center py-6">
        <div class="w-16 h-16 rounded-full bg-amber-500/20 border-2 border-amber-500/40 mx-auto mb-4 flex items-center justify-center">
            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-surface-50 mb-1">Waiting for payment</h1>
        <p class="text-sm text-surface-400">
            We haven't received your UTR yet. <a href="{{ route('paywall.pay', $payment->order_id) }}" class="text-brand-400 hover:underline">Go back and submit it</a>.
        </p>
    </div>
    @endif

    {{-- ── The receipt itself ────────────────────────────────────────── --}}
    <div class="card receipt-bg p-6 space-y-5">

        {{-- Brand strip --}}
        <div class="flex items-center justify-between pb-4 border-b border-surface-700">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-surface-50 receipt-text">IELTS Band AI</p>
                    <p class="text-[10px] text-surface-500 receipt-muted">ieltsbandai.online</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted">Receipt</p>
                <p class="text-xs font-mono text-surface-200 receipt-text">{{ $payment->order_id }}</p>
            </div>
        </div>

        {{-- Buyer + date --}}
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted mb-1">Billed to</p>
                <p class="text-surface-200 receipt-text font-medium">{{ $user->name }}</p>
                <p class="text-surface-500 receipt-muted text-[11px]">{{ $user->email }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted mb-1">Date</p>
                <p class="text-surface-200 receipt-text">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                @if($payment->verified_at)
                    <p class="text-emerald-400 text-[11px] mt-1">&check; Verified</p>
                @elseif($payment->granted_at)
                    <p class="text-amber-400 text-[11px] mt-1">Verifying within 24h</p>
                @endif
            </div>
        </div>

        {{-- Line item --}}
        <div class="border-t border-surface-700 pt-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted">
                        <th class="text-left pb-2 font-semibold">Item</th>
                        <th class="text-right pb-2 font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 align-top">
                            <p class="text-surface-100 receipt-text font-medium">{{ $plan['label'] ?? $payment->plan }}</p>
                            <p class="text-surface-500 receipt-muted text-[11px] mt-0.5">{{ $plan['subtitle'] ?? '' }}</p>
                        </td>
                        <td class="py-2 text-right align-top">
                            <p class="text-surface-100 receipt-text font-medium">{{ config('plans.currency_symbol') }}{{ number_format($payment->amount, 2) }}</p>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-surface-700">
                        <td class="pt-3 text-sm font-semibold text-surface-200 receipt-text">Total paid</td>
                        <td class="pt-3 text-right text-lg font-bold text-surface-50 receipt-text">{{ config('plans.currency_symbol') }}{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Payment method --}}
        <div class="border-t border-surface-700 pt-4 grid grid-cols-2 gap-4 text-xs">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted mb-1">Payment method</p>
                <p class="text-surface-200 receipt-text">UPI &middot; manual</p>
            </div>
            @if($payment->proof_id)
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted mb-1">UTR</p>
                <p class="text-surface-200 receipt-text font-mono text-[11px]">{{ $payment->proof_id }}</p>
            </div>
            @endif
        </div>

        {{-- What you got --}}
        @if($plan)
        <div class="border-t border-surface-700 pt-4">
            <p class="text-[10px] uppercase tracking-wider text-surface-500 receipt-muted mb-2">Activated</p>
            <ul class="space-y-1.5 text-xs">
                @foreach($plan['features'] ?? [] as $feature)
                <li class="flex items-start gap-2 text-surface-300 receipt-text">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>{{ $feature }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Footer note --}}
        <div class="border-t border-surface-700 pt-3 text-[10px] text-surface-500 receipt-muted text-center leading-relaxed">
            Questions? Email <span class="text-surface-300 receipt-text">ronnie@vedcool.com</span>.
            For refund within {{ config('plans.beta.guarantee_days', 7) }} days, reply to your welcome email.
        </div>
    </div>

    {{-- ── Next-step CTA ─────────────────────────────────────────────── --}}
    @if($payment->granted_at)
    <div class="no-print grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a href="{{ route('writing.index') }}" class="btn-primary justify-center text-sm py-2.5">
            Start a Writing test
        </a>
        <a href="{{ route('home') }}" class="btn-secondary justify-center text-sm py-2.5">
            Back to dashboard
        </a>
    </div>
    @endif

</div>

</body>
</html>
