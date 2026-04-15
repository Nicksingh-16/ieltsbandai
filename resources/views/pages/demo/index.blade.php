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

@php
    $calendlyUrl = 'https://calendly.com/nishantshekhawat2001';
    $demoEmail   = 'ieltsband25@gmail.com';
@endphp

{{-- Institute Banner --}}
<div class="bg-amber-500/10 border-b border-amber-500/30 px-4 py-2.5">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-sm text-amber-300">
            <span>🎓</span>
            <span><strong>Institute Demo</strong> — Submit one essay and see AI scoring live. Full report unlocks after a demo call.</span>
        </div>
        <a href="{{ $calendlyUrl }}" target="_blank"
           class="shrink-0 text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 px-3 py-1.5 rounded-lg transition-colors">
            Schedule Full Demo →
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
        <a href="{{ $calendlyUrl }}" target="_blank"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-400 hover:text-amber-300 transition-colors">
            📅 Schedule Demo for My Institute
        </a>
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

    {{-- Mode Selector --}}
    <div class="mb-8">
        <div class="flex items-center gap-1 bg-surface-800 border border-surface-700 p-1 rounded-xl w-fit">
            <button type="button" id="modePracticeBtn" onclick="setDemoMode('practice')"
                class="mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-surface-700 text-surface-50 border border-surface-500">
                📝 Practice Mode
            </button>
            <button type="button" id="modeExamBtn" onclick="setDemoMode('exam')"
                class="mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200">
                🎓 Exam Simulation
            </button>
        </div>
        <div id="practiceDesc" class="mt-3 text-xs text-surface-500">
            Dark practice interface with detailed AI feedback. Great for everyday practice.
        </div>
        <div id="examDesc" class="mt-3 text-xs text-surface-500 hidden">
            White IELTS computer-based test UI with fullscreen, anti-cheat, and strict timer. Identical to real exam conditions.
        </div>
    </div>

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

    @if($locked ?? false)

    {{-- ── Demo Used / Locked Gate ── --}}
    <div class="card p-8 text-center border border-amber-500/25 bg-gradient-to-br from-amber-500/5 to-surface-900 mb-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/15 flex items-center justify-center mx-auto mb-4 text-3xl">🔒</div>
        <h2 class="text-xl font-bold text-surface-50 mb-2">You've Already Used the Demo</h2>
        <p class="text-surface-400 text-sm mb-6 max-w-md mx-auto leading-relaxed">
            The free demo is limited to one essay submission per visitor. To see the full AI report — error highlights, examiner comments, vocabulary suggestions and Band 9 rewrite — schedule a demo call.
        </p>
        <a href="{{ $calendlyUrl }}" target="_blank"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-black font-bold px-6 py-3 rounded-xl text-sm transition-colors shadow-lg mb-3">
            📅 Schedule Full Demo for My Institute
        </a>
        <p class="text-surface-600 text-xs">
            Individual student?
            <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-semibold">Create a free account</a>
            for unlimited practice.
        </p>
    </div>

    @else

    <form method="POST" action="{{ route('demo.submit') }}" id="demoForm">
        @csrf
        <input type="hidden" name="demo_mode" id="demoModeInput" value="practice">

        {{-- Form wrapper — restyled by setDemoMode() --}}
        <div id="formWrapper" style="transition:background 0.2s;">

            {{-- Exam simulation header bar (hidden in practice mode) --}}
            <div id="examUiHeader" style="display:none;background:#003087;height:50px;align-items:center;padding:0 20px;font-family:Arial,sans-serif;border-radius:6px 6px 0 0;margin-bottom:0;">
                <span style="font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;min-width:160px;">IELTS Band AI</span>
                <span style="flex:1;text-align:center;font-size:14px;font-weight:bold;color:#fff;">Academic Writing Test — Task 2</span>
                <span style="min-width:160px;text-align:right;font-size:12px;color:rgba(255,255,255,0.7);">Demo Mode</span>
            </div>

            {{-- Exam toolbar (hidden in practice mode) --}}
            <div id="examUiToolbar" style="display:none;background:#F0F2F7;border-bottom:1px solid #D0D3DC;padding:7px 20px;font-family:Arial,sans-serif;align-items:center;gap:16px;">
                <span style="font-size:12px;color:#333;">Minimum words: <strong>250</strong></span>
                <span style="width:1px;height:14px;background:#C0C3CC;display:inline-block;"></span>
                <span style="font-size:12px;color:#555;">Word count: <strong id="wordCountExam">0</strong></span>
                <span style="width:1px;height:14px;background:#C0C3CC;display:inline-block;"></span>
                <span style="font-size:12px;color:#777;">⏱ 40:00 remaining (demo)</span>
            </div>

            {{-- Question Card --}}
            <div id="questionCard" class="card p-6 mb-5">
                {{-- Practice label --}}
                <div id="practiceLabelRow" class="flex items-start gap-3 mb-4">
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
                        <p class="text-surface-100 font-medium leading-snug" id="questionText">{{ $question->title }}</p>
                    </div>
                </div>
                {{-- Exam label (hidden in practice) --}}
                <div id="examLabelRow" style="display:none;margin-bottom:12px;">
                    <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Writing Task 2</div>
                    <p style="font-size:15px;font-weight:bold;color:#1a1a1a;line-height:1.4;" id="questionTextExam">{{ $question->title }}</p>
                </div>
                <div id="questionInstruction" class="bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-xs text-surface-400">
                    Write <strong class="text-surface-300">at least 250 words</strong> in about 40 minutes. Address all parts of the question.
                </div>
            </div>

            {{-- Editor --}}
            <div id="editorCard" class="card p-5 mb-5">
                <div id="practiceEditorHeader" class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Your Essay</span>
                    <div class="flex items-center gap-3 text-xs">
                        <span id="wordCount" class="text-surface-400">0 words</span>
                        <span id="wordCountStatus" class="tag bg-surface-700 text-surface-500 border-surface-600">need 250+</span>
                    </div>
                </div>
                <div id="examEditorHeader" style="display:none;padding:10px 0 8px;border-bottom:1px solid #E0E2EE;margin-bottom:8px;">
                    <span style="font-size:12px;color:#555;">Type your answer below</span>
                </div>
                <textarea
                    name="answer"
                    id="essayTextarea"
                    rows="16"
                    placeholder="Begin typing your answer here..."
                    class="w-full bg-surface-900 border border-surface-700 rounded-xl px-4 py-3.5 text-surface-100 text-sm leading-relaxed focus:outline-none focus:border-brand-500 resize-none transition-colors placeholder:text-surface-600"
                    oninput="updateWordCount(this)"
                    required
                >{{ old('answer') }}</textarea>
                @error('answer')
                <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div id="submitRow" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <p id="practiceSubmitHint" class="text-xs text-surface-500">
                    This uses the same GPT-4 engine as the full product. Results appear in ~15 seconds.
                </p>
                <p id="examWordBar" style="display:none;font-size:12px;color:#555;">
                    <span id="wordCountBarExam">0</span> words
                </p>
                <button type="submit" id="submitBtn"
                    class="shrink-0 inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-semibold px-6 py-3 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span id="submitText">Score My Essay</span>
                </button>
            </div>

        </div>{{-- end formWrapper --}}
    </form>

    @endif

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
            <a href="{{ $calendlyUrl }}" target="_blank"
               class="shrink-0 btn-primary text-sm">
                Book a Demo Call →
            </a>
        </div>
    </div>

