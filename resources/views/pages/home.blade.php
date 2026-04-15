<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Band AI — Know Your Band Score Before the Real Exam</title>
    <meta name="description" content="Practice IELTS with full mock tests and get instant AI band score predictions. Writing feedback, speaking evaluation, and real exam simulation. Free to start.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Hero UI mockup styles ── */
        .mock-error {
            background: rgba(239,68,68,0.12);
            border-bottom: 2px solid #ef4444;
            color: #fca5a5;
            cursor: default;
            position: relative;
        }
        .mock-correction {
            background: rgba(16,185,129,0.12);
            border-bottom: 2px solid #10b981;
            color: #6ee7b7;
        }
        .score-ring-bg  { stroke: #1e293b; }
        .score-ring-fg  { stroke: #06b6d4; stroke-linecap: round; transition: stroke-dashoffset 1s ease; }
        .score-ring-warn { stroke: #f59e0b; }

        /* ── Floating label chip ── */
        .problem-number {
            width: 2rem; height: 2rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #06b6d4, #0e7490);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: #fff;
            flex-shrink: 0;
        }

        /* ── Subtle grid pattern ── */
        .grid-bg {
            background-image:
                linear-gradient(rgba(6,182,212,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6,182,212,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ── Feature tab active ── */
        .feat-tab.active { background: rgba(6,182,212,0.12); color: #67e8f9; border-color: rgba(6,182,212,0.35); }
        .feat-tab { cursor: pointer; transition: all 0.2s; }

        /* ── Noise texture for hero ── */
        .hero-glow::before {
            content: '';
            position: absolute;
            top: -200px; left: 50%; transform: translateX(-50%);
            width: 900px; height: 600px;
            background: radial-gradient(ellipse at center, rgba(6,182,212,0.10) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Section separator */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(51,65,85,0.8), transparent);
        }

        /* Typing cursor blink */
        @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0; } }
        .cursor { animation: blink 1s step-end infinite; }

        /* Floating card animation */
        @keyframes floatUp { 0%,100% { transform: translateY(0px); } 50% { transform: translateY(-6px); } }
        .float-card { animation: floatUp 4s ease-in-out infinite; }
        .float-card-delayed { animation: floatUp 4s ease-in-out infinite; animation-delay: 2s; }
    </style>
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased overflow-x-hidden">

{{-- ════════════════════════════════════════
     STICKY NAVIGATION
════════════════════════════════════════ --}}
<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" style="background: transparent;">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                    <svg class="w-4.5 h-4.5 text-white" style="width:18px;height:18px" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                </div>
                <span class="font-bold text-surface-50 text-[15px] tracking-tight">IELTS Band <span class="text-brand-400">AI</span></span>
            </a>

            {{-- Nav links (desktop) --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="#features" class="text-sm text-surface-400 hover:text-surface-100 px-3 py-2 rounded-lg hover:bg-surface-800/60 transition-all">Features</a>
                <a href="#how-it-works" class="text-sm text-surface-400 hover:text-surface-100 px-3 py-2 rounded-lg hover:bg-surface-800/60 transition-all">How it works</a>
                <a href="#exam-simulation" class="text-sm text-amber-400 hover:text-amber-300 px-3 py-2 rounded-lg hover:bg-amber-500/10 transition-all font-medium">Exam Simulation ✦</a>
                <a href="#sample-result" class="text-sm text-surface-400 hover:text-surface-100 px-3 py-2 rounded-lg hover:bg-surface-800/60 transition-all">Sample result</a>
                <a href="{{ route('pricing') }}" class="text-sm text-surface-400 hover:text-surface-100 px-3 py-2 rounded-lg hover:bg-surface-800/60 transition-all">Pricing</a>
            </nav>

            {{-- CTAs --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm text-surface-400 hover:text-surface-100 px-3 py-2 rounded-lg hover:bg-surface-800/60 transition-all font-medium">Sign in</a>
                <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2 shadow-glow">
                    Start Free
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>

{{-- Scroll-to-color header script --}}
<script>
    window.addEventListener('scroll', () => {
        const h = document.getElementById('site-header');
        if (window.scrollY > 40) {
            h.style.background = 'rgba(15,23,42,0.92)';
            h.style.borderBottom = '1px solid rgba(51,65,85,0.5)';
            h.style.backdropFilter = 'blur(12px)';
        } else {
            h.style.background = 'transparent';
            h.style.borderBottom = 'none';
            h.style.backdropFilter = 'none';
        }
    });
</script>


{{-- ════════════════════════════════════════
     SECTION 1 — HERO
════════════════════════════════════════ --}}
<section class="relative hero-glow min-h-screen flex items-center pt-16 pb-8 overflow-hidden grid-bg">

    {{-- Deep background glow orbs --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-20 w-64 h-64 bg-purple-600/6 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-48 h-48 bg-brand-700/8 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-5 sm:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left: Copy --}}
            <div class="text-center lg:text-left">

                {{-- Trust badge --}}
                <div class="inline-flex items-center gap-2 bg-surface-800/80 border border-surface-600/60 text-surface-300 text-xs font-medium px-3.5 py-1.5 rounded-full mb-7 backdrop-blur-sm">
                    <div class="flex -space-x-1.5">
                        @foreach(['bg-brand-500','bg-purple-500','bg-amber-500','bg-emerald-500'] as $c)
                        <div class="w-5 h-5 rounded-full {{ $c }} border-2 border-surface-800"></div>
                        @endforeach
                    </div>
                    <span>10,000+ students practice here</span>
                </div>

                {{-- Main headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-surface-50 leading-[1.08] tracking-tight mb-5">
                    Know Your Real<br>
                    IELTS Band Score<br>
                    <span class="text-gradient">Before Test Day.</span>
                </h1>

                <p class="text-base sm:text-lg text-surface-400 leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                    Take full IELTS mock tests online. Get instant AI feedback on your writing and speaking — just like a real examiner. No coaching needed.
                </p>

                {{-- CTAs --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-10">
                    <a href="{{ route('register') }}" class="btn-primary text-base px-7 py-3.5 shadow-glow-lg font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Start Free Mock Test
                    </a>
<a href="#sample-result" class="btn-secondary text-base px-7 py-3.5 font-semibold">
                        See Sample Feedback
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                </div>

                {{-- Micro-trust --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-6 gap-y-2 text-xs text-surface-500">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Free to start — no card needed
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        All 4 IELTS skills
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Result in under 60 seconds
                    </span>
                </div>
            </div>

            {{-- Right: Hero UI Mockup --}}
            <div class="relative flex justify-center lg:justify-end">

                {{-- Main writing feedback card --}}
                <div class="relative w-full max-w-md">

                    {{-- Floating band score chip --}}
                    <div class="float-card absolute -top-4 -left-4 z-20 bg-surface-800 border border-surface-600 rounded-2xl px-4 py-3 shadow-card-hover">
                        <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-0.5">Overall Band</p>
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-black text-surface-50">7.0</span>
                            <div class="mb-1">
                                <div class="flex gap-0.5 mb-0.5">
                                    @for($i=0;$i<5;$i++)<div class="w-4 h-1 rounded-full {{ $i < 4 ? 'bg-brand-500' : 'bg-surface-600' }}"></div>@endfor
                                </div>
                                <p class="text-[9px] text-brand-400 font-semibold">Good</p>
                            </div>
                        </div>
                    </div>

                    {{-- Main card --}}
                    <div class="bg-surface-800 border border-surface-600 rounded-2xl shadow-card-hover overflow-hidden">

                        {{-- Card top bar --}}
                        <div class="bg-surface-900/80 border-b border-surface-700 px-4 py-3 flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500/70"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-500/70"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/70"></div>
                            <span class="ml-2 text-[11px] text-surface-500 font-medium">Writing Task 2 — AI Feedback</span>
                        </div>

                        <div class="p-5 space-y-4">

                            {{-- Essay excerpt with error highlighting --}}
                            <div>
                                <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-2 font-semibold">Your Essay</p>
                                <div class="bg-surface-900/60 rounded-xl p-4 text-sm text-surface-300 leading-7 font-mono text-[12px]">
                                    Technology <span class="mock-error px-0.5 rounded" title="effect → affects">effect</span> every part of our lives today. Many people <span class="mock-error px-0.5 rounded">beleive</span> that social media has make us <span class="mock-error px-0.5 rounded">less social</span> in real world. <span class="mock-correction px-0.5 rounded">However, others argue</span> that it connects people across distances<span class="cursor">|</span>
                                </div>
                            </div>

                            {{-- Error count strip --}}
                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1 bg-red-500/10 border border-red-500/20 text-red-300 px-2.5 py-1 rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8" fill="rgba(239,68,68,0.3)"/><path d="M10 6v4m0 4h.01" stroke="#fca5a5" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    3 errors found
                                </span>
                                <span class="flex items-center gap-1 bg-amber-500/10 border border-amber-500/20 text-amber-300 px-2.5 py-1 rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Word choice
                                </span>
                            </div>

                            {{-- 4-criteria band bars --}}
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([['Task', 7.0,'brand'],['Coherence', 7.0,'brand'],['Lexical', 6.5,'purple'],['Grammar', 7.5,'emerald']] as [$crit,$score,$color])
                                <div class="bg-surface-900/50 rounded-xl p-3">
                                    <div class="flex justify-between items-center mb-1.5">
                                        <span class="text-[10px] text-surface-500 font-medium">{{ $crit }}</span>
                                        <span class="text-[11px] font-bold {{ $color === 'emerald' ? 'text-emerald-400' : ($color === 'purple' ? 'text-purple-400' : 'text-brand-400') }}">{{ $score }}</span>
                                    </div>
                                    <div class="h-1 bg-surface-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $color === 'emerald' ? 'bg-emerald-500' : ($color === 'purple' ? 'bg-purple-500' : 'bg-brand-500') }}" style="width: {{ ($score/9)*100 }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Examiner comment --}}
                            <div class="bg-brand-500/8 border border-brand-500/20 rounded-xl p-3 text-[11px] text-surface-400 leading-relaxed">
                                <span class="text-brand-300 font-semibold">AI Examiner:</span> Your argument structure is clear. Work on subject-verb agreement and spelling to reach Band 7.5.
                            </div>
                        </div>
                    </div>

                    {{-- Floating speaking chip --}}
                    <div class="float-card-delayed absolute -bottom-4 -right-4 z-20 bg-surface-800 border border-surface-600 rounded-2xl px-4 py-3 shadow-card-hover max-w-[180px]">
                        <p class="text-[9px] text-surface-500 uppercase tracking-widest mb-1">Speaking Part 2</p>
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="w-6 h-6 rounded-full bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></div>
                            </div>
                            <div class="flex items-end gap-0.5 h-4">
                                @foreach([3,6,5,8,4,7,3] as $h)
                                <div class="w-1 bg-brand-400/70 rounded-full" style="height: {{ $h * 2 }}px"></div>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-[10px] text-surface-400">Fluency <span class="text-brand-400 font-bold">7.5</span> · Vocab <span class="text-brand-400 font-bold">7.0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 2 — SOCIAL PROOF (Stats + Testimonials)
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section class="py-14 px-5 sm:px-8 bg-surface-900/30">
    <div class="max-w-7xl mx-auto">

        {{-- Stats row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-12">
            @foreach([
                ['10,000+', 'Mock tests completed', 'brand'],
                ['90%+',    'Score prediction accuracy', 'emerald'],
                ['+1.0',    'Average band improvement', 'amber'],
                ['4 Skills','Reading, Listening, Writing, Speaking', 'purple'],
            ] as [$num,$label,$color])
            <div class="text-center">
                <p class="text-3xl sm:text-4xl font-black mb-1
                    {{ $color==='emerald'?'text-emerald-400':($color==='amber'?'text-amber-400':($color==='purple'?'text-purple-400':'text-brand-400')) }}">
                    {{ $num }}
                </p>
                <p class="text-xs text-surface-500 leading-snug">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Testimonials --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach([
                ['Rahul S.','Mumbai, India','🇮🇳','I went from Band 6.0 to 7.5 in 8 weeks using IELTS Band AI. The error highlighting showed me exactly what I was doing wrong in writing.',7.5,6.0,'brand'],
                ['Aisha K.','Dubai, UAE','🇦🇪','The speaking feedback is incredible. It told me I was speaking too fast and gave me tips. My speaking went from 6.5 to 7.0 in 3 weeks.',7.0,6.5,'emerald'],
                ['Priya M.','Ahmedabad, India','🇮🇳','I spent ₹15,000 on coaching and did not improve. One month of this AI tool and I finally got my target band for Canada immigration.',7.5,6.0,'purple'],
            ] as [$name,$loc,$flag,$quote,$after,$before,$color])
            <div class="card p-5">
                <div class="flex gap-0.5 mb-3">
                    @for($i=0;$i<5;$i++)<svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-surface-300 text-sm leading-relaxed mb-4">"{{ $quote }}"</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-surface-700 flex items-center justify-center text-sm">{{ $flag }}</div>
                        <div>
                            <p class="text-xs font-semibold text-surface-200">{{ $name }}</p>
                            <p class="text-[10px] text-surface-500">{{ $loc }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center gap-1.5">
                            <span class="text-surface-600 text-xs line-through">{{ $before }}</span>
                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            <span class="text-emerald-400 font-bold text-sm">{{ $after }}</span>
                        </div>
                        <p class="text-[9px] text-surface-600">Band score</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 3 — THE PROBLEM
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section class="py-20 px-5 sm:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <div class="tag-amber inline-flex mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Does this sound familiar?
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">IELTS preparation is broken.</h2>
            <p class="text-surface-400 text-lg max-w-2xl mx-auto">Most students spend months studying — without ever knowing their real score. Sound familiar?</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                [
                    'I don\'t know my real band score.',
                    'You practice every day — but you don\'t actually know if you\'re Band 6 or Band 7. You are guessing.',
                    'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'amber'
                ],
                [
                    'My writing has mistakes I can\'t find.',
                    'You submit your writing to a friend or teacher. They say "looks good." You still don\'t know what to fix.',
                    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'red'
                ],
                [
                    'Good coaching costs too much.',
                    'A private IELTS tutor costs ₹1,000–₹3,000 per class. Coaching centres charge ₹15,000 or more. It is too expensive.',
                    'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                    'red'
                ],
                [
                    'Old PDFs don\'t simulate the real exam.',
                    'You practice with downloaded PDFs. But the real IELTS exam is timed, digital, and stressful. PDFs do not prepare you.',
                    'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                    'amber'
                ],
            ] as [$title,$desc,$icon,$color])
            <div class="card p-6 flex gap-4">
                <div class="w-10 h-10 rounded-xl {{ $color==='red' ? 'bg-red-500/10 border border-red-500/20' : 'bg-amber-500/10 border border-amber-500/20' }} flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 {{ $color==='red' ? 'text-red-400' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-surface-100 mb-1 text-[15px]">{{ $title }}</h3>
                    <p class="text-surface-400 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 4 — THE SOLUTION
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section class="py-20 px-5 sm:px-8 bg-surface-900/30">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <div class="tag-cyan inline-flex mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Here's what changes
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Practice like the real exam. Get feedback like a real examiner.</h2>
            <p class="text-surface-400 text-lg max-w-2xl mx-auto">IELTS Band AI solves every problem — at a fraction of the cost of a tutor.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                ['Know your exact band score','After every test, you get an instant predicted band score for each skill. No more guessing. Know if you are ready.','bg-brand-500/10 border-brand-500/20 text-brand-400','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['AI finds every writing mistake','Every grammar error, spelling mistake, and weak word choice is underlined in your essay — with an explanation and the correct version.','bg-emerald-500/10 border-emerald-500/20 text-emerald-400','M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                ['Speaking feedback without a tutor','Record your answer. AI transcribes it, scores your fluency, vocabulary, pronunciation, and grammar. Just like Part 1, 2, and 3 of the real test.','bg-purple-500/10 border-purple-500/20 text-purple-400','M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                ['Real exam pressure. Zero cost.','Full timed mock tests online. No PDF. No printing. Exam conditions with a live timer and immediate results. Free to start.','bg-amber-500/10 border-amber-500/20 text-amber-400','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as [$title,$desc,$style,$icon])
            <div class="card p-6 flex gap-4 group card-hover">
                <div class="w-11 h-11 rounded-xl {{ $style }} border flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-surface-100 mb-1.5 text-[15px]">{{ $title }}</h3>
                    <p class="text-surface-400 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 5 — FEATURES (Interactive tabs)
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section id="features" class="py-20 px-5 sm:px-8" x-data="{ tab: 'writing' }">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-12">
            <div class="tag-purple inline-flex mb-4">All four IELTS skills</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Every skill. One platform.</h2>
            <p class="text-surface-400 text-lg">Select a skill to see how it works.</p>
        </div>

        {{-- Tab pills --}}
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            @foreach([
                ['writing','Writing','M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                ['speaking','Speaking','M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                ['reading','Reading','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['listening','Listening','M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                ['progress','Progress','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ] as [$key,$label,$icon])
            <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-brand-500/12 text-brand-300 border-brand-500/35' : 'bg-surface-800/60 text-surface-400 border-surface-600/60 hover:border-surface-500'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/></svg>
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Tab panels --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

            {{-- Writing --}}
            <div x-show="tab==='writing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="mb-2">
                    <span class="tag-purple mb-3 inline-flex">Writing Evaluation</span>
                    <h3 class="text-2xl font-bold text-surface-50 mb-3">Every mistake, explained.</h3>
                    <p class="text-surface-400 leading-relaxed mb-5">Write your Task 1 or Task 2 essay. The AI reads it like an examiner — and underlines every error with the correct fix.</p>
                    <ul class="space-y-2">
                        @foreach(['Highlights grammar, spelling and word-choice errors','Shows exact corrections inline','Gives criterion-level band scores (Task, Coherence, Lexical, Grammar)','Provides a Band 9 model essay on demand'] as $f)
                        <li class="flex items-center gap-2 text-sm text-surface-300">
                            <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('writing.index') }}" class="btn-primary mt-6 inline-flex">Try Writing Test →</a>
                </div>
            </div>
            <div x-show="tab==='writing'" x-transition:enter="transition ease-out duration-200">
                <div class="card p-5 space-y-3">
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest">Writing Task 2 — Error View</p>
                    <div class="bg-surface-900/60 rounded-xl p-4 text-[12px] leading-7 text-surface-300 font-mono">
                        The use of social media <span class="mock-error px-0.5 rounded">have</span> <span class="mock-correction px-0.5 rounded">has</span> increased dramatically. People spend <span class="mock-error px-0.5 rounded">alot</span> <span class="mock-correction px-0.5 rounded">a lot</span> of time on <span class="mock-error px-0.5 rounded">there</span> <span class="mock-correction px-0.5 rounded">their</span> phones every day.
                    </div>
                    <div class="flex gap-2 flex-wrap text-[10px]">
                        <span class="bg-red-500/10 border border-red-500/20 text-red-300 px-2 py-0.5 rounded-full">have → has (SVA)</span>
                        <span class="bg-red-500/10 border border-red-500/20 text-red-300 px-2 py-0.5 rounded-full">alot → a lot</span>
                        <span class="bg-red-500/10 border border-red-500/20 text-red-300 px-2 py-0.5 rounded-full">there → their</span>
                    </div>
                </div>
            </div>

            {{-- Speaking --}}
            <div x-show="tab==='speaking'" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <span class="tag-cyan mb-3 inline-flex">Speaking Feedback</span>
                <h3 class="text-2xl font-bold text-surface-50 mb-3">Record. Transcribe. Score.</h3>
                <p class="text-surface-400 leading-relaxed mb-5">Answer Part 1, 2, and 3 questions just like in the real exam. AI transcribes your speech and scores your fluency, vocabulary, grammar, and pronunciation.</p>
                <ul class="space-y-2">
                    @foreach(['Part 1, 2, and 3 questions','Live countdown timer','AI transcription of your speech','Fluency, vocabulary, grammar and pronunciation scores'] as $f)
                    <li class="flex items-center gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('speaking.test') }}" class="btn-primary mt-6 inline-flex">Try Speaking Test →</a>
            </div>
            <div x-show="tab==='speaking'" x-transition:enter="transition ease-out duration-200">
                <div class="card p-5 space-y-4">
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest">Speaking Part 2 — Live</p>
                    <div class="bg-surface-900/60 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-red-500/20 border border-red-500/30 rounded-full flex items-center justify-center">
                                <div class="w-2.5 h-2.5 bg-red-400 rounded-full animate-pulse"></div>
                            </div>
                            <span class="text-[11px] text-surface-400">Recording — <span class="font-mono text-brand-400">01:45</span> remaining</span>
                        </div>
                        <p class="text-[11px] text-surface-500 italic">Transcript: "I would like to talk about a memorable journey I had... the experience was very exciting and I learned many things from it..."</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-center text-[11px]">
                        @foreach([['Fluency','7.5','brand'],['Vocab','7.0','purple'],['Grammar','7.0','emerald'],['Pronunc.','6.5','amber']] as [$c,$s,$col])
                        <div class="bg-surface-900/50 rounded-lg p-2">
                            <div class="font-bold text-xl {{ $col==='brand'?'text-brand-400':($col==='purple'?'text-purple-400':($col==='emerald'?'text-emerald-400':'text-amber-400')) }}">{{ $s }}</div>
                            <div class="text-surface-500 text-[9px]">{{ $c }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Reading --}}
            <div x-show="tab==='reading'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <span class="tag-amber mb-3 inline-flex">Reading Test</span>
                <h3 class="text-2xl font-bold text-surface-50 mb-3">Real passages. Real questions.</h3>
                <p class="text-surface-400 leading-relaxed mb-5">Academic and General Training reading passages with MCQ, True/False/Not Given, and matching question types — auto-graded instantly.</p>
                <ul class="space-y-2">
                    @foreach(['Academic and General Training passages','All official question formats','Auto-graded with explanations','Timed to match real exam conditions'] as $f)
                    <li class="flex items-center gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('reading.index') }}" class="btn-primary mt-6 inline-flex">Try Reading Test →</a>
            </div>
            <div x-show="tab==='reading'" x-transition:enter="transition ease-out duration-200">
                <div class="card p-5 space-y-3">
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest">Reading — Question Types</p>
                    <div class="space-y-2">
                        @foreach([
                            ['True / False / Not Given','bg-brand-500/10 text-brand-300','Correct','bg-emerald-500/10 border-emerald-500/20 text-emerald-300'],
                            ['Multiple Choice (A–D)','bg-purple-500/10 text-purple-300','Incorrect','bg-red-500/10 border-red-500/20 text-red-300'],
                            ['Sentence Completion','bg-amber-500/10 text-amber-300','Correct','bg-emerald-500/10 border-emerald-500/20 text-emerald-300'],
                        ] as [$type,$tbg,$result,$rbg])
                        <div class="bg-surface-900/50 rounded-xl p-3 flex items-center justify-between">
                            <span class="text-[11px] font-medium {{ $tbg }} px-2 py-0.5 rounded-lg">{{ $type }}</span>
                            <span class="text-[10px] border {{ $rbg }} px-2 py-0.5 rounded-full">{{ $result }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="bg-surface-900/50 rounded-xl p-3 text-center">
                        <p class="text-2xl font-black text-brand-400">32 / 40</p>
                        <p class="text-[10px] text-surface-500">Band 7.0 equivalent</p>
                    </div>
                </div>
            </div>

            {{-- Listening --}}
            <div x-show="tab==='listening'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <span class="tag-green mb-3 inline-flex">Listening Test</span>
                <h3 class="text-2xl font-bold text-surface-50 mb-3">Listen. Answer. Learn.</h3>
                <p class="text-surface-400 leading-relaxed mb-5">Play audio recordings from real-exam-style passages and answer MCQ, fill-in-the-blank and matching questions. Auto-graded with explanations.</p>
                <ul class="space-y-2">
                    @foreach(['Real-style audio recordings','Fill-in-the-blank, MCQ and matching formats','Instant correct/incorrect marking','Detailed explanations for wrong answers'] as $f)
                    <li class="flex items-center gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('listening.index') }}" class="btn-primary mt-6 inline-flex">Try Listening Test →</a>
            </div>
            <div x-show="tab==='listening'" x-transition:enter="transition ease-out duration-200">
                <div class="card p-5 space-y-4">
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest">Listening — Audio Player</p>
                    <div class="bg-surface-900/60 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <button class="w-9 h-9 bg-brand-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                            <div class="flex-1">
                                <div class="h-1.5 bg-surface-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-500 rounded-full w-2/5"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-surface-600 mt-1">
                                    <span>01:23</span><span>03:45</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-surface-500">Section 2 — A guide to the local library...</p>
                    </div>
                    <div class="space-y-2 text-[12px]">
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-[9px] font-bold">✓</span><span class="text-surface-400">The library opens at <span class="text-emerald-400 font-mono font-bold">9am</span> on weekdays.</span></div>
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500/20 border border-red-500/30 text-red-400 flex items-center justify-center text-[9px] font-bold">✗</span><span class="text-surface-400">Membership costs <span class="text-red-400 font-mono line-through">£10</span> <span class="text-emerald-400 font-mono">£12</span> per year.</span></div>
                    </div>
                </div>
            </div>

            {{-- Progress --}}
            <div x-show="tab==='progress'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <span class="tag-cyan mb-3 inline-flex">Progress Tracking</span>
                <h3 class="text-2xl font-bold text-surface-50 mb-3">See your improvement over time.</h3>
                <p class="text-surface-400 leading-relaxed mb-5">Every test is saved. Your dashboard shows your band score history, skill-by-skill breakdown, and where to focus next.</p>
                <ul class="space-y-2">
                    @foreach(['Band score history for each skill','Identify your weakest areas','Compare your progress week-by-week','AI suggestions on what to improve'] as $f)
                    <li class="flex items-center gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('dashboard') }}" class="btn-primary mt-6 inline-flex">View Dashboard →</a>
            </div>
            <div x-show="tab==='progress'" x-transition:enter="transition ease-out duration-200">
                <div class="card p-5 space-y-4">
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest">Your Progress — Last 6 Weeks</p>
                    <div class="space-y-3">
                        @foreach([['Writing','w-4/5',7.0,'brand'],['Speaking','w-3/4',6.5,'purple'],['Reading','w-10/12',7.5,'emerald'],['Listening','w-2/3',6.5,'amber']] as [$skill,$w,$score,$c])
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-surface-400">{{ $skill }}</span>
                                <span class="{{ $c==='brand'?'text-brand-400':($c==='purple'?'text-purple-400':($c==='emerald'?'text-emerald-400':'text-amber-400')) }} font-bold">{{ $score }}</span>
                            </div>
                            <div class="h-2 bg-surface-700 rounded-full overflow-hidden">
                                <div class="{{ $w }} h-full rounded-full {{ $c==='brand'?'bg-brand-500':($c==='purple'?'bg-purple-500':($c==='emerald'?'bg-emerald-500':'bg-amber-500')) }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between bg-brand-500/8 border border-brand-500/20 rounded-xl p-3">
                        <span class="text-[11px] text-surface-400">Overall improvement</span>
                        <span class="text-emerald-400 font-bold text-sm">+1.0 band in 6 weeks</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 5B — EXAM SIMULATION SHOWCASE
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section id="exam-simulation" class="py-20 px-5 sm:px-8 overflow-hidden">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                New — Exam Simulation Mode
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-4">
                Feels Exactly Like<br>
                <span class="text-gradient">The Real IELTS Exam.</span>
            </h2>
            <p class="text-surface-400 text-base max-w-2xl mx-auto">
                Not just a practice tool — a full exam replica. Same white interface, same strict timer, same pressure. So when test day comes, you've been there before.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

            {{-- Left: Feature list --}}
            <div class="space-y-5">

                @foreach([
                    ['🎓', 'Official IELTS-Style Interface', 'White, clean, distraction-free UI that mirrors the real computer-based IELTS test — not our dark practice mode.', 'amber'],
                    ['⏱', 'Strict Countdown Timer', 'Full-screen timer with automatic submission when time runs out. Warnings at 10 minutes and 5 minutes remaining.', 'brand'],
                    ['🖊', 'Text Highlighting Tool', 'Highlight passages in yellow, green, or pink — exactly like the real exam. Click to remove. Saved automatically.', 'emerald'],
                    ['⚑', 'Flag Questions for Review', 'Mark any question to revisit before submitting. See your flagged vs answered status at a glance.', 'purple'],
                    ['📝', 'Scratchpad Notes', 'Built-in notepad for rough work and planning. Notes are never submitted — only your answers are.', 'rose'],
                    ['🔒', 'Full-Screen Exam Mode', 'Locks the browser into fullscreen. Detects tab switching. Disables copy-paste. Real exam conditions.', 'amber'],
                ] as [$icon, $title, $desc, $color])
                @php
                    $colors = [
                        'amber'   => ['bg-amber-500/10 border-amber-500/20',   'text-amber-400'],
                        'brand'   => ['bg-brand-500/10 border-brand-500/20',   'text-brand-400'],
                        'emerald' => ['bg-emerald-500/10 border-emerald-500/20','text-emerald-400'],
                        'purple'  => ['bg-purple-500/10 border-purple-500/20', 'text-purple-400'],
                        'rose'    => ['bg-rose-500/10 border-rose-500/20',     'text-rose-400'],
                    ];
                    [$bg, $text] = $colors[$color];
                @endphp
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl {{ $bg }} border flex items-center justify-center text-lg shrink-0">
                        {{ $icon }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-surface-100 mb-0.5">{{ $title }}</p>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach

                <div class="pt-2">
                    <a href="{{ route('register') }}" class="btn-primary px-6 py-3 font-bold shadow-glow">
                        Try Exam Simulation Free
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Right: UI mockup --}}
            <div class="relative" style="max-width:100%;overflow:hidden;">
                {{-- Glow --}}
                <div class="absolute inset-0 bg-brand-500/5 rounded-2xl blur-3xl pointer-events-none"></div>

                {{-- Exam UI preview --}}
                <div class="relative rounded-xl overflow-hidden border border-surface-600 shadow-card-hover bg-white">

                    {{-- Fake IELTS header --}}
                    <div style="background:#fff;border-bottom:2px solid #003087;height:44px;display:flex;align-items:center;padding:0 16px;font-family:Arial,sans-serif;">
                        <span style="font-size:10px;font-weight:bold;color:#003087;letter-spacing:.06em;text-transform:uppercase;min-width:120px;">IELTS Band AI</span>
                        <span style="flex:1;text-align:center;font-size:13px;font-weight:bold;color:#1a1a1a;">Academic Reading Test</span>
                        <div style="display:flex;align-items:center;gap:6px;min-width:120px;justify-content:flex-end;">
                            <span style="font-size:11px;color:#555;">Time</span>
                            <span style="font-family:'Courier New',monospace;font-size:14px;font-weight:bold;color:#003087;">45:23</span>
                        </div>
                    </div>

                    {{-- Fake toolbar --}}
                    <div style="background:#F0F2F7;border-bottom:1px solid #D0D3DC;height:32px;display:flex;align-items:center;padding:0 12px;gap:6px;font-family:Arial,sans-serif;">
                        <span style="padding:2px 8px;font-size:11px;border:1px solid #B0B3BC;background:#FFEF9F;border-radius:2px;cursor:pointer;">Highlight</span>
                        <span style="padding:2px 8px;font-size:11px;border:1px solid #B0B3BC;background:#BDFFC7;border-radius:2px;cursor:pointer;"></span>
                        <span style="padding:2px 8px;font-size:11px;border:1px solid #B0B3BC;background:#FFCECE;border-radius:2px;cursor:pointer;"></span>
                        <span style="width:1px;height:16px;background:#ccc;margin:0 4px;"></span>
                        <span style="padding:2px 8px;font-size:11px;border:1px solid #B0B3BC;background:#fff;border-radius:2px;">📝 Notes</span>
                        <span style="margin-left:auto;font-size:11px;color:#555;"><strong>12</strong> / 14 answered</span>
                    </div>

                    {{-- Fake split panel --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;height:180px;font-family:Arial,sans-serif;">
                        {{-- Passage --}}
                        <div style="padding:14px;border-right:1px solid #D0D3DC;overflow:hidden;">
                            <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Reading Passage</div>
                            <div style="font-size:11px;color:#1a1a1a;line-height:1.7;">
                                The development of <mark style="background:#FFEF9F;">artificial intelligence</mark> in the 21st century has transformed industries at an unprecedented rate. Researchers suggest that automation will <mark style="background:#BDFFC7;">reshape the labour market</mark> significantly over the coming decade…
                            </div>
                        </div>
                        {{-- Questions --}}
                        <div style="padding:14px;background:#FAFBFE;overflow:hidden;">
                            <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Questions</div>
                            <div style="margin-bottom:10px;">
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                    <span style="width:18px;height:18px;background:#003087;color:#fff;font-size:9px;font-weight:bold;display:flex;align-items:center;justify-content:center;border-radius:2px;flex-shrink:0;">1</span>
                                    <span style="font-size:11px;color:#1a1a1a;">The passage states that AI has changed industries</span>
                                </div>
                                <div style="display:flex;gap:4px;margin-left:24px;">
                                    @foreach(['True','False','Not Given'] as $opt)
                                    <span style="padding:3px 8px;border:1px solid {{ $opt === 'True' ? '#003087' : '#B0B3BC' }};background:{{ $opt === 'True' ? '#003087' : '#fff' }};color:{{ $opt === 'True' ? '#fff' : '#555' }};font-size:10px;border-radius:2px;cursor:pointer;">{{ $opt }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div style="margin-bottom:10px;">
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                    <span style="width:18px;height:18px;background:#E07B00;color:#fff;font-size:9px;font-weight:bold;display:flex;align-items:center;justify-content:center;border-radius:2px;flex-shrink:0;">2</span>
                                    <span style="font-size:11px;color:#1a1a1a;">What will automation reshape?</span>
                                    <span style="margin-left:auto;font-size:10px;color:#E07B00;">⚑ flagged</span>
                                </div>
                                <input style="border:1px solid #B0B3BC;padding:3px 7px;font-size:11px;border-radius:2px;width:80%;background:#fff;" placeholder="Write answer..." value="" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Fake nav panel --}}
                    <div style="background:#F0F2F7;border-top:2px solid #003087;padding:6px 12px;display:flex;align-items:center;gap:8px;font-family:Arial,sans-serif;">
                        <div style="display:flex;gap:3px;flex:1;flex-wrap:wrap;">
                            @for($i=1;$i<=14;$i++)
                            <span style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;border:1px solid {{ $i <= 12 ? '#003087' : '#B0B3BC' }};background:{{ $i === 2 ? '#E07B00' : ($i <= 12 ? '#003087' : '#fff') }};color:{{ $i <= 12 ? '#fff' : '#555' }};border-radius:2px;">{{ $i }}</span>
                            @endfor
                        </div>
                        <span style="padding:5px 14px;background:#003087;color:#fff;font-size:11px;font-weight:bold;border-radius:2px;white-space:nowrap;">Submit →</span>
                    </div>
                </div>

                {{-- Badge overlay --}}
                <div class="absolute -top-3 -right-3 bg-surface-800 border border-amber-500/40 text-amber-400 text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                    🔒 Fullscreen + Anti-cheat
                </div>
            </div>
        </div>

        {{-- Comparison strip --}}
        <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl mx-auto">
            <div class="card p-5">
                <p class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-3">📝 Practice Mode</p>
                <ul class="space-y-2 text-sm text-surface-400">
                    <li class="flex items-center gap-2"><span class="text-brand-400">✓</span> Dark UI with live analysis</li>
                    <li class="flex items-center gap-2"><span class="text-brand-400">✓</span> AI hints available</li>
                    <li class="flex items-center gap-2"><span class="text-brand-400">✓</span> Flexible timer</li>
                    <li class="flex items-center gap-2"><span class="text-brand-400">✓</span> Instant word count feedback</li>
                </ul>
            </div>
            <div class="card p-5 border-amber-500/30 bg-amber-500/5">
                <p class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-3">🎓 Exam Simulation Mode</p>
                <ul class="space-y-2 text-sm text-surface-300">
                    <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Real IELTS white interface</li>
                    <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Fullscreen + tab detection</li>
                    <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Strict auto-submit timer</li>
                    <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Highlight, flag & notes tools</li>
                </ul>
            </div>
        </div>

    </div>
</section>

{{-- ════════════════════════════════════════
     SECTION 6 — SAMPLE RESULT (Report mockup)
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section id="sample-result" class="py-20 px-5 sm:px-8 bg-surface-900/30">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <div class="tag-cyan inline-flex mb-4">Real feedback. Real report.</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">This is what your feedback looks like.</h2>
            <p class="text-surface-400 text-lg">A real sample output from a Writing Task 2 submission.</p>
        </div>

        {{-- Full result report mockup --}}
        <div class="card overflow-hidden max-w-3xl mx-auto">

            {{-- Report header --}}
            <div class="bg-gradient-to-r from-surface-800 to-surface-900 border-b border-surface-700 px-6 py-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                            </div>
                            <span class="text-xs font-bold text-surface-300">IELTS Band AI — Evaluation Report</span>
                        </div>
                        <p class="text-[11px] text-surface-500">Writing Task 2 · Academic · April 2025</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <p class="text-4xl font-black text-surface-50 leading-none">7.0</p>
                            <p class="text-[10px] text-surface-500 mt-1">Overall Band</p>
                        </div>
                        <div>
                            {{-- SVG ring --}}
                            <svg width="60" height="60" viewBox="0 0 60 60" style="overflow:visible">
                                <circle cx="30" cy="30" r="24" fill="none" stroke="#1e293b" stroke-width="6"/>
                                <circle cx="30" cy="30" r="24" fill="none" stroke="#06b6d4" stroke-width="6"
                                    stroke-dasharray="150.8" stroke-dashoffset="43"
                                    stroke-linecap="round" class="progress-ring"
                                    style="filter:drop-shadow(0 0 6px rgba(6,182,212,0.5))"/>
                                <text x="30" y="35" text-anchor="middle" fill="#e2e8f0" font-size="11" font-weight="800" font-family="Figtree,sans-serif">78%</text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Criterion scores --}}
                <div>
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-3 font-semibold">Criterion Scores</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            ['Task Achievement','6.5','72'],
                            ['Coherence & Cohesion','7.0','78'],
                            ['Lexical Resource','7.0','78'],
                            ['Grammatical Range','7.5','83'],
                        ] as [$crit,$band,$pct])
                        <div class="bg-surface-900/60 rounded-xl p-3 text-center">
                            <div class="text-2xl font-black text-brand-400 mb-1">{{ $band }}</div>
                            <div class="h-1 bg-surface-700 rounded-full mb-2 overflow-hidden">
                                <div class="h-full bg-brand-500 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                            <p class="text-[9px] text-surface-500 leading-tight">{{ $crit }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Essay with highlights --}}
                <div>
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-3 font-semibold">Your Essay — Annotated</p>
                    <div class="bg-surface-900/60 rounded-xl p-4 text-[12px] leading-8 text-surface-300 font-mono border border-surface-700">
                        Technology <span class="mock-error px-1 rounded group relative" title="'effect' should be 'affects' (verb form)">effect</span> every part of modern life, and social media <span class="mock-error px-1 rounded" title="'is' should be 'are' (SVA)">is</span> one of the <span class="mock-correction px-1 rounded">most significant</span> changes. Many people <span class="mock-error px-1 rounded" title="Spelling: 'beleive' → 'believe'">beleive</span> it has made society less connected, while <span class="mock-correction px-1 rounded">others contend</span> the opposite is true.
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <div class="flex items-center gap-1.5 text-xs text-surface-500"><div class="w-3 h-0.5 bg-red-400 rounded"></div> Error found</div>
                        <div class="flex items-center gap-1.5 text-xs text-surface-500"><div class="w-3 h-0.5 bg-emerald-400 rounded"></div> Strong phrase</div>
                    </div>
                </div>

                {{-- Error breakdown --}}
                <div>
                    <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-3 font-semibold">Error Summary</p>
                    <div class="space-y-2">
                        @foreach([
                            ['Grammar — Subject-Verb Agreement','"technology effect" → "technology affects"','red'],
                            ['Spelling — Simple word','"beleive" → "believe"','red'],
                            ['Grammar — Article use','"in real world" → "in the real world"','amber'],
                        ] as [$type,$fix,$color])
                        <div class="flex items-start gap-3 bg-surface-900/50 rounded-xl p-3">
                            <div class="w-1.5 h-1.5 rounded-full {{ $color==='red' ? 'bg-red-400' : 'bg-amber-400' }} mt-1.5 shrink-0"></div>
                            <div>
                                <p class="text-[11px] font-semibold text-surface-300">{{ $type }}</p>
                                <p class="text-[10px] text-surface-500 font-mono">{{ $fix }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Examiner comment --}}
                <div class="bg-brand-500/8 border border-brand-500/20 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <span class="text-[11px] font-bold text-brand-300">AI Examiner Feedback</span>
                    </div>
                    <p class="text-[12px] text-surface-400 leading-relaxed">Your essay shows a clear position and logical paragraph structure — which is good. To reach Band 7.5, focus on reducing grammatical errors (especially subject-verb agreement) and expand your range of linking devices. Your vocabulary is generally appropriate but occasionally imprecise.</p>
                </div>

                {{-- Strength + Improvement --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-emerald-500/8 border border-emerald-500/20 rounded-xl p-4">
                        <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest mb-2">Strengths</p>
                        <ul class="space-y-1.5">
                            @foreach(['Clear central argument','Good paragraph structure','Appropriate formal register'] as $s)
                            <li class="flex items-center gap-2 text-[11px] text-surface-400">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ $s }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-amber-500/8 border border-amber-500/20 rounded-xl p-4">
                        <p class="text-[10px] text-amber-400 font-bold uppercase tracking-widest mb-2">To Improve</p>
                        <ul class="space-y-1.5">
                            @foreach(['Fix subject-verb agreement','Use more linking words','Vary sentence length more'] as $i)
                            <li class="flex items-center gap-2 text-[11px] text-surface-400">
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                {{ $i }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="text-center pt-2">
                    <p class="text-surface-500 text-sm mb-4">You get this level of detail for every test you take.</p>
                    <a href="{{ route('register') }}" class="btn-primary px-8 py-3.5 shadow-glow font-bold text-base">Get My Band Score Now</a>
                </div>

            </div>
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 7 — HOW IT WORKS
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section id="how-it-works" class="py-20 px-5 sm:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <div class="tag-cyan inline-flex mb-4">Simple process</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Three steps. That's it.</h2>
            <p class="text-surface-400 text-lg">From signup to band score in under 10 minutes.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
            {{-- Desktop connector --}}
            <div class="hidden md:block absolute top-10 left-[calc(33.3%+1rem)] right-[calc(33.3%+1rem)] h-0.5 bg-gradient-to-r from-brand-500/50 via-brand-400/70 to-brand-500/50 z-0"></div>

            @foreach([
                ['01','Choose a test','Pick any skill — Writing, Speaking, Reading, or Listening. Choose Academic or General Training.','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','brand'],
                ['02','Complete the test','Write your essay or record your speaking. Work under real exam conditions with a live timer.','M12 6v6m0 0v6m0-6h6m-6 0H6','emerald'],
                ['03','Get your result','Receive your band score in under 60 seconds. See every error, read the feedback, and know exactly what to fix.','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','amber'],
            ] as [$num,$title,$desc,$icon,$color])
            <div class="relative z-10 card p-7 text-center flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br
                    {{ $color==='emerald' ? 'from-emerald-500 to-emerald-700' : ($color==='amber' ? 'from-amber-500 to-amber-700' : 'from-brand-500 to-brand-700') }}
                    flex items-center justify-center mb-5 shadow-glow">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div class="text-[10px] font-black text-surface-600 tracking-widest mb-2">STEP {{ $num }}</div>
                <h3 class="text-lg font-bold text-surface-50 mb-2">{{ $title }}</h3>
                <p class="text-surface-400 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('register') }}" class="btn-primary text-base px-8 py-3.5 shadow-glow-lg font-bold">
                Start My Free Test
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 8 — PRICING PREVIEW
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section class="py-20 px-5 sm:px-8 bg-surface-900/30">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <div class="tag-amber inline-flex mb-4">Simple pricing</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Start free. Upgrade when you need more.</h2>
            <p class="text-surface-400 text-lg">No hidden fees. Cancel anytime.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl mx-auto">

            {{-- Free plan --}}
            <div class="card p-7">
                <p class="text-[10px] text-surface-500 uppercase tracking-widest mb-3 font-semibold">Free Forever</p>
                <div class="flex items-end gap-1 mb-5">
                    <span class="text-4xl font-black text-surface-50">₹0</span>
                    <span class="text-surface-500 text-sm mb-1">/month</span>
                </div>
                <ul class="space-y-2.5 mb-7">
                    @foreach([
                        [true, '3 test credits to start'],
                        [true, 'Writing & Speaking evaluation'],
                        [true, 'Band score prediction'],
                        [true, 'Basic error highlighting'],
                        [false,'Unlimited daily tests'],
                        [false,'PDF reports'],
                        [false,'Progress analytics'],
                    ] as [$incl, $feat])
                    <li class="flex items-center gap-2 text-sm {{ $incl ? 'text-surface-300' : 'text-surface-600' }}">
                        <svg class="w-4 h-4 shrink-0 {{ $incl ? 'text-emerald-400' : 'text-surface-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $incl ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                        </svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="btn-secondary w-full justify-center">Get started free</a>
            </div>

            {{-- Pro plan --}}
            <div class="relative card p-7 border-brand-500/40" style="background: linear-gradient(135deg, rgba(6,182,212,0.06) 0%, rgba(30,41,59,1) 60%);">
                {{-- Popular badge --}}
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-500 text-white text-[10px] font-bold px-3 py-1 rounded-full tracking-wide shadow-glow">
                    MOST POPULAR
                </div>
                <p class="text-[10px] text-brand-400 uppercase tracking-widest mb-3 font-semibold">Pro</p>
                <div class="flex items-end gap-1 mb-5">
                    <span class="text-4xl font-black text-surface-50">₹99</span>
                    <span class="text-surface-400 text-sm mb-1">/month</span>
                </div>
                <ul class="space-y-2.5 mb-7">
                    @foreach([
                        'Unlimited tests every day',
                        'Full writing & speaking evaluation',
                        'Complete error analysis',
                        'Band 9 model essay on demand',
                        'PDF result reports',
                        'Progress analytics',
                        'Priority support',
                    ] as $feat)
                    <li class="flex items-center gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('pricing') }}" class="btn-primary w-full justify-center shadow-glow">Upgrade to Pro</a>
                <p class="text-[10px] text-surface-500 text-center mt-3">7-day refund · Cancel anytime</p>
            </div>

        </div>

        <p class="text-center text-sm text-surface-500 mt-8">
            Coaching centres cost ₹15,000+. Private tutors cost ₹1,000 per session.<br>
            <span class="text-surface-300 font-medium">IELTS Band AI Pro is ₹99/month — unlimited practice.</span>
        </p>

    </div>
</section>


{{-- ════════════════════════════════════════
     SECTION 9 — FINAL CTA
════════════════════════════════════════ --}}
<div class="section-divider"></div>

<section class="py-24 px-5 sm:px-8 relative overflow-hidden">

    {{-- Background glow --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-brand-500/8 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-3xl mx-auto text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center mx-auto mb-6 shadow-glow-lg">
            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
            </svg>
        </div>

        <h2 class="text-4xl sm:text-5xl font-black text-surface-50 leading-tight mb-5">
            Find out your real IELTS<br>band score today.
        </h2>

        <p class="text-surface-400 text-lg mb-10 max-w-xl mx-auto leading-relaxed">
            Take your first mock test right now — for free.<br>
            No coaching. No expensive books. Just honest AI feedback.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
            <a href="{{ route('register') }}" class="btn-primary text-base px-9 py-4 shadow-glow-lg font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Start Free Mock Test
            </a>
            <a href="{{ route('login') }}" class="btn-secondary text-base px-9 py-4 font-semibold">
                Already have an account? Sign in
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-x-7 gap-y-2 text-xs text-surface-500">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                No credit card needed
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Result in 60 seconds
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                All four IELTS skills
            </span>
        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     FOOTER
════════════════════════════════════════ --}}
<footer class="border-t border-surface-700/50 bg-surface-950 py-14 px-5 sm:px-8">
    <div class="max-w-7xl mx-auto">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-12">

            <div class="col-span-2 sm:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
                </a>
                <p class="text-surface-500 text-xs leading-relaxed max-w-[180px]">AI-powered IELTS mock tests for students targeting Band 7+.</p>

                <div class="flex gap-3 mt-5">
                    <a href="#" class="w-8 h-8 bg-surface-800 border border-surface-700 rounded-lg flex items-center justify-center text-surface-500 hover:text-brand-400 hover:border-brand-500/40 transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-surface-800 border border-surface-700 rounded-lg flex items-center justify-center text-surface-500 hover:text-brand-400 hover:border-brand-500/40 transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-semibold text-surface-300 text-xs uppercase tracking-wider mb-4">Practice</h4>
                <ul class="space-y-2.5">
                    @foreach([['Speaking Test', route('speaking.test')],['Writing Test', route('writing.index')],['Reading Test', route('reading.index')],['Listening Test', route('listening.index')]] as [$l,$u])
                    <li><a href="{{ $u }}" class="text-surface-500 hover:text-surface-200 text-sm transition-colors">{{ $l }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-surface-300 text-xs uppercase tracking-wider mb-4">Company</h4>
                <ul class="space-y-2.5">
                    @foreach([['About', route('about')],['Pricing', route('pricing')],['Contact', route('contact')],['FAQ', route('faq')]] as [$l,$u])
                    <li><a href="{{ $u }}" class="text-surface-500 hover:text-surface-200 text-sm transition-colors">{{ $l }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-surface-300 text-xs uppercase tracking-wider mb-4">Legal</h4>
                <ul class="space-y-2.5">
                    @foreach([['Privacy Policy', route('privacy')],['Terms of Use','#'],['Refund Policy','#']] as [$l,$u])
                    <li><a href="{{ $u }}" class="text-surface-500 hover:text-surface-200 text-sm transition-colors">{{ $l }}</a></li>
                    @endforeach
                </ul>
            </div>

        </div>

        <div class="border-t border-surface-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-surface-600 text-xs">&copy; {{ date('Y') }} IELTS Band AI. All rights reserved. Not affiliated with British Council, IDP or Cambridge Assessment.</p>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                <span class="text-xs text-surface-600">All systems operational</span>
            </div>
        </div>

    </div>
</footer>

</body>
</html>
