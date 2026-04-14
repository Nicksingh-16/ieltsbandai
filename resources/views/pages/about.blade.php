<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — IELTS Band AI</title>
    <meta name="description" content="We built IELTS Band AI to help students from India, Asia and the Middle East get honest AI feedback on their IELTS preparation — without expensive coaching.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .section-divider { height:1px; background:linear-gradient(90deg,transparent,rgba(51,65,85,0.8),transparent); }
        .timeline-dot { width:12px; height:12px; border-radius:50%; background:#06b6d4; flex-shrink:0; margin-top:4px; box-shadow:0 0 10px rgba(6,182,212,0.5); }
        .timeline-line { width:1px; background:linear-gradient(to bottom,#06b6d4,transparent); margin:0 auto; }
    </style>
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

{{-- ── Nav ── --}}
<header class="sticky top-0 z-50 bg-surface-950/90 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-6xl mx-auto px-5 sm:px-8 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <nav class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="btn-ghost text-sm">Home</a>
            <a href="{{ route('contact') }}" class="btn-ghost text-sm">Contact</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Start Free</a>
        </nav>
    </div>
</header>


{{-- ── Hero ── --}}
<section class="relative py-20 sm:py-28 px-5 sm:px-8 overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-brand-500/8 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-3xl mx-auto text-center">
        <div class="tag-cyan inline-flex mb-6">Our story</div>
        <h1 class="text-4xl sm:text-5xl font-black text-surface-50 leading-tight mb-6">
            We built the tool we<br>wished we had.
        </h1>
        <p class="text-surface-400 text-lg leading-relaxed max-w-2xl mx-auto">
            IELTS Band AI started with a simple frustration: why do students spend months preparing for IELTS without ever knowing their real score until exam day?
        </p>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Origin Story ── --}}
<section class="py-20 px-5 sm:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">

            <div class="space-y-5 text-surface-400 leading-relaxed">
                <p class="text-surface-200 text-lg font-medium">
                    Every year, millions of students take the IELTS exam to study abroad, migrate, or get a work visa.
                </p>
                <p>
                    Most of them prepare alone — with downloaded PDFs, outdated books, and YouTube videos. A few pay ₹15,000 or more for a coaching centre. But almost none of them get honest, specific feedback on their actual writing and speaking performance.
                </p>
                <p>
                    They walk into the exam not knowing if they're a Band 5.5 or a Band 7. They are guessing.
                </p>
                <p>
                    We thought: <span class="text-surface-100 font-semibold">AI can change this.</span> A language model that has read thousands of IELTS essays can score your essay with examiner-level accuracy — instantly. No tutor. No waiting. No ₹1,000 per session.
                </p>
                <p>
                    So we built it.
                </p>
            </div>

            {{-- Stats card cluster --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['10,000+', 'Mock tests completed', 'brand'],
                    ['90%+',    'Band score accuracy vs real examiners', 'emerald'],
                    ['+1.0',    'Average band improvement after 4 weeks', 'amber'],
                    ['₹99',     'Per month — less than one coaching class', 'purple'],
                ] as [$num, $label, $color])
                <div class="card p-5 text-center">
                    <p class="text-3xl font-black mb-1
                        {{ $color==='emerald'?'text-emerald-400':($color==='amber'?'text-amber-400':($color==='purple'?'text-purple-400':'text-brand-400')) }}">
                        {{ $num }}
                    </p>
                    <p class="text-surface-500 text-xs leading-snug">{{ $label }}</p>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Mission ── --}}
<section class="py-20 px-5 sm:px-8 bg-surface-900/40">
    <div class="max-w-4xl mx-auto text-center">
        <div class="tag-purple inline-flex mb-6">Our mission</div>
        <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-8 leading-tight">
            Every student deserves honest feedback —<br class="hidden sm:block"> not just the ones who can afford a tutor.
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 text-left mt-12">
            @foreach([
                [
                    'Honest scores',
                    'We predict band scores the same way an examiner would. We don\'t inflate scores to make you feel good. Honest feedback is the only feedback that actually helps.',
                    'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'brand',
                ],
                [
                    'Accessible to all',
                    'A student in a small town in Bihar and a student in Dubai should have the same quality of IELTS preparation. Geography and budget should not decide your band score.',
                    'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064',
                    'emerald',
                ],
                [
                    'Always improving',
                    'We update our AI models regularly based on real exam patterns. Our feedback gets better as more students use the platform. Every test you take improves the platform for everyone.',
                    'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                    'purple',
                ],
            ] as [$title, $desc, $icon, $color])
            <div class="card p-6">
                <div class="w-10 h-10 rounded-xl mb-4 flex items-center justify-center
                    {{ $color==='emerald'?'bg-emerald-500/12 border border-emerald-500/20':($color==='purple'?'bg-purple-500/12 border border-purple-500/20':'bg-brand-500/12 border border-brand-500/20') }}">
                    <svg class="w-5 h-5 {{ $color==='emerald'?'text-emerald-400':($color==='purple'?'text-purple-400':'text-brand-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                    </svg>
                </div>
                <h3 class="font-bold text-surface-100 mb-2">{{ $title }}</h3>
                <p class="text-surface-400 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── What we offer ── --}}