</div>

{{-- Loading overlay --}}
<div id="loadingOverlay" class="fixed inset-0 z-50 bg-surface-950/97 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="text-center px-6 max-w-lg mx-auto">

        {{-- Spinner --}}
        <div class="w-20 h-20 rounded-full bg-brand-500/15 border border-brand-500/30 flex items-center justify-center mx-auto mb-7">
            <svg class="w-10 h-10 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>

        {{-- Scoring step --}}
        <h3 class="text-xl font-bold text-surface-50 mb-1">Scoring your essay…</h3>
        <p class="text-surface-500 text-xs mb-8">This takes about 10–20 seconds</p>

        {{-- Rotating feature cards --}}
        <div id="featureSlider" class="relative h-28">
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700" data-index="0">
                <div class="text-2xl mb-2">✍️</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">GPT-4 is reading your writing</p>
                <p class="text-surface-500 text-xs leading-relaxed">Applying official IELTS band descriptors across all 4 criteria — Task Achievement, Coherence, Lexical Resource, and Grammar.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="1">
                <div class="text-2xl mb-2">🎤</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">We evaluate all 4 IELTS modules</p>
                <p class="text-surface-500 text-xs leading-relaxed">Writing · Speaking · Reading · Listening — full AI evaluation across every module, not just writing.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="2">
                <div class="text-2xl mb-2">🏫</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">Built for IELTS coaching institutes</p>
                <p class="text-surface-500 text-xs leading-relaxed">Manage students, create batches, assign tests, and track every student's band progress — all from one dashboard.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="3">
                <div class="text-2xl mb-2">🎓</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">Real Exam Simulation Mode</p>
                <p class="text-surface-500 text-xs leading-relaxed">Pixel-accurate IELTS computer-based test UI — fullscreen, anti-cheat, strict timer, text highlighting, and question flagging. Just like the real exam.</p>
            </div>
        </div>

        {{-- Dot indicators --}}
        <div class="flex justify-center gap-2 mt-4">
            <div class="dot w-1.5 h-1.5 rounded-full bg-brand-400" data-dot="0"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="1"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="2"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="3"></div>
        </div>

    </div>
