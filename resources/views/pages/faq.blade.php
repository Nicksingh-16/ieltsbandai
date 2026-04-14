<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ — IELTS Band AI</title>
    <meta name="description" content="Frequently asked questions about IELTS Band AI — band scores, speaking tests, writing feedback, payments, and more.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .section-divider { height:1px; background:linear-gradient(90deg,transparent,rgba(51,65,85,0.8),transparent); }
    </style>
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

{{-- Nav --}}
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
            <a href="{{ route('home') }}"    class="btn-ghost text-sm">Home</a>
            <a href="{{ route('contact') }}" class="btn-ghost text-sm">Contact</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Start Free</a>
        </nav>
    </div>
</header>

{{-- Hero --}}
<section class="relative py-16 sm:py-24 px-5 sm:px-8 overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-[500px] h-[350px] bg-brand-500/7 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-2xl mx-auto text-center">
        <div class="tag-cyan inline-flex mb-6">Frequently asked questions</div>
        <h1 class="text-4xl sm:text-5xl font-black text-surface-50 leading-tight mb-5">Got a question?</h1>
        <p class="text-surface-400 text-lg leading-relaxed">
            Find quick answers below. If you can't find what you're looking for,
            <a href="{{ route('contact') }}" class="text-brand-400 hover:text-brand-300 transition-colors font-medium">contact us</a> and we'll reply within 24 hours.
        </p>
    </div>
</section>

<div class="section-divider"></div>