<section class="py-20 px-5 sm:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14">
            <div class="tag-amber inline-flex mb-4">The platform</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">What IELTS Band AI gives you</h2>
            <p class="text-surface-400 text-lg">Everything you need. Nothing you don't.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                ['Full mock tests for all 4 IELTS skills', 'Reading, Listening, Writing, and Speaking — Academic and General Training — in one place.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['Instant AI band score prediction', 'Get your score the moment you submit — broken down by Task Achievement, Coherence, Lexical Resource, and Grammatical Range.', 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['Writing error highlighting', 'Every grammar mistake, spelling error, and weak word is underlined with an explanation and the correct fix.', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                ['Speaking evaluation', 'Record your answers. AI transcribes and scores your fluency, vocabulary, grammar, and pronunciation for all three parts.', 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                ['Band 9 model answers', 'See a complete Band 9 essay on the same question you answered — so you understand exactly what top performance looks like.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['Progress tracking', 'Every test is saved. Your dashboard shows your score history and tells you which areas need the most work.', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ] as [$title, $desc, $icon])
            <div class="card p-5 flex gap-4 card-hover">
                <div class="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4.5 h-4.5 text-brand-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-surface-100 text-sm mb-1">{{ $title }}</p>
                    <p class="text-surface-500 text-xs leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Who we're for ── --}}
<section class="py-20 px-5 sm:px-8 bg-surface-900/40">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <div class="tag-cyan inline-flex mb-4">Who this is for</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Built for students like you</h2>
            <p class="text-surface-400 text-lg">If any of these describe you, IELTS Band AI was made for you.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                ['🎓', 'Targeting Band 7+', 'You need Band 7 or higher for a university application, skilled migration visa, or professional registration.'],
                ['🇮🇳', 'Preparing in India', 'You\'re in India and coaching centres are expensive, far away, or just not worth the cost.'],
                ['🌍', 'Studying from home', 'You don\'t have access to a native speaker or private IELTS tutor. You\'re doing this yourself.'],
                ['📝', 'Weak in Writing', 'You know your Writing score is dragging down your overall band. You need specific, actionable feedback.'],
                ['🎙️', 'Nervous about Speaking', 'The Speaking test makes you anxious. You want to practice with real questions and get scored.'],
                ['⏱️', 'Exam in 4–8 weeks', 'You don\'t have time to waste. You need to know exactly what to fix — now.'],
            ] as [$icon, $title, $desc])
            <div class="card p-5">
                <div class="text-2xl mb-3">{{ $icon }}</div>
                <h3 class="font-bold text-surface-100 text-sm mb-1.5">{{ $title }}</h3>
                <p class="text-surface-500 text-xs leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Team ── --}}
<section class="py-20 px-5 sm:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <div class="tag-purple inline-flex mb-4">The team</div>
            <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-3">Small team. Big mission.</h2>
            <p class="text-surface-400 text-lg">We are a small team of developers, educators, and language enthusiasts.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach([
                ['N', 'bg-brand-500', 'Nick Singh', 'Founder & Developer', 'Built the platform from scratch. Passionate about making quality IELTS prep accessible to every student regardless of budget.'],
                ['A', 'bg-purple-500', 'AI Team', 'Language & Evaluation', 'The AI models behind writing and speaking evaluation — trained on thousands of real IELTS responses to match examiner accuracy.'],
                ['S', 'bg-emerald-500', 'Student Advisors', 'IELTS Expertise', 'Former IELTS test takers and tutors who review our question bank, calibrate scoring, and test every new feature.'],
            ] as [$initial, $bg, $name, $role, $bio])
            <div class="card p-6 text-center">
                <div class="w-14 h-14 rounded-2xl {{ $bg }}/20 border border-{{ $bg }}/30 flex items-center justify-center mx-auto mb-4 text-xl font-black
                    {{ $bg==='bg-brand-500'?'text-brand-400':($bg==='bg-purple-500'?'text-purple-400':'text-emerald-400') }}">
                    {{ $initial }}
                </div>
                <p class="font-bold text-surface-100 mb-0.5">{{ $name }}</p>
                <p class="text-[11px] text-brand-400 font-semibold uppercase tracking-wider mb-3">{{ $role }}</p>
                <p class="text-surface-500 text-xs leading-relaxed">{{ $bio }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Final CTA ── --}}
<section class="py-20 px-5 sm:px-8 bg-surface-900/40">
    <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-black text-surface-50 mb-5">
            Ready to find out your real band score?
        </h2>
        <p class="text-surface-400 text-lg mb-8">
            Join thousands of students who practice with IELTS Band AI every day. Free to start. No credit card. Result in 60 seconds.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('register') }}" class="btn-primary text-base px-8 py-3.5 shadow-glow-lg font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Start Free Mock Test
            </a>
            <a href="{{ route('contact') }}" class="btn-secondary text-base px-8 py-3.5 font-semibold">
                Have a question? Contact us
            </a>
        </div>
    </div>
</section>


{{-- ── Footer ── --}}
<footer class="border-t border-surface-700/50 py-8 px-5 sm:px-8">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            </div>
            <span class="text-sm font-bold text-surface-50">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-5 text-xs text-surface-600">
            <a href="{{ route('about') }}"   class="hover:text-surface-300 transition-colors">About</a>
            <a href="{{ route('contact') }}" class="hover:text-surface-300 transition-colors">Contact</a>
            <a href="{{ route('faq') }}"     class="hover:text-surface-300 transition-colors">FAQ</a>
            <a href="{{ route('privacy') }}" class="hover:text-surface-300 transition-colors">Privacy</a>
        </div>
        <p class="text-xs text-surface-700">&copy; {{ date('Y') }} IELTS Band AI</p>
    </div>
</footer>

</body>
</html>
