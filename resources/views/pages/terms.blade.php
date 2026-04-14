<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Use — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-200 font-sans antialiased">

{{-- Nav --}}
<header class="sticky top-0 z-50 bg-surface-950/90 backdrop-blur-md border-b border-surface-700/50">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('privacy') }}" class="btn-ghost text-sm hidden sm:inline-flex">Privacy</a>
            <a href="{{ route('contact') }}" class="btn-ghost text-sm">Contact</a>
            <a href="{{ route('home') }}"    class="btn-secondary text-sm px-4 py-2">← Home</a>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-5 sm:px-8 py-12">
    <div class="flex gap-12 items-start">

        {{-- ── Sticky sidebar TOC ── --}}
        <aside class="hidden lg:block w-64 shrink-0 sticky top-20 self-start">
            <div class="card p-5">
                <p class="text-[10px] font-bold text-surface-500 uppercase tracking-widest mb-4">On this page</p>
                <nav class="space-y-1">
                    @foreach([
                        ['#about',       '1. About the Platform'],
                        ['#eligibility', '2. Eligibility'],
                        ['#account',     '3. Account rules'],
                        ['#use',         '4. Acceptable use'],
                        ['#ai',          '5. AI limitations'],
                        ['#payment',     '6. Payments & refunds'],
                        ['#ip',          '7. Intellectual property'],
                        ['#disclaimer',  '8. Disclaimer'],
                        ['#liability',   '9. Limitation of liability'],
                        ['#termination', '10. Termination'],
                        ['#governing',   '11. Governing law'],
                        ['#changes',     '12. Changes'],
                        ['#contact',     '13. Contact'],
                    ] as [$href, $label])
                    <a href="{{ $href }}"
                       class="block text-xs text-surface-500 hover:text-brand-400 hover:bg-surface-700/40 px-2.5 py-1.5 rounded-lg transition-all leading-snug">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>
                <div class="mt-5 pt-5 border-t border-surface-700">
                    <a href="{{ route('privacy') }}" class="flex items-center gap-2 text-xs text-surface-500 hover:text-surface-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Read Privacy Policy
                    </a>
                </div>
            </div>
        </aside>

        {{-- ── Main content ── --}}
        <main class="flex-1 min-w-0 space-y-3">

            {{-- Page header --}}
            <div class="card p-7 sm:p-9">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                    <div>
                        <div class="tag-amber inline-flex mb-3">Legal document</div>
                        <h1 class="text-3xl sm:text-4xl font-black text-surface-50">Terms of Use</h1>
                    </div>
                    <div class="bg-surface-900/60 border border-surface-700 rounded-xl px-4 py-3 text-right shrink-0">
                        <p class="text-[10px] text-surface-500 uppercase tracking-widest">Last updated</p>
                        <p class="text-sm font-semibold text-surface-200 mt-0.5">{{ date('d M Y') }}</p>
                    </div>
                </div>
                <p class="text-surface-400 leading-relaxed">
                    These Terms of Use govern your access to and use of the IELTS Band AI website and services. By creating an account or using the Platform, you agree to these Terms. Please read them before using the service.
                </p>
            </div>

            {{-- Important disclaimer banner --}}
            <div class="bg-amber-500/8 border border-amber-500/25 rounded-2xl p-5 flex gap-4">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-300 mb-1">Not affiliated with any official IELTS body</p>
                    <p class="text-xs text-surface-400 leading-relaxed">IELTS Band AI is an independent practice platform. We are not affiliated with, endorsed by, or associated with the British Council, IDP Education, Cambridge Assessment English, or any official IELTS testing body. Band scores we provide are <strong class="text-surface-300">estimates for practice purposes only</strong> — not official IELTS results.</p>
                </div>
            </div>

            {{-- Section 1: About --}}
            <div id="about" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">1</span>
                    <h2 class="text-xl font-bold text-surface-50">About the Platform</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed mb-3">
                    IELTS Band AI provides online IELTS mock tests for Reading, Listening, Writing, and Speaking — along with AI-generated band score predictions and feedback. The Platform is for personal, non-commercial study purposes only.
                </p>
                <p class="text-sm text-surface-400 leading-relaxed">
                    The band scores and feedback generated are predictions based on statistical AI models. They are designed to be as accurate as possible but are <strong class="text-surface-300">not official IELTS results and carry no legal or academic weight.</strong>
                </p>
            </div>

            {{-- Section 2: Eligibility --}}
            <div id="eligibility" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">2</span>
                    <h2 class="text-xl font-bold text-surface-50">Eligibility</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed">
                    You must be at least 13 years old to use the Platform. By using it, you confirm that you meet this requirement. If you are under 18, you should review these Terms with a parent or guardian before creating an account.
                </p>
            </div>

            {{-- Section 3: Account --}}
            <div id="account" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">3</span>
                    <h2 class="text-xl font-bold text-surface-50">Account responsibilities</h2>
                </div>
                <div class="space-y-2.5">
                    @foreach([
                        'Keep your login credentials secure and do not share your account with others.',
                        'Provide accurate information when registering. Do not impersonate another person.',
                        'You are responsible for all activity that occurs under your account.',
                        'Notify us immediately at support@ieltsbandai.com if you suspect unauthorised access.',
                    ] as $item)
                    <div class="flex items-start gap-3 bg-surface-900/40 rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $item }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Section 4: Acceptable use --}}
            <div id="use" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">4</span>
                    <h2 class="text-xl font-bold text-surface-50">Acceptable use</h2>
                </div>
                <p class="text-sm text-surface-400 mb-4">You agree not to use the Platform in any of the following ways:</p>
                <div class="space-y-2">
                    @foreach([
                        'Use the Platform for any unlawful purpose.',
                        'Submit another person\'s writing or speaking as your own to misrepresent your IELTS ability.',
                        'Attempt to reverse-engineer, scrape, or copy the Platform\'s content, AI models, or question bank.',
                        'Circumvent credit limits, subscription restrictions, or access controls.',
                        'Create multiple accounts to obtain free credits fraudulently.',
                        'Attempt to disrupt, overload, or attack the Platform\'s servers or infrastructure.',
                    ] as $item)
                    <div class="flex items-start gap-3 bg-red-500/6 border border-red-500/15 rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $item }}</p>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-surface-500 mt-4">Violations may result in immediate suspension or permanent ban of your account.</p>
            </div>

            {{-- Section 5: AI limitations --}}
            <div id="ai" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">5</span>
                    <h2 class="text-xl font-bold text-surface-50">AI evaluation — accuracy and limitations</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed mb-4">
                    The band scores and feedback generated by our AI are predictions based on statistical models trained on real IELTS data. They are designed to be as accurate as possible, but they are <strong class="text-surface-300">not official IELTS results and carry no legal or academic weight.</strong>
                </p>
                <p class="text-sm text-surface-400 leading-relaxed mb-4">
                    Predicted scores may differ from actual exam results due to examiner subjectivity, test-day performance, handwriting legibility (for paper tests), and differences in question difficulty between sessions.
                </p>
                <div class="bg-amber-500/8 border border-amber-500/20 rounded-xl px-5 py-4">
                    <p class="text-sm font-semibold text-amber-300 mb-1">Use scores as a guide, not a guarantee.</p>
                    <p class="text-xs text-surface-400 leading-relaxed">We do not accept liability for decisions made based on our predicted band scores — including university applications, visa applications, or employment decisions.</p>
                </div>
            </div>

            {{-- Section 6: Payments --}}
            <div id="payment" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">6</span>
                    <h2 class="text-xl font-bold text-surface-50">Subscription, payments & refunds</h2>
                </div>
                <div class="space-y-5">
                    @foreach([
                        ['Free plan', 'bg-surface-700/40 border-surface-600', 'New accounts receive 3 test credits on signup. Free credits are non-transferable and cannot be converted to cash. We reserve the right to change the free credit amount at any time.'],
                        ['Pro subscription', 'bg-brand-500/6 border-brand-500/20', 'The Pro plan is a monthly recurring subscription at ₹99/month (or the amount shown at checkout). Payment is via Razorpay. Your subscription renews automatically each month until cancelled.'],
                        ['Cancellation', 'bg-surface-700/40 border-surface-600', 'You may cancel at any time from your account settings. Cancellation takes effect at the end of the current billing period. You keep Pro access until that date.'],
                        ['Refunds', 'bg-emerald-500/6 border-emerald-500/20', 'We offer a 7-day money-back guarantee for new Pro subscriptions. Contact us at support@ieltsbandai.com within 7 days of payment with your Razorpay payment ID. Refunds are processed within 5–10 business days to your original payment method. Refunds are not available for accounts suspended due to Terms violations.'],
                    ] as [$subtitle, $style, $body])
                    <div class="border {{ $style }} rounded-xl p-4">
                        <p class="text-sm font-semibold text-surface-200 mb-1.5">{{ $subtitle }}</p>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $body }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Section 7: IP --}}
            <div id="ip" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">7</span>
                    <h2 class="text-xl font-bold text-surface-50">Intellectual property</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed mb-3">
                    All Platform content — including the AI evaluation logic, question bank, design, source code, and branding — is owned by IELTS Band AI and protected by applicable intellectual property law. You may not reproduce, distribute, or create derivative works from our content without written permission.
                </p>
                <div class="bg-brand-500/8 border border-brand-500/20 rounded-xl px-5 py-4">
                    <p class="text-sm font-semibold text-brand-300 mb-1">Your content stays yours.</p>
                    <p class="text-xs text-surface-400 leading-relaxed">The essays and speaking responses you submit remain your property. By submitting them, you grant us a limited licence to process them through our AI systems solely for the purpose of generating your evaluation. We do not claim ownership of your writing or speech.</p>
                </div>
            </div>

            {{-- Sections 8–13 compact --}}
            @foreach([
                ['disclaimer', '8', 'Disclaimer of warranties',
                 'The Platform is provided "as is" and "as available" without any warranty of any kind, express or implied. We do not warrant that the Platform will be uninterrupted, error-free, or free of viruses. We do not guarantee that AI evaluations will be accurate in every case.',
                 null, null],
                ['liability', '9', 'Limitation of liability',
                 'To the maximum extent permitted by applicable law, IELTS Band AI shall not be liable for any indirect, incidental, special, consequential, or punitive damages — including losses from reliance on band score predictions, data loss, or service interruptions. Our total liability for any claim shall not exceed the amount you paid us in the 30 days preceding the claim.',
                 null, null],
                ['termination', '10', 'Termination',
                 'We may suspend or terminate your account if you violate these Terms. If we do so without cause, we will refund any remaining Pro subscription days on a pro-rata basis. You may delete your account at any time from your profile settings.',
                 null, null],
                ['governing', '11', 'Governing law',
                 'These Terms are governed by the laws of India. Any disputes arising from these Terms shall be subject to the exclusive jurisdiction of the courts of India.',
                 null, null],
                ['changes', '12', 'Changes to these Terms',
                 'We may update these Terms from time to time. We will notify you of significant changes by email or by displaying a notice on the Platform. Continued use after changes take effect constitutes acceptance of the updated Terms.',
                 null, null],
                ['contact', '13', 'Contact',
                 'If you have questions about these Terms, contact us:',
                 ['Email: support@ieltsbandai.com', 'Contact form: use the Contact page on our website'],
                 null],
            ] as [$id, $num, $heading, $body, $bullets, $_])
            <div id="{{ $id }}" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">{{ $num }}</span>
                    <h2 class="text-xl font-bold text-surface-50">{{ $heading }}</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed {{ $bullets ? 'mb-4' : '' }}">{{ $body }}</p>
                @if($bullets)
                <div class="space-y-2">
                    @foreach($bullets as $bullet)
                    <div class="flex items-center gap-2.5 text-sm text-surface-400">
                        <svg class="w-3.5 h-3.5 text-brand-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $bullet }}
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach

            {{-- Bottom links --}}
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ route('privacy') }}" class="btn-secondary text-sm px-5 py-2.5">Read Privacy Policy</a>
                <a href="{{ route('contact') }}" class="btn-ghost text-sm px-5 py-2.5">Ask us a question</a>
            </div>

        </main>
    </div>
</div>

{{-- Footer --}}
<footer class="border-t border-surface-700/50 mt-10 py-8 px-5 sm:px-8">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            </div>
            <span class="text-sm font-bold text-surface-50">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-5 text-xs text-surface-600">
            <a href="{{ route('about') }}"   class="hover:text-surface-300 transition-colors">About</a>
            <a href="{{ route('faq') }}"     class="hover:text-surface-300 transition-colors">FAQ</a>
            <a href="{{ route('contact') }}" class="hover:text-surface-300 transition-colors">Contact</a>
            <a href="{{ route('privacy') }}" class="hover:text-surface-300 transition-colors">Privacy</a>
            <a href="{{ route('terms') }}"   class="hover:text-surface-300 transition-colors font-medium text-surface-400">Terms</a>
        </div>
        <p class="text-xs text-surface-700">&copy; {{ date('Y') }} IELTS Band AI</p>
    </div>
</footer>

</body>
</html>
