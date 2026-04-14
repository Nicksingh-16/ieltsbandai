<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Speaking Topics 2024 — Parts 1, 2 & 3 | IELTS Band AI</title>
    <meta name="description" content="Browse 30 real IELTS speaking topics for Parts 1, 2, and 3. Each topic includes sample questions, cue cards, and expert practice tips to help you score higher.">
    <meta name="keywords" content="IELTS speaking topics, IELTS speaking part 1, IELTS speaking part 2, IELTS speaking part 3, IELTS cue card topics, IELTS speaking questions 2024">
    <meta property="og:title" content="IELTS Speaking Topics 2024 — Parts 1, 2 & 3">
    <meta property="og:description" content="30 real IELTS speaking topics with sample questions and practice tips for all three parts.">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url('/ielts-speaking-topics') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-100 min-h-screen">

{{-- Minimal nav --}}
<nav class="bg-surface-900 border-b border-surface-700 px-4 py-3 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/></svg>
        </div>
        <span class="font-bold text-surface-50">IELTS Band AI</span>
    </a>
    <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Free Sign Up</a>
</nav>

<main class="max-w-4xl mx-auto px-4 py-12">

    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-bold text-surface-50 mb-3">IELTS Speaking Topics</h1>
        <p class="text-surface-400 max-w-2xl mx-auto">30 frequently tested IELTS speaking topics across all three parts — with sample questions, cue cards, and practice tips for each one.</p>
    </div>

    {{-- ── Part 1 ── --}}
    <section class="mb-14">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-brand-500/15 flex items-center justify-center shrink-0">
                <span class="text-brand-400 font-bold text-sm">P1</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-surface-50">Part 1 — Personal Questions</h2>
                <p class="text-surface-500 text-sm">4–5 minutes · Familiar, everyday topics · Short, direct answers</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach([
                ['Hometown', 'Where are you from, and what do you like most about it?', 'Give 2–3 specific details about your hometown rather than vague descriptions. Mention something unique.'],
                ['Work or Study', 'Are you currently working or studying? What do you enjoy most about it?', 'Explain your role or subject clearly, then extend your answer with a reason or example.'],
                ['Free Time & Hobbies', 'What do you like to do in your free time? Has this changed over the years?', 'Describe your hobby vividly, say how often you do it, and why you enjoy it.'],
                ['Music', 'What kind of music do you listen to? Does music affect your mood?', 'Name a specific genre or artist — vague answers like "I like all kinds of music" score poorly.'],
                ['Food', 'What is your favourite food, and do you prefer eating at home or in restaurants?', 'Use sensory language — describe taste, texture, smell. Show vocabulary range.'],
                ['Travel', 'Do you enjoy travelling? Where is the most memorable place you have visited?', 'Use narrative — tell a short story about a specific trip rather than generalising.'],
                ['Technology', 'How important is technology in your daily life?', 'Go beyond "I use my phone a lot." Mention specific tools and their concrete impact.'],
                ['Sports & Exercise', 'Do you play any sports? Why is exercise important to you?', 'If you rarely exercise, be honest — but explain why and what you do instead.'],
                ['Reading', 'Do you enjoy reading? What kind of books or articles do you prefer?', 'Mention a specific book, author, or article to make your answer memorable and personal.'],
                ['Weather', 'What is the weather like in your country? How does weather affect your lifestyle?', 'Link weather to daily activities or cultural events for a richer, more connected answer.'],
            ] as $i => [$topic, $question, $tip])
            <div class="card p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <h3 class="font-semibold text-surface-100">{{ $topic }}</h3>
                    <span class="text-xs text-brand-400 font-mono shrink-0">#{{ $i + 1 }}</span>
                </div>
                <p class="text-sm text-surface-300 mb-3 italic">"{{ $question }}"</p>
                <div class="border-t border-surface-700 pt-3">
                    <p class="text-xs text-surface-500 flex gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ $tip }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Part 2 ── --}}
    <section class="mb-14">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0">
                <span class="text-purple-400 font-bold text-sm">P2</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-surface-50">Part 2 — Long Turn (Cue Card)</h2>
                <p class="text-surface-500 text-sm">3–4 minutes · 1 minute to prepare · Speak for 1–2 minutes</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach([
                [
                    'A Person Who Inspired You',
                    'Describe a person who has had a significant influence on your life.',
                    ['Who this person is', 'How you know them', 'What they did or said that influenced you', 'Why they are important to you'],
                    'Use specific anecdotes — a single memorable moment is more powerful than a general description.'
                ],
                [
                    'A Place You Would Like to Visit',
                    'Describe a place you would like to visit in the future.',
                    ['Where the place is', 'What it is famous for', 'How you would get there', 'Why you want to visit it'],
                    'Use sensory language and speculate with phrases like "I imagine it would be…" to demonstrate range.'
                ],
                [
                    'A Book or Film That Affected You',
                    'Describe a book or film that made a strong impression on you.',
                    ['What it is called and who made it', 'What it is about', 'How you came across it', 'Why it affected you deeply'],
                    'Focus on your emotional reaction and what the work made you think or feel, not just the plot summary.'
                ],
                [
                    'A Skill You Would Like to Learn',
                    'Describe a skill you would really like to learn in the future.',
                    ['What the skill is', 'Why you want to learn it', 'How you would go about learning it', 'How it would benefit your life'],
                    'Show your ability to speculate and discuss future plans — use conditionals naturally.'
                ],
                [
                    'An Unforgettable Journey',
                    'Describe a journey or trip that you remember well.',
                    ['Where you went and why', 'Who you were with', 'What happened during the trip', 'Why it was memorable'],
                    'Use narrative tenses accurately (past simple, past continuous, past perfect) to show grammatical range.'
                ],
                [
                    'A Time You Helped Someone',
                    'Describe a time when you helped someone.',
                    ['Who the person was', 'Why they needed help', 'How you helped them', 'How you felt afterwards'],
                    'Use this as a chance to show empathy and reflection — go beyond what happened to how it felt.'
                ],
                [
                    'A Piece of Technology You Use',
                    'Describe a piece of technology that you find very useful.',
                    ['What the technology is', 'How long you have been using it', 'How you use it in daily life', 'Why you could not imagine life without it'],
                    'Avoid clichés — try to pick something specific rather than just "my smartphone."'
                ],
                [
                    'A Celebration or Festival',
                    'Describe a festival or celebration that is important in your culture.',
                    ['What the festival is called', 'When it takes place', 'What people do during it', 'Why it is important to you'],
                    'Connect personal memories to the wider cultural significance of the event.'
                ],
                [
                    'A Historical Building or Place',
                    'Describe a historical place or building you have visited.',
                    ['Where it is located', 'What it looks like', 'What its historical significance is', 'How you felt when you visited it'],
                    'Use descriptive and evaluative language — avoid listing facts and instead share your interpretation.'
                ],
                [
                    'An Achievement You Are Proud Of',
                    'Describe something you achieved that you are proud of.',
                    ['What the achievement was', 'What challenges you faced', 'How you overcame those challenges', 'Why you are proud of it'],
                    'Emphasise the process and the obstacles — this shows depth and demonstrates a wider vocabulary.'
                ],
            ] as $i => [$topic, $prompt, $points, $tip])
            <div class="card p-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h3 class="font-semibold text-surface-100">{{ $topic }}</h3>
                    <span class="text-xs text-purple-400 font-mono shrink-0">#{{ $i + 1 }}</span>
                </div>
                <p class="text-xs text-surface-500 italic mb-3">{{ $prompt }}</p>
                <div class="bg-surface-900 rounded-lg p-3 border border-surface-700 mb-3">
                    <p class="text-xs text-surface-400 font-semibold uppercase tracking-wider mb-2">You should say:</p>
                    <ul class="space-y-1">
                        @foreach($points as $point)
                        <li class="text-xs text-surface-300 flex gap-1.5">
                            <span class="text-brand-400 mt-0.5">–</span>{{ $point }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <p class="text-xs text-surface-500 flex gap-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    {{ $tip }}
                </p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Part 3 ── --}}
    <section class="mb-14">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-rose-500/15 flex items-center justify-center shrink-0">
                <span class="text-rose-400 font-bold text-sm">P3</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-surface-50">Part 3 — Discussion Questions</h2>
                <p class="text-surface-500 text-sm">4–5 minutes · Abstract, societal themes · Extended analytical answers</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach([
                ['Role of Technology in Society', 'Do you think technology has made human relationships stronger or weaker overall?', 'Take a nuanced stance — "it depends" is fine, but you must explain the conditions clearly.'],
                ['Environmental Responsibility', 'Who is more responsible for environmental protection — governments or individuals?', 'Use hedging language: "I would argue…", "It seems to me…" — this sounds more academic.'],
                ['Education Systems', 'How important is it for education systems to teach practical life skills alongside academic subjects?', 'Compare different educational philosophies or countries if you can — it shows breadth of knowledge.'],
                ['Globalisation', 'Do you think globalisation has had a mostly positive or negative effect on local cultures?', 'Acknowledge both sides before stating your view — it demonstrates balance and critical thinking.'],
                ['Work-Life Balance', 'Do you think modern workers have a healthy work-life balance? Why or why not?', 'Use real-world trends like remote work or burnout to ground your argument in reality.'],
                ['Healthcare & Responsibility', 'Should governments fund treatments for diseases that are caused by unhealthy lifestyle choices?', 'This is an ethical question — show you can reason through competing values without being dogmatic.'],
                ['Media & Influence', 'How much influence do social media platforms have on public opinion today?', 'Use concrete examples (elections, movements) to illustrate your points — vague claims score poorly.'],
                ['Urbanisation', 'What are the most significant challenges that rapid urbanisation creates for city governments?', 'Think in categories: housing, infrastructure, inequality, environment — then prioritise one or two.'],
                ['Gender Equality', 'Do you think gender equality in the workplace has improved significantly in recent decades?', 'Show awareness of regional differences — progress varies widely across cultures and industries.'],
                ['Future of Work', 'How might automation and artificial intelligence change the nature of employment in the next 20 years?', 'Speculate confidently using future forms and conditionals — this is a chance to show grammatical range.'],
            ] as $i => [$topic, $question, $tip])
            <div class="card p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <h3 class="font-semibold text-surface-100">{{ $topic }}</h3>
                    <span class="text-xs text-rose-400 font-mono shrink-0">#{{ $i + 1 }}</span>
                </div>
                <p class="text-sm text-surface-300 mb-3 italic">"{{ $question }}"</p>
                <div class="border-t border-surface-700 pt-3">
                    <p class="text-xs text-surface-500 flex gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ $tip }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- SEO content --}}
    <div class="mt-4 space-y-4 text-surface-400 text-sm leading-relaxed">
        <h2 class="text-xl font-semibold text-surface-200">How to prepare for IELTS Speaking</h2>
        <p>The IELTS Speaking test is a face-to-face (or recorded) interview lasting 11–14 minutes. It is assessed on Fluency and Coherence, Lexical Resource, Grammatical Range and Accuracy, and Pronunciation. The most effective preparation combines regular practice speaking aloud, recording yourself to identify weak areas, and studying sample answers at Band 7+ to understand what examiners reward.</p>
        <p>For Part 2, always use your 1 minute of preparation time to make brief notes under each bullet point on the cue card. This prevents you from going blank mid-answer. For Part 3, practise giving structured answers: state your view, explain the reason, give an example, then conclude.</p>
    </div>

    {{-- CTA --}}
    <div class="mt-12 card p-8 text-center border-glow">
        <h2 class="text-2xl font-bold text-surface-50 mb-3">Practice Speaking with AI Feedback</h2>
        <p class="text-surface-400 mb-6 max-w-lg mx-auto">Record your answers to real IELTS speaking prompts and receive instant band score feedback on fluency, vocabulary, grammar, and pronunciation.</p>
        <a href="{{ route('register') }}" class="btn-primary px-8 py-3 text-base font-semibold">Try Free — Instant Feedback</a>
    </div>

</main>

</body>
</html>
