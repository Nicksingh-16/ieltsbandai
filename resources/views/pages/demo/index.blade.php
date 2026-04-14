<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Demo — AI Writing Scoring | IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-950 text-surface-200 font-sans antialiased">

{{-- Ambient glow --}}
<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-600/6 rounded-full blur-3xl"></div>
</div>

{{-- Demo Banner --}}
<div class="bg-brand-500/10 border-b border-brand-500/30 px-4 py-2.5">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-sm text-brand-300">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span><strong>Live Demo</strong> — No account needed. Real GPT-4 scoring on your actual essay.</span>
        </div>
        <a href="{{ route('register') }}" class="shrink-0 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 px-3 py-1.5 rounded-lg transition-colors">
            Create Free Account →
        </a>
    </div>
</div>

{{-- Header --}}
<header class="sticky top-0 z-40 bg-surface-900/90 backdrop-blur border-b border-surface-700/50">
    <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-sm text-surface-400 hover:text-surface-200 transition-colors">Sign In</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm">Get Started Free</a>
        </div>
    </div>
</header>

<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Error --}}
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm mb-6 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- What you get banner --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        @foreach([
            ['Band score 0–9', 'text-brand-400', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['Criteria breakdown', 'text-purple-400', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Error highlights', 'text-amber-400', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['Improvement tips', 'text-emerald-400', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ] as [$label, $color, $icon])
        <div class="card p-3 flex items-center gap-2.5">
            <svg class="w-4 h-4 {{ $color }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
            <span class="text-xs text-surface-300 font-medium">{{ $label }}</span>
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('demo.submit') }}" id="demoForm">
        @csrf

        {{-- Question Card --}}
        <div class="card p-6 mb-5">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-surface-500 uppercase tracking-wider">IELTS Writing Task 2 — Academic</span>
                        <span class="tag-cyan text-[10px]">Demo Question</span>
                    </div>
                    <p class="text-surface-100 font-medium leading-snug">{{ $question->title }}</p>
                </div>
            </div>
            <div class="bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-xs text-surface-400">
                Write <strong class="text-surface-300">at least 250 words</strong> in about 40 minutes. Address all parts of the question.
            </div>
        </div>

        {{-- Editor --}}
        <div class="card p-5 mb-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Your Essay</span>
                <div class="flex items-center gap-3 text-xs">
                    <span id="wordCount" class="text-surface-400">0 words</span>
                    <span id="wordCountStatus" class="tag bg-surface-700 text-surface-500 border-surface-600">need 250+</span>
                </div>
            </div>
            <textarea
                name="answer"
                id="essayTextarea"
                rows="16"
                placeholder="Start writing your essay here…&#10;&#10;Remember to:&#10;• Discuss both views clearly&#10;• Give and support your own opinion&#10;• Use varied vocabulary and grammar&#10;• Aim for 250–350 words"
                class="w-full bg-surface-900 border border-surface-700 rounded-xl px-4 py-3.5 text-surface-100 text-sm leading-relaxed focus:outline-none focus:border-brand-500 resize-none transition-colors placeholder:text-surface-600"
                oninput="updateWordCount(this)"
                required
            >{{ old('answer') }}</textarea>
            @error('answer')
            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <p class="text-xs text-surface-500">
                This uses the same GPT-4 engine as the full product. Results appear in ~15 seconds.
            </p>
            <button type="submit" id="submitBtn"
                class="shrink-0 inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-semibold px-6 py-3 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span id="submitText">Score My Essay</span>
            </button>
        </div>
    </form>

    {{-- Institute CTA --}}
    <div class="mt-10 card p-6 border border-indigo-500/20 bg-gradient-to-r from-indigo-500/5 to-surface-900">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-surface-50 mb-1">Running an IELTS coaching institute?</h3>
                <p class="text-surface-400 text-sm">Get AI scoring for all your students. Manage batches, assign tests, track progress — all in one dashboard.</p>
            </div>
            <a href="mailto:hello@ieltsbandai.com?subject=Institute%20Demo%20Request"
               class="shrink-0 btn-primary text-sm">
                Book a Demo Call →
            </a>
        </div>
    </div>

</div>

{{-- Loading overlay --}}
<div id="loadingOverlay" class="fixed inset-0 z-50 bg-surface-950/95 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="text-center px-6">
        <div class="w-20 h-20 rounded-full bg-brand-500/15 border border-brand-500/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-surface-50 mb-2">Scoring your essay…</h3>
        <p class="text-surface-400 text-sm mb-1">GPT-4 is reading your writing and applying IELTS band descriptors.</p>
        <p class="text-surface-600 text-xs">This takes about 10–20 seconds</p>
    </div>
</div>

<script>
function updateWordCount(textarea) {
    const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
    const el     = document.getElementById('wordCount');
    const status = document.getElementById('wordCountStatus');
    el.textContent = words + ' word' + (words !== 1 ? 's' : '');
    if (words >= 250) {
        el.classList.add('text-emerald-400');
        el.classList.remove('text-surface-400', 'text-amber-400');
        status.className = 'tag tag-green text-[10px]';
        status.textContent = 'ready';
    } else if (words >= 150) {
        el.classList.add('text-amber-400');
        el.classList.remove('text-surface-400', 'text-emerald-400');
        status.className = 'tag bg-amber-500/15 text-amber-400 border-amber-500/30 text-[10px]';
        status.textContent = 'need ' + (250 - words) + ' more';
    } else {
        el.classList.add('text-surface-400');
        el.classList.remove('text-amber-400', 'text-emerald-400');
        status.className = 'tag bg-surface-700 text-surface-500 border-surface-600 text-[10px]';
        status.textContent = 'need 250+';
    }
}

document.getElementById('demoForm').addEventListener('submit', function(e) {
    const textarea = document.getElementById('essayTextarea');
    const words = textarea.value.trim().split(/\s+/).length;
    if (words < 50) {
        e.preventDefault();
        textarea.focus();
        return;
    }
    document.getElementById('loadingOverlay').classList.remove('hidden');
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitText').textContent = 'Scoring…';
});
</script>

</body>
</html>