</div>

<script>
function setDemoMode(mode) {
    const isExam = mode === 'exam';

    // ── Mode toggle buttons ──
    document.getElementById('modePracticeBtn').className = isExam
        ? 'mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200'
        : 'mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-surface-700 text-surface-50 border border-surface-500';
    document.getElementById('modeExamBtn').className = isExam
        ? 'mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-[#003087] text-white border border-[#003087]'
        : 'mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200';

    // ── Description text ──
    document.getElementById('practiceDesc').classList.toggle('hidden', isExam);
    document.getElementById('examDesc').classList.toggle('hidden', !isExam);

    // ── Hidden mode input ──
    document.getElementById('demoModeInput').value = mode;

    // ── Form wrapper ──
    const fw = document.getElementById('formWrapper');
    if (isExam) {
        fw.style.cssText = 'background:#fff;border:1px solid #D0D3DC;border-radius:6px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;';
    } else {
        fw.style.cssText = '';
    }

    // ── Exam header/toolbar ──
    const eHeader = document.getElementById('examUiHeader');
    const eToolbar = document.getElementById('examUiToolbar');
    eHeader.style.display  = isExam ? 'flex' : 'none';
    eToolbar.style.display = isExam ? 'flex' : 'none';

    // ── Question card ──
    const qCard = document.getElementById('questionCard');
    if (isExam) {
        qCard.style.cssText = 'background:#F5F6FA;border:none;border-bottom:1px solid #D0D3DC;border-radius:0;margin:0;padding:20px 24px;';
    } else {
        qCard.style.cssText = '';
        qCard.className = 'card p-6 mb-5';
    }
    document.getElementById('practiceLabelRow').style.display  = isExam ? 'none' : '';
    document.getElementById('examLabelRow').style.display      = isExam ? 'block' : 'none';
    const qInstr = document.getElementById('questionInstruction');
    if (isExam) {
        qInstr.style.cssText = 'background:#EEF0F8;border:none;border-left:3px solid #003087;border-radius:0;padding:10px 14px;font-size:12px;color:#333;';
        qInstr.innerHTML = 'Write <strong>at least 250 words</strong> in about 40 minutes. Address all parts of the question.';
    } else {
        qInstr.style.cssText = '';
        qInstr.className = 'bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-xs text-surface-400';
        qInstr.innerHTML = 'Write <strong class="text-surface-300">at least 250 words</strong> in about 40 minutes. Address all parts of the question.';
    }

    // ── Editor card ──
    const eCard = document.getElementById('editorCard');
    if (isExam) {
        eCard.style.cssText = 'background:#FAFBFE;border:none;border-radius:0;margin:0;padding:0;';
    } else {
        eCard.style.cssText = '';
        eCard.className = 'card p-5 mb-5';
    }
    document.getElementById('practiceEditorHeader').style.display = isExam ? 'none' : '';
    document.getElementById('examEditorHeader').style.display     = isExam ? 'block' : 'none';

    // ── Textarea ──
    const ta = document.getElementById('essayTextarea');
    if (isExam) {
        ta.style.cssText = 'width:100%;background:#FAFBFE;border:none;border-bottom:1px solid #E0E2EE;border-radius:0;padding:16px 20px;color:#1a1a1a;font-size:14px;font-family:Arial,sans-serif;line-height:1.75;resize:none;outline:none;';
        ta.placeholder = 'Begin typing your answer here...';
    } else {
        ta.style.cssText = '';
        ta.className = 'w-full bg-surface-900 border border-surface-700 rounded-xl px-4 py-3.5 text-surface-100 text-sm leading-relaxed focus:outline-none focus:border-brand-500 resize-none transition-colors placeholder:text-surface-600';
        ta.placeholder = 'Start writing your essay here…\n\nRemember to:\n• Discuss both views clearly\n• Give and support your own opinion\n• Use varied vocabulary and grammar\n• Aim for 250–350 words';
    }

    // ── Submit row ──
    const sr = document.getElementById('submitRow');
    const btn = document.getElementById('submitBtn');
    if (isExam) {
        sr.style.cssText = 'background:#F0F2F7;border-top:1px solid #D0D3DC;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;';
        btn.style.cssText = 'background:#003087;color:#fff;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:8px 22px;border:none;border-radius:2px;cursor:pointer;';
        btn.className = '';
    } else {
        sr.style.cssText = '';
        sr.className = 'flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4';
        btn.style.cssText = '';
        btn.className = 'shrink-0 inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-semibold px-6 py-3 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed';
    }
    document.getElementById('practiceSubmitHint').style.display = isExam ? 'none' : '';
    document.getElementById('examWordBar').style.display        = isExam ? 'block' : 'none';
    document.getElementById('submitText').textContent = isExam ? 'Submit Writing →' : 'Score My Essay';
}

