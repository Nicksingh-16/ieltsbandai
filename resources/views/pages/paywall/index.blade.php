<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get more practice — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

@include('partials.beta-banner')

<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-600/6 rounded-full blur-3xl"></div>
</div>

<header class="sticky top-0 z-50 bg-surface-950/80 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="btn-ghost text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
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
        <div class="text-xs text-surface-500">
            @auth Credits: <span class="font-semibold text-brand-300">{{ Auth::user()->test_credits ?? 0 }}</span> @endauth
        </div>
    </div>
</header>

<div class="max-w-5xl mx-auto px-4 py-10 sm:py-14 space-y-10">

    {{-- ── Hero ─────────────────────────────────────────────────────────── --}}
    <div class="text-center max-w-2xl mx-auto">
        @if($context)
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-semibold uppercase tracking-wider mb-4">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                Out of free tests
            </div>
        @endif

        <h1 class="text-3xl sm:text-4xl font-bold text-surface-50 mb-3 leading-tight">
            You're @if($context)almost @endif there.<br>
            <span class="bg-gradient-to-r from-brand-300 to-purple-400 bg-clip-text text-transparent">Don't lose your momentum.</span>
        </h1>
        <p class="text-surface-400 text-base leading-relaxed">
            You've experienced what AI band scoring + examiner-style feedback can do.
            Pick how you'd like to keep going &mdash; pay-as-you-go for one more attempt,
            or unlock unlimited practice for less than the cost of a single coaching session.
        </p>

        {{-- Social proof strip --}}
        <div class="mt-6 inline-flex flex-wrap items-center justify-center gap-4 sm:gap-6 px-5 py-2.5 rounded-full bg-surface-800/60 border border-surface-700/60 text-xs">
            <span class="flex items-center gap-1.5 text-surface-300">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-semibold text-surface-100">{{ $beta['social_proof']['users_joined'] ?? 80 }}</span> joined this week
            </span>
            <span class="hidden sm:inline w-px h-3 bg-surface-700"></span>
            <span class="text-surface-400">
                <span class="font-semibold text-surface-100">{{ number_format($beta['social_proof']['tests_taken'] ?? 240) }}</span> tests scored
            </span>
            <span class="hidden sm:inline w-px h-3 bg-surface-700"></span>
            <span class="text-surface-400">Calibrated vs <span class="font-semibold text-surface-100">Cambridge IELTS</span></span>
        </div>
    </div>

    {{-- ── Beta urgency banner ─────────────────────────────────────────── --}}
    @if($beta['price_locks_in'] ?? false)
    <div class="card border-amber-500/30 bg-gradient-to-r from-amber-500/5 via-amber-500/10 to-amber-500/5 p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-100">Beta pricing locks in for the first {{ $beta['cap_users'] ?? 100 }} users</p>
            <p class="text-xs text-amber-300/80 mt-0.5">Today's prices stay yours forever, even after we 2x for public launch. Available to early users only.</p>
        </div>
    </div>
    @endif

    {{-- ── Monthly plans — the headline offers ─────────────────────────── --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-widest">Recommended &mdash; Unlimited monthly</h2>
            <div class="flex-1 h-px bg-surface-700/60"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Pro Monthly (the anchor + most-popular default) --}}
            @php $basic = $subscription['monthly_basic']; @endphp
            <div class="card relative p-6 border-brand-500/40 bg-gradient-to-b from-brand-500/5 to-surface-900 ring-1 ring-brand-500/30">
                @if(!empty($basic['badge']))
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-brand-700 text-white text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-brand-500/30">
                    {{ $basic['badge'] }}
                </div>
                @endif
                <p class="text-sm font-semibold text-brand-300 uppercase tracking-wider mb-1">{{ $basic['label'] }}</p>
                <p class="text-xs text-surface-400 mb-4">{{ $basic['subtitle'] }}</p>
                <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-4xl font-extrabold text-surface-50">{{ $symbol }}{{ $basic['price'] }}</span>
                    <span class="text-sm text-surface-500">/month</span>
                </div>
                <p class="text-xs text-emerald-400 font-medium mb-5">
                    Just {{ $symbol }}{{ round($basic['price'] / 30, 1) }} per day &mdash; less than one chai.
                </p>
                <ul class="space-y-2 text-sm mb-6">
                    @foreach($basic['features'] as $feature)
                    <li class="flex items-start gap-2 text-surface-300">
                        <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('paywall.start') }}" class="block">
                    @csrf
                    <input type="hidden" name="plan" value="monthly_basic">
                    <button type="submit" class="btn-primary w-full justify-center text-sm py-2.5">
                        Unlock unlimited &mdash; {{ $symbol }}{{ $basic['price'] }}
                    </button>
                </form>
                <p class="text-[11px] text-surface-500 text-center mt-3">No auto-renewal &middot; cancel anytime &middot; {{ $beta['guarantee_days'] ?? 7 }}-day refund</p>
            </div>

            {{-- Pro Plus Monthly (premium accuracy) --}}
            @php $premium = $subscription['monthly_premium']; @endphp
            <div class="card relative p-6 border-purple-500/30 bg-gradient-to-b from-purple-500/5 to-surface-900">
                @if(!empty($premium['badge']))
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-purple-500 to-purple-700 text-white text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-purple-500/30">
                    {{ $premium['badge'] }}
                </div>
                @endif
                <p class="text-sm font-semibold text-purple-300 uppercase tracking-wider mb-1">{{ $premium['label'] }}</p>
                <p class="text-xs text-surface-400 mb-4">{{ $premium['subtitle'] }}</p>
                <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-4xl font-extrabold text-surface-50">{{ $symbol }}{{ $premium['price'] }}</span>
                    <span class="text-sm text-surface-500">/month</span>
                </div>
                <p class="text-xs text-purple-300 font-medium mb-5">
                    {{ $premium['anchor_text'] ?? 'For aspirants targeting Band 7+' }}
                </p>
                <ul class="space-y-2 text-sm mb-6">
                    @foreach($premium['features'] as $feature)
                    <li class="flex items-start gap-2 text-surface-300">
                        @if($loop->index === 1)
                            <svg class="w-4 h-4 text-purple-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                        @else
                            <svg class="w-4 h-4 text-purple-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('paywall.start') }}" class="block">
                    @csrf
                    <input type="hidden" name="plan" value="monthly_premium">
                    <button type="submit" class="w-full justify-center text-sm py-2.5 rounded-lg bg-gradient-to-r from-purple-500 to-purple-700 hover:from-purple-400 hover:to-purple-600 text-white font-semibold transition flex items-center gap-2">
                        Upgrade to premium &mdash; {{ $symbol }}{{ $premium['price'] }}
                    </button>
                </form>
                <p class="text-[11px] text-surface-500 text-center mt-3">Same cancellation + refund terms</p>
            </div>
        </div>
    </div>

    {{-- ── One-time tests — the de-anchor that makes monthly look obvious ─ --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-widest">Just need one more test?</h2>
            <div class="flex-1 h-px bg-surface-700/60"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($oneTime as $key => $plan)
            @php
                $isHighlightedContext = $context && (
                    ($context === 'writing'   && $key === 'single_writing') ||
                    ($context === 'speaking'  && $key === 'single_speaking') ||
                    (in_array($context, ['listening', 'reading'], true) && $key === 'single_full')
                );
            @endphp
            <div class="card p-4 relative {{ $isHighlightedContext ? 'border-brand-500/40 ring-1 ring-brand-500/20' : '' }}">
                @if(!empty($plan['badge']))
                <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">{{ $plan['badge'] }}</span>
                @endif
                <p class="text-sm font-semibold text-surface-100 mb-0.5">{{ $plan['label'] }}</p>
                <p class="text-[11px] text-surface-500 mb-3 leading-tight">{{ $plan['subtitle'] }}</p>
                <div class="flex items-baseline gap-1 mb-3">
                    <span class="text-2xl font-bold text-surface-50">{{ $symbol }}{{ $plan['price'] }}</span>
                    <span class="text-[11px] text-surface-500">one-time</span>
                </div>
                <form method="POST" action="{{ route('paywall.start') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <button type="submit" class="btn-secondary w-full justify-center text-xs py-2">
                        Buy &mdash; {{ $symbol }}{{ $plan['price'] }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- The crux of the anchoring: make the math obvious --}}
        <p class="text-center text-xs text-surface-500 mt-4 leading-relaxed">
            <span class="text-amber-300">Heads up:</span>
            5 single Writing tests already cost
            <span class="text-surface-200 font-semibold">{{ $symbol }}45</span> &mdash;
            for half that, Pro Monthly gets you
            <span class="text-brand-300 font-semibold">unlimited</span> tests across all 4 modules.
        </p>
    </div>

    {{-- ── Trust strip ─────────────────────────────────────────────────── --}}
    <div class="card p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[11px] text-surface-300 font-medium">{{ $beta['guarantee_days'] ?? 7 }}-day refund</p>
                <p class="text-[10px] text-surface-500">No questions asked</p>
            </div>
            <div>
                <div class="w-8 h-8 rounded-lg bg-brand-500/10 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                </div>
                <p class="text-[11px] text-surface-300 font-medium">UPI payment</p>
                <p class="text-[10px] text-surface-500">Any UPI app</p>
            </div>
            <div>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <p class="text-[11px] text-surface-300 font-medium">Instant access</p>
                <p class="text-[10px] text-surface-500">No waiting</p>
            </div>
            <div>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-[11px] text-surface-300 font-medium">Beta locked-in</p>
                <p class="text-[10px] text-surface-500">Price never goes up</p>
            </div>
        </div>
    </div>

    {{-- ── FAQ ─────────────────────────────────────────────────────────── --}}
    <div class="card p-6">
        <h3 class="text-surface-50 font-bold text-base mb-4">Common questions</h3>
        <div class="space-y-4 text-sm">
            <div>
                <p class="font-semibold text-surface-100 mb-1">Is the AI scoring accurate?</p>
                <p class="text-surface-400 text-xs leading-relaxed">Calibrated against Cambridge IELTS published model answers. Most accurate in the Band 5&ndash;7.5 range; conservative at Band 8+ (your true score may be 0.5&ndash;1.0 higher on an official test).</p>
            </div>
            <div>
                <p class="font-semibold text-surface-100 mb-1">How does payment work without a card?</p>
                <p class="text-surface-400 text-xs leading-relaxed">Scan a UPI QR with any payment app (PhonePe, GPay, Paytm, BHIM). Pay, then paste the transaction ID. Your plan activates instantly &mdash; we verify the payment within 24 hours.</p>
            </div>
            <div>
                <p class="font-semibold text-surface-100 mb-1">What's the difference between Pro and Pro Plus?</p>
                <p class="text-surface-400 text-xs leading-relaxed">Pro uses our standard AI model (works great for Band 5&ndash;7.5 students). Pro Plus uses a premium model with measurably better accuracy at Band 7.5+ &mdash; worth it if you're already a strong writer chasing the last half-band.</p>
            </div>
            <div>
                <p class="font-semibold text-surface-100 mb-1">Can I cancel?</p>
                <p class="text-surface-400 text-xs leading-relaxed">Yes &mdash; no auto-renewal in beta. You only get charged for what you actively buy. Full refund within {{ $beta['guarantee_days'] ?? 7 }} days, no questions.</p>
            </div>
        </div>
    </div>

</div>

</body>
</html>
