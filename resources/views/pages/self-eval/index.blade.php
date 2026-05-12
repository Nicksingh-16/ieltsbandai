<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Evaluate your essay — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

@include('partials.beta-banner')

<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
</div>

<header class="sticky top-0 z-50 bg-surface-950/80 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="btn-ghost text-sm flex items-center gap-1.5">
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
        <div class="text-xs">
            @if($isPro)
                <span class="px-2 py-1 rounded-full bg-purple-500/20 text-purple-300 font-semibold">Pro · Unlimited</span>
            @else
                <span class="text-surface-500">Evaluations left: <span class="font-semibold text-brand-300">{{ $remaining }}</span></span>
            @endif
        </div>
    </div>
</header>

<div class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-6">

    {{-- Hero --}}
    <div class="text-center max-w-xl mx-auto">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-300 text-xs font-semibold uppercase tracking-wider mb-4">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Paste &amp; evaluate
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-surface-50 mb-2 leading-tight">
            Evaluate any IELTS Task 2 essay
        </h1>
        <p class="text-surface-400 text-sm leading-relaxed">
            Paste a Task 2 question and your written response. You'll get the full IELTS-style
            band breakdown (TA, CC, LR, GRA), error highlighting, and a Band&nbsp;9 model rewrite —
            the same engine that scores our real tests.
        </p>
        @unless($isPro)
        <p class="text-xs text-surface-500 mt-3">
            First <span class="font-semibold text-emerald-400">5 evaluations are free</span>.
            After that, each one is ₹10 via UPI.
        </p>
        @endunless
    </div>

    @if(session('error'))
    <div class="card border-red-500/30 bg-red-500/5 p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('self-eval.evaluate') }}" id="evalForm" class="card p-5 sm:p-6 space-y-5">
        @csrf

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="question" class="text-xs font-semibold text-surface-300 uppercase tracking-wider">
                    1. Task 2 question / prompt
                </label>
                <span class="text-[11px] text-surface-500" id="qChars">0 chars</span>
            </div>
            <textarea
                name="question"
                id="question"
                rows="4"
                required
                minlength="30"
                maxlength="2000"
                class="w-full px-3 py-2.5 rounded-lg bg-surface-800 border border-surface-700 focus:border-brand-500 focus:outline-none text-sm text-surface-100 placeholder:text-surface-600 leading-relaxed"
                placeholder="Paste the full Task 2 prompt here. e.g. 'Some people think that young people should be required to do a period of community service…  Do you agree or disagree? Give reasons for your answer.'"
            >{{ old('question') }}</textarea>
            @error('question')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="answer" class="text-xs font-semibold text-surface-300 uppercase tracking-wider">
                    2. Your essay
                </label>
                <span class="text-[11px]" id="aWords">
                    <span class="text-surface-500" id="aWordCount">0</span>
                    <span class="text-surface-600">words · 250+ for Task 2</span>
                </span>
            </div>
            <textarea
                name="answer"
                id="answer"
                rows="14"
                required
                minlength="100"
                maxlength="8000"
                class="w-full px-3 py-2.5 rounded-lg bg-surface-800 border border-surface-700 focus:border-brand-500 focus:outline-none text-sm text-surface-100 placeholder:text-surface-600 leading-relaxed font-serif"
                placeholder="Paste your full essay response here. We score it against the official IELTS Writing Task 2 band descriptors — TA, CC, LR, GRA."
            >{{ old('answer') }}</textarea>
            @error('answer')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2 border-t border-surface-700/50">
            <button type="submit" id="submitBtn" class="btn-primary justify-center text-sm py-2.5 sm:flex-1">
                <span id="submitLabel">Evaluate my essay</span>
                <svg id="submitSpin" class="hidden w-4 h-4 animate-spin ml-2" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" class="opacity-75"/></svg>
            </button>
            <p class="text-[11px] text-surface-500 text-center sm:text-right sm:max-w-[200px]">
                Scoring takes ~15 seconds.<br>One credit will be used.
            </p>
        </div>
    </form>

    {{-- Trust strip --}}
    <div class="grid grid-cols-3 gap-3 text-center text-xs">
        <div class="card p-3">
            <p class="font-semibold text-surface-200">Cambridge-calibrated</p>
            <p class="text-surface-500 text-[11px] mt-0.5">Anchored to published model essays</p>
        </div>
        <div class="card p-3">
            <p class="font-semibold text-surface-200">Same engine</p>
            <p class="text-surface-500 text-[11px] mt-0.5">As the live exam scoring</p>
        </div>
        <div class="card p-3">
            <p class="font-semibold text-surface-200">~15 seconds</p>
            <p class="text-surface-500 text-[11px] mt-0.5">No queue, no waiting</p>
        </div>
    </div>

    @unless($isPro)
    {{-- Soft upsell — only shown to non-pro --}}
    @if(is_numeric($remaining) && $remaining <= 2)
    <div class="card border-amber-500/30 bg-amber-500/5 p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
        <div class="flex-1">
            <p class="text-sm font-semibold text-amber-100">
                @if($remaining === 0)
                    You've used all 5 free evaluations
                @else
                    Only {{ $remaining }} {{ $remaining === 1 ? 'evaluation' : 'evaluations' }} left
                @endif
            </p>
            <p class="text-xs text-amber-300/80 mt-0.5">
                Buy another at ₹10 each, or unlock unlimited evaluations with Pro Monthly (₹99/month).
            </p>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('paywall.index', ['from' => 'self_eval']) }}" class="text-xs px-3 py-1.5 rounded-lg bg-amber-500 text-surface-950 font-semibold hover:bg-amber-400">See plans</a>
            </div>
        </div>
    </div>
    @endif
    @endunless

</div>

<script>
(function () {
    const question  = document.getElementById('question');
    const answer    = document.getElementById('answer');
    const qChars    = document.getElementById('qChars');
    const aWordCount= document.getElementById('aWordCount');

    const updateQ = () => { qChars.textContent = (question.value || '').length + ' chars'; };
    const updateA = () => {
        const words = (answer.value || '').trim().split(/\s+/).filter(Boolean).length;
        aWordCount.textContent = words;
        aWordCount.className = words >= 250 ? 'text-emerald-400 font-semibold' : 'text-surface-500';
    };

    question.addEventListener('input', updateQ);
    answer.addEventListener('input', updateA);
    updateQ(); updateA();

    // Disable submit button + show spinner on submit so users don't double-click.
    document.getElementById('evalForm').addEventListener('submit', () => {
        const btn = document.getElementById('submitBtn');
        const label = document.getElementById('submitLabel');
        const spin = document.getElementById('submitSpin');
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        label.textContent = 'Scoring your essay…';
        spin.classList.remove('hidden');
    });
})();
</script>

</body>
</html>
