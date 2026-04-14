<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — IELTS Band AI</title>
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
            <a href="{{ route('terms') }}"   class="btn-ghost text-sm hidden sm:inline-flex">Terms</a>
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
                <nav class="space-y-1" id="toc">
                    @foreach([
                        ['#info',       '1. Information we collect'],
                        ['#use',        '2. How we use it'],
                        ['#ai',         '3. AI & third parties'],
                        ['#retention',  '4. Data retention'],
                        ['#rights',     '5. Your rights'],
                        ['#cookies',    '6. Cookies'],
                        ['#security',   '7. Security'],
                        ['#children',   '8. Children'],
                        ['#changes',    '9. Changes'],
                        ['#contact',    '10. Contact'],
                    ] as [$href, $label])
                    <a href="{{ $href }}"
                       class="block text-xs text-surface-500 hover:text-brand-400 hover:bg-surface-700/40 px-2.5 py-1.5 rounded-lg transition-all leading-snug">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                <div class="mt-5 pt-5 border-t border-surface-700">
                    <a href="{{ route('terms') }}" class="flex items-center gap-2 text-xs text-surface-500 hover:text-surface-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Read Terms of Use
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
                        <div class="tag-cyan inline-flex mb-3">Legal document</div>
                        <h1 class="text-3xl sm:text-4xl font-black text-surface-50">Privacy Policy</h1>
                    </div>
                    <div class="bg-surface-900/60 border border-surface-700 rounded-xl px-4 py-3 text-right shrink-0">
                        <p class="text-[10px] text-surface-500 uppercase tracking-widest">Last updated</p>
                        <p class="text-sm font-semibold text-surface-200 mt-0.5">{{ date('d M Y') }}</p>
                    </div>
                </div>
                <p class="text-surface-400 leading-relaxed">
                    This Privacy Policy explains what information IELTS Band AI collects when you use our platform, how we use it, and what rights you have. We have written it in plain language — no legal jargon.
                </p>
                <p class="text-surface-500 text-sm mt-3">
                    By creating an account or using the Platform, you agree to this policy. If you disagree, please do not use the Platform.
                </p>
            </div>

            {{-- Quick summary cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach([
                    ['We never sell your data', 'Your personal information is never sold or shared with advertisers.', 'emerald', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['Audio is never stored', 'Speaking recordings are deleted immediately after transcription.', 'brand', 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                    ['Delete anytime', 'You can delete your account and all data at any time from settings.', 'purple', 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                ] as [$title, $desc, $color, $icon])
                <div class="card p-5 flex gap-3">
                    <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center
                        {{ $color==='emerald' ? 'bg-emerald-500/12 border border-emerald-500/20' : ($color==='purple' ? 'bg-purple-500/12 border border-purple-500/20' : 'bg-brand-500/12 border border-brand-500/20') }}">
                        <svg class="w-4 h-4 {{ $color==='emerald' ? 'text-emerald-400' : ($color==='purple' ? 'text-purple-400' : 'text-brand-400') }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            {{-- Section 1 --}}
            <div id="info" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">1</span>
                    <h2 class="text-xl font-bold text-surface-50">Information we collect</h2>
                </div>

                <div class="space-y-5">
                    @foreach([
                        ['Account information', 'When you register, we collect your name, email address, and password (stored as a bcrypt hash — we cannot read it). If you sign in with Google, we receive your name, email, and profile picture from Google. We never receive your Google password.'],
                        ['Test and evaluation data', 'When you take a test, we store your written essay or typed responses, your predicted band scores and AI feedback, the date and time of each submission, and your selected test type (Academic / General Training).'],
                        ['Speaking recordings', 'Speaking test audio is sent to our servers for transcription and AI evaluation. We do not store your audio recordings permanently. Audio files are deleted immediately after transcription is complete. We retain only the text transcript and evaluation scores.'],
                        ['Payment information', 'We do not store your card number, CVV, bank account, or UPI details. All payments are processed by Razorpay (PCI-DSS compliant, regulated by RBI). We receive only a payment confirmation token and your subscription status.'],
                        ['Usage data', 'We automatically collect your IP address, browser type, device type, pages visited, and time on the Platform. This is used only to fix bugs and improve performance.'],
                    ] as [$subtitle, $body])
                    <div class="border-l-2 border-surface-700 pl-4">
                        <p class="text-sm font-semibold text-surface-200 mb-1.5">{{ $subtitle }}</p>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $body }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Section 2 --}}
            <div id="use" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">2</span>
                    <h2 class="text-xl font-bold text-surface-50">How we use your information</h2>
                </div>
                <div class="space-y-2.5">
                    @foreach([
                        ['To run the Platform', 'Creating your account, running tests, and displaying your results.'],
                        ['To improve AI accuracy', 'Aggregated and anonymised test data helps us calibrate our scoring models. We never use identifiable data for model training without consent.'],
                        ['To communicate with you', 'Account confirmations, support replies, and important service updates. We do not send marketing emails unless you opt in.'],
                        ['To process payments', 'Managing your subscription and billing history.'],
                        ['For security', 'Detecting and preventing fraud, abuse, and unauthorised access.'],
                    ] as [$title, $desc])
                    <div class="flex gap-3 bg-surface-900/40 rounded-xl p-4">
                        <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <span class="text-sm font-semibold text-surface-200">{{ $title }} — </span>
                            <span class="text-sm text-surface-400">{{ $desc }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-5 bg-emerald-500/8 border border-emerald-500/20 rounded-xl px-5 py-4">
                    <p class="text-sm text-emerald-300 font-semibold mb-1">We do not sell your data.</p>
                    <p class="text-xs text-surface-400 leading-relaxed">Your personal information is never sold to third parties. We do not use your data for advertising or share it with marketing companies.</p>
                </div>
            </div>

            {{-- Section 3 --}}
            <div id="ai" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">3</span>
                    <h2 class="text-xl font-bold text-surface-50">AI processing & third-party services</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed mb-5">
                    AI evaluation of your writing and speaking is powered by third-party AI APIs. Your submissions are sent to these services for processing. Both operate under enterprise data processing agreements and do not use your data to train their public models.
                </p>
                <div class="space-y-2.5">
                    @foreach([
                        ['OpenAI API',       'Writing evaluation and speaking scoring', 'brand'],
                        ['Google Gemini',    'Vocabulary analysis and supplementary feedback', 'brand'],
                        ['Razorpay',         'Payment processing (PCI-DSS compliant, RBI regulated)', 'emerald'],
                        ['Google OAuth',     'Optional social sign-in (no password shared)', 'emerald'],
                    ] as [$name, $purpose, $color])
                    <div class="flex items-center justify-between bg-surface-900/40 rounded-xl px-5 py-3 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $color==='emerald' ? 'bg-emerald-400' : 'bg-brand-400' }} shrink-0"></div>
                            <span class="text-sm font-semibold text-surface-200">{{ $name }}</span>
                        </div>
                        <span class="text-xs text-surface-500 text-right">{{ $purpose }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Sections 4–10 in a compact grouped layout --}}
            @foreach([
                [
                    'retention', '4', 'Data retention',
                    'We retain your account data and test history for as long as your account is active. If you delete your account, all personal data and test results are permanently deleted within 30 days. Anonymised, aggregated data (with no personally identifiable information) may be retained indefinitely for research and improvement.',
                    null
                ],
                [
                    'rights', '5', 'Your rights',
                    'You have the right to access a copy of your data, correct inaccurate data, delete your account and all associated data, receive your test history in a portable format, and object to how we process your data. To exercise any of these rights, email us at support@ieltsbandai.com or use the contact form.',
                    [
                        'Access — request a copy of data we hold about you',
                        'Correction — fix inaccurate or outdated information',
                        'Deletion — permanently delete your account and all data',
                        'Portability — export your test history in a readable format',
                        'Objection — object to specific types of processing',
                    ]
                ],
                [
                    'cookies', '6', 'Cookies',
                    'We use only one essential cookie — the Laravel session cookie that keeps you logged in. We do not use advertising cookies, third-party analytics cookies, or tracking pixels. You can disable cookies in your browser, but this will log you out and prevent you from staying signed in.',
                    null
                ],
                [
                    'security', '7', 'Security',
                    'We use HTTPS encryption for all data in transit. Passwords are hashed using bcrypt. We conduct regular security reviews of our infrastructure. However, no system is completely secure — we cannot guarantee absolute security. If you notice a security issue, please report it to support@ieltsbandai.com immediately.',
                    null
                ],
                [
                    'children', '8', 'Children\'s privacy',
                    'The Platform is not intended for users under 13 years of age. We do not knowingly collect personal data from children under 13. If you believe a child has created an account without parental consent, contact us and we will delete the account immediately.',
                    null
                ],
                [
                    'changes', '9', 'Changes to this policy',
                    'We may update this Privacy Policy from time to time. When we do, we will update the "Last updated" date at the top of this page. For significant changes, we will notify you by email or with a notice on the Platform. Continued use after changes take effect constitutes acceptance of the updated policy.',
                    null
                ],
                [
                    'contact', '10', 'Contact',
                    'If you have any questions about this Privacy Policy or how we handle your data, please contact us:',
                    ['Email: support@ieltsbandai.com', 'Contact form: ieltsbandai.com/contact']
                ],
            ] as [$id, $num, $heading, $body, $bullets])
            <div id="{{ $id }}" class="card p-7 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/15 border border-brand-500/25 text-brand-400 text-xs font-black flex items-center justify-center shrink-0">{{ $num }}</span>
                    <h2 class="text-xl font-bold text-surface-50">{{ $heading }}</h2>
                </div>
                <p class="text-sm text-surface-400 leading-relaxed {{ $bullets ? 'mb-4' : '' }}">{{ $body }}</p>
                @if($bullets)
                <div class="space-y-2">
                    @foreach($bullets as $bullet)
                    <div class="flex items-start gap-2.5 text-sm text-surface-400">
                        <svg class="w-3.5 h-3.5 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
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
                <a href="{{ route('terms') }}"   class="btn-secondary text-sm px-5 py-2.5">Read Terms of Use</a>
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
            <a href="{{ route('privacy') }}" class="hover:text-surface-300 transition-colors font-medium text-surface-400">Privacy</a>
            <a href="{{ route('terms') }}"   class="hover:text-surface-300 transition-colors">Terms</a>
        </div>
        <p class="text-xs text-surface-700">&copy; {{ date('Y') }} IELTS Band AI</p>
    </div>
</footer>

</body>
</html>