function updateWordCount(textarea) {
    const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
    // Sync exam word count displays
    const examEl = document.getElementById('wordCountExam');
    const barEl  = document.getElementById('wordCountBarExam');
    if (examEl) examEl.textContent = words;
    if (barEl)  barEl.textContent  = words;
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

// ── Feature slider ──
let sliderIndex = 0;
let sliderInterval = null;

function startSlider() {
    const slides = document.querySelectorAll('.feature-slide');
    const dots   = document.querySelectorAll('.dot');
    if (!slides.length) return;

    sliderInterval = setInterval(function () {
        // Hide current
        slides[sliderIndex].style.opacity = '0';
        slides[sliderIndex].style.pointerEvents = 'none';
        dots[sliderIndex].classList.replace('bg-brand-400', 'bg-surface-600');

        // Advance
        sliderIndex = (sliderIndex + 1) % slides.length;

        // Show next
        slides[sliderIndex].style.opacity = '1';
        slides[sliderIndex].style.pointerEvents = '';
        dots[sliderIndex].classList.replace('bg-surface-600', 'bg-brand-400');
    }, 3500);
}

const demoForm = document.getElementById('demoForm');
if (demoForm) demoForm.addEventListener('submit', function(e) {
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
    startSlider();
});

</script>

</body>
</html>