{{-- FAQ sections --}}
<section class="py-16 px-5 sm:px-8">
    <div class="max-w-3xl mx-auto space-y-14" x-data="{ open: null }">

        @php
        $sections = [
            [
                'icon'  => 'M13 10V3L4 14h7v7l9-11h-7z',
                'color' => 'brand',
                'title' => 'Getting started',
                'faqs'  => [
                    ['What is IELTS Band AI?', 'IELTS Band AI is an online platform where you can take full IELTS mock tests for all four skills — Reading, Listening, Writing, and Speaking — and receive instant AI-generated feedback with a predicted band score.'],
                    ['Is it really free?', 'Yes. You can sign up for free and start practising immediately. Free accounts receive 3 test credits on signup. You can upgrade to Pro for ₹99/month to get unlimited daily tests and the full feature set.'],
                    ['Do I need to install anything?', 'No. Everything runs in your browser. For the Speaking test, you will need to allow microphone access in Chrome or Firefox. No app download is required.'],
                    ['Which IELTS module does this cover — Academic or General Training?', 'Both. When you start a Writing or Reading test, you choose Academic or General Training. Speaking tests follow the same format for both modules.'],
                    ['How is this different from buying an IELTS practice book?', 'A book cannot score your writing or speaking. IELTS Band AI reads your actual essay, identifies specific errors, and tells you exactly what to fix — the same way a human examiner would. Books can only show you model answers.'],
                ],
            ],
            [
                'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'color' => 'emerald',
                'title' => 'Band scores & accuracy',
                'faqs'  => [
                    ['How accurate is the AI band score prediction?', 'Our AI is calibrated against thousands of real IELTS responses with known examiner scores. In testing, the predicted score matches the real examiner score within ±0.5 band in over 90% of cases. It is not perfect, but it is far more reliable than guessing or asking a non-examiner friend.'],
                    ['My score seems too low. Is the AI wrong?', 'It is possible, but unlikely. The AI scores the same criteria a real examiner uses: Task Achievement, Coherence and Cohesion, Lexical Resource, and Grammatical Range and Accuracy. If your score is lower than you expected, read the detailed error feedback carefully — it will tell you specifically what is dragging your score down. If you genuinely believe there is a system error, contact us with the test ID.'],
                    ['Can the AI score go above what I actually get in the real exam?', 'Yes — sometimes. The AI evaluates writing quality very precisely, but real examiners may also consider factors like handwriting legibility (for paper-based tests) or how you recover from errors in speaking. Use the AI score as a realistic target range, not a guarantee.'],
                    ['Does the band score get recalculated if I fix the errors?', 'Not automatically. You would need to submit a new attempt. Each submission is evaluated independently. This is intentional — it encourages you to actually practice rewriting, which is the best way to improve.'],
                ],
            ],
            [
                'icon'  => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                'color' => 'purple',
                'title' => 'Writing test',
                'faqs'  => [
                    ['What writing question types are available?', 'Task 1 Academic (graphs, charts, diagrams, processes), Task 1 General Training (formal and informal letters), and Task 2 essays (opinion, discussion, problem-solution, advantages-disadvantages) for both Academic and General Training.'],
                    ['What does the writing feedback include?', 'Inline error highlighting with corrections, band score for each of the four criteria, an examiner-style comment, a list of strengths, a list of improvements, topic vocabulary suggestions, and a Band 9 model essay on demand.'],
                    ['What counts as an error?', 'Grammar errors (wrong verb form, tense, subject-verb agreement), spelling mistakes, punctuation errors, wrong word choice, and cohesion issues (missing or incorrect linking words). The AI also flags sentences that are unclear or off-topic.'],
                    ['What is the Band 9 model essay?', 'After you receive your result, you can request a full Band 9 essay written by AI on the exact same question you answered. This shows you what a top-scoring response looks like for that specific topic and task type.'],
                    ['Is there a word count limit?', 'Task 1 requires a minimum of 150 words. Task 2 requires a minimum of 250 words. You can write more — there is no upper limit — but writing significantly more than needed can reduce your score if the extra content is unfocused.'],
                ],
            ],
            [
                'icon'  => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
                'color' => 'amber',
                'title' => 'Speaking test',
                'faqs'  => [
                    ['How does the speaking test work?', 'You go through three parts — just like the real exam. Part 1 is short personal questions (60 seconds each). Part 2 is a cue card topic where you speak for up to 2 minutes. Part 3 is a discussion with follow-up questions. You record each part using your microphone, and the AI evaluates your response.'],
                    ['My microphone is not working. What do I do?', 'First, check that your browser has microphone permission — click the lock icon in the address bar and make sure microphone is set to "Allow". The speaking test works best on Chrome or Firefox. If you are on Safari, switch to Chrome. If the problem persists, try a different device or contact us.'],
                    ['What does the speaking feedback include?', 'Your speech is transcribed. You receive separate scores for Fluency and Coherence, Lexical Resource, Grammatical Range and Accuracy, and Pronunciation — just like the real speaking marking criteria.'],
                    ['What audio format does the recorder use?', 'The browser records in WebM/Opus format, which is supported by all modern browsers. The audio is sent to our servers for transcription and evaluation. We do not store your recordings permanently.'],
                    ['Can I retake a part if I made a mistake?', 'No — just like the real exam, once you stop recording a part it is submitted. You can always start a new full test to try again.'],
                ],
            ],
            [
                'icon'  => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                'color' => 'brand',
                'title' => 'Payments & subscription',
                'faqs'  => [
                    ['What payment methods are accepted?', 'All major credit and debit cards, UPI (Google Pay, PhonePe, Paytm), net banking, and digital wallets — processed securely via Razorpay.'],
                    ['Is my payment information safe?', 'Yes. We do not store your card or payment details. All payments are processed by Razorpay, which is PCI-DSS compliant and regulated by the RBI.'],
                    ['I paid but my account still shows Free.', 'Payments usually activate within 1–2 minutes. If your account has not upgraded after 5 minutes, contact us with your Razorpay payment ID (found in your payment confirmation email or SMS) and we will upgrade your account manually.'],
                    ['Can I cancel my Pro subscription?', 'Yes, at any time. Go to your profile settings and cancel from there. Your Pro access continues until the end of the current billing period. No penalty, no questions asked.'],
                    ['Is there a refund policy?', 'Yes. We offer a 7-day money-back guarantee. If you are not happy with the Pro features within 7 days of subscribing, contact us with your payment ID and we will refund you in full.'],
                    ['Do you offer student discounts or group pricing?', 'Not currently, but we are working on it. Contact us if you are a teacher, coaching centre, or institution interested in bulk access.'],
                ],
            ],
            [
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'color' => 'emerald',
                'title' => 'Technical issues',
                'faqs'  => [
                    ['The page is loading slowly or not loading at all.', 'Try a hard refresh (Ctrl+Shift+R on Windows, Cmd+Shift+R on Mac). If the problem continues, clear your browser cache or try a different browser. If you are on a slow connection, the writing evaluation (which uses AI) may take up to 60 seconds — please do not close the tab while it is processing.'],
                    ['I got an error after submitting my writing test.', 'This usually means the AI evaluation timed out. Go to your Dashboard — your result may have been saved there. If not, try submitting again. If you keep seeing the error, contact us and include a screenshot of the error message.'],
                    ['Can I use IELTS Band AI on my phone?', 'Yes. The platform is fully mobile-responsive and works on Android and iOS. For the Speaking test, Chrome on Android works best. Safari on iPhone can have microphone recording limitations — Chrome is recommended.'],
                    ['Does IELTS Band AI work offline?', 'No. An internet connection is required for all tests and evaluations. The AI evaluation happens on our servers and cannot run offline.'],
                ],
            ],
        ];
        @endphp

        @foreach($sections as $section)
        @php $baseIdx = $loop->index * 20; @endphp
        <div>
            {{-- Section header --}}
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                    {{ $section['color']==='emerald' ? 'bg-emerald-500/10 border border-emerald-500/20' :
                       ($section['color']==='purple'  ? 'bg-purple-500/10 border border-purple-500/20'  :
                       ($section['color']==='amber'   ? 'bg-amber-500/10 border border-amber-500/20'   :
                                                        'bg-brand-500/10 border border-brand-500/20')) }}">
                    <svg class="w-4.5 h-4.5 {{ $section['color']==='emerald' ? 'text-emerald-400' : ($section['color']==='purple' ? 'text-purple-400' : ($section['color']==='amber' ? 'text-amber-400' : 'text-brand-400')) }}"
                         style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $section['icon'] }}"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-surface-100">{{ $section['title'] }}</h2>
            </div>

            {{-- FAQs --}}
            <div class="space-y-2.5">
                @foreach($section['faqs'] as $faq)
                @php $idx = $baseIdx + $loop->index; @endphp
                <div class="card overflow-hidden">
                    <button type="button"
                        @click="open === {{ $idx }} ? open = null : open = {{ $idx }}"
                        class="w-full flex items-start justify-between gap-4 px-5 py-4 text-left hover:bg-surface-700/40 transition-colors">
                        <span class="font-semibold text-surface-100 text-sm leading-snug">{{ $faq[0] }}</span>
                        <svg class="w-4 h-4 text-surface-500 shrink-0 mt-0.5 transition-transform duration-200"
                             :class="open === {{ $idx }} ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === {{ $idx }}"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="px-5 pb-5 border-t border-surface-700">
                        <p class="pt-4 text-surface-400 text-sm leading-relaxed">{{ $faq[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>
</section>

<div class="section-divider"></div>

{{-- Still need help --}}
<section class="py-16 px-5 sm:px-8 bg-surface-900/40">
    <div class="max-w-2xl mx-auto text-center">
        <div class="w-12 h-12 rounded-2xl bg-brand-500/12 border border-brand-500/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-black text-surface-50 mb-3">Still have a question?</h2>
        <p class="text-surface-400 mb-7 leading-relaxed">We read every message and reply within 24 hours on weekdays — usually much faster.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('contact') }}" class="btn-primary px-8 py-3 font-bold shadow-glow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Us
            </a>
            <a href="{{ route('register') }}" class="btn-secondary px-8 py-3 font-semibold">
                Start Free Test
            </a>
        </div>
    </div>
</section>

{{-- Footer --}}
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
            <a href="{{ route('terms') }}"   class="hover:text-surface-300 transition-colors">Terms</a>
        </div>
        <p class="text-xs text-surface-700">&copy; {{ date('Y') }} IELTS Band AI</p>
    </div>
</footer>

</body>
</html>
