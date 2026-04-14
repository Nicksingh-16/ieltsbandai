<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — IELTS Band AI</title>
    <meta name="description" content="Get in touch with the IELTS Band AI team. We reply within 24 hours.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .section-divider { height:1px; background:linear-gradient(90deg,transparent,rgba(51,65,85,0.8),transparent); }
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
            <a href="{{ route('about') }}" class="btn-ghost text-sm">About</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Start Free</a>
        </nav>
    </div>
</header>


{{-- ── Page hero ── --}}
<section class="relative py-16 sm:py-24 px-5 sm:px-8 overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-[500px] h-[350px] bg-brand-500/7 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-2xl mx-auto text-center">
        <div class="tag-cyan inline-flex mb-6">We reply within 24 hours</div>
        <h1 class="text-4xl sm:text-5xl font-black text-surface-50 leading-tight mb-5">
            How can we help?
        </h1>
        <p class="text-surface-400 text-lg leading-relaxed">
            Questions about the platform, feedback, or something not working right? Send us a message and we'll get back to you quickly.
        </p>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Main content: form + sidebar ── --}}
<section class="py-16 px-5 sm:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- Left: Contact info ── --}}
            <div class="space-y-5">

                {{-- Response time --}}
                <div class="card p-5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5 text-emerald-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-surface-100 mb-1">Response time</p>
                    <p class="text-surface-500 text-sm leading-relaxed">We reply within <span class="text-emerald-400 font-semibold">24 hours</span> on weekdays. Usually much faster.</p>
                </div>

                {{-- Email --}}
                <div class="card p-5">
                    <div class="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5 text-brand-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-surface-100 mb-1">Email us directly</p>
                    <a href="mailto:support@ieltsbandai.com" class="text-brand-400 hover:text-brand-300 text-sm transition-colors font-medium">
                        support@ieltsbandai.com
                    </a>
                </div>

                {{-- What to include --}}
                <div class="card p-5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5 text-amber-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-surface-100 mb-2">Helpful to include</p>
                    <ul class="space-y-1.5 text-surface-500 text-xs">
                        @foreach(['Your registered email address','Which skill (Speaking, Writing, etc.)','What you expected to happen','What actually happened'] as $tip)
                        <li class="flex items-start gap-2">
                            <svg class="w-3 h-3 text-amber-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ $tip }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- FAQ link --}}
                <a href="{{ route('faq') }}" class="card p-5 flex items-center gap-3 card-hover group block">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-purple-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-surface-100 text-sm">Browse the FAQ</p>
                        <p class="text-surface-500 text-xs">Quick answers to common questions</p>
                    </div>
                    <svg class="w-4 h-4 text-surface-600 group-hover:text-brand-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

            </div>


            {{-- Right: Contact form ── --}}
            <div class="lg:col-span-2">

                {{-- Success message --}}
                @if(session('success'))
                <div class="mb-6 flex items-start gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-5 py-4 rounded-2xl">
                    <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold mb-0.5">Message sent!</p>
                        <p class="text-sm text-emerald-400/80">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="mb-6 flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-300 px-5 py-4 rounded-2xl">
                    <svg class="w-5 h-5 shrink-0 mt-0.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <ul class="text-sm space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="card p-6 sm:p-8 space-y-5"
                      x-data="{ loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Name + Email row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="label">Your name <span class="text-red-400">*</span></label>
                            <input id="name" name="name" type="text"
                                value="{{ old('name', auth()->user()?->name) }}"
                                required placeholder="Rahul Sharma"
                                class="input @error('name') border-red-500/60 focus:ring-red-500 @enderror">
                            @error('name')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email address <span class="text-red-400">*</span></label>
                            <input id="email" name="email" type="email"
                                value="{{ old('email', auth()->user()?->email) }}"
                                required placeholder="you@example.com"
                                class="input @error('email') border-red-500/60 focus:ring-red-500 @enderror">
                            @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label for="subject" class="label">Subject <span class="text-red-400">*</span></label>
                        <select id="subject" name="subject"
                            class="input @error('subject') border-red-500/60 focus:ring-red-500 @enderror">
                            <option value="" disabled {{ !old('subject') ? 'selected' : '' }}>Select a topic…</option>
                            @foreach([
                                'My band score seems incorrect',
                                'Speaking test — microphone issue',
                                'Writing feedback is missing',
                                'I cannot log in / sign up',
                                'Payment or subscription issue',
                                'Question about the Pro plan',
                                'I found a bug',
                                'General feedback or suggestion',
                                'Other',
                            ] as $opt)
                            <option value="{{ $opt }}" {{ old('subject') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="label">
                            Message <span class="text-red-400">*</span>
                            <span class="text-surface-600 font-normal ml-1">(min. 20 characters)</span>
                        </label>
                        <textarea id="message" name="message" rows="6" required
                            placeholder="Describe your question or issue in as much detail as you can. The more you tell us, the faster we can help."
                            class="input resize-none @error('message') border-red-500/60 focus:ring-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-between gap-4 pt-1">
                        <p class="text-xs text-surface-600">We read every message. We reply within 24 hours.</p>
                        <button type="submit" class="btn-primary px-7 py-3 font-bold shrink-0"
                            :disabled="loading"
                            :class="loading ? 'opacity-60 cursor-wait' : ''">
                            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span x-show="!loading">Send Message</span>
                            <span x-show="loading">Sending…</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</section>

<div class="section-divider"></div>


{{-- ── Common questions ── --}}
<section class="py-16 px-5 sm:px-8 bg-surface-900/40">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-black text-surface-50 mb-2">Common questions</h2>
            <p class="text-surface-400 text-sm">Check these first — you might find your answer here.</p>
        </div>

        <div class="space-y-3" x-data="{ open: null }">
            @foreach([
                ['My band score looks too low. Is the AI wrong?', 'Our AI scores are calibrated to match real IELTS examiners with 90%+ accuracy. If your score is lower than expected, it usually means there are errors or weaknesses the AI has found that are genuinely affecting your score. Read the detailed feedback — it will tell you exactly what is wrong. If you still think there is an error, contact us with the test ID.'],
                ['The speaking recorder is not working.', 'The speaking test requires microphone access in your browser. Make sure you have allowed microphone permissions (look for the mic icon in your browser address bar). This works on Chrome and Firefox. Safari on iPhone can sometimes be unreliable — try Chrome instead.'],
                ['I submitted my writing but got no result.', 'This usually happens when the result page times out. Try refreshing after 30 seconds. If the result is still not there, go to your Dashboard — your test result is saved there. If it is missing entirely, contact us with the approximate time you submitted and your email address.'],
                ['I paid for Pro but my account still shows Free.', 'Payments via Razorpay are usually instant. If your account has not upgraded within 5 minutes, contact us immediately with your Razorpay payment ID (you will find it in your payment confirmation email). We will upgrade your account manually within hours.'],
                ['Can I get a refund?', 'Yes. We offer a 7-day money-back guarantee with no questions asked. Contact us within 7 days of your payment with your payment ID and we will process the refund.'],
                ['How accurate is the AI band score really?', 'Our system is trained on thousands of real IELTS writing submissions with known examiner scores. In testing, it matches the real examiner score within ±0.5 band in over 90% of cases. It is not perfect — no automated system is — but it is significantly more accurate than self-assessment and much faster than human feedback.'],
            ] as $faq)
            @php $idx = $loop->index; @endphp
            <div class="card overflow-hidden">
                <button type="button"
                    @click="open === {{ $idx }} ? open = null : open = {{ $idx }}"
                    class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-surface-700/40 transition-colors">
                    <span class="font-semibold text-surface-100 text-sm leading-snug">{{ $faq[0] }}</span>
                    <svg class="w-4 h-4 text-surface-500 shrink-0 transition-transform duration-200"
                         :class="open === {{ $idx }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $idx }}"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-5 pb-5 text-surface-400 text-sm leading-relaxed border-t border-surface-700">
                    <p class="pt-4">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-8">
            <p class="text-surface-500 text-sm">
                Still not answered?
                <a href="{{ route('faq') }}" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">Read the full FAQ →</a>
            </p>
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
