<x-app-layout>
@php
    $band      = $scores['overall_band'] ?? 0;
    $fluency   = $scores['fluency_coherence'] ?? 0;
    $lexical   = $scores['lexical_resource'] ?? 0;
    $grammar   = $scores['grammatical_range_accuracy'] ?? 0;
    $pronun    = $scores['pronunciation'] ?? 0;

    $criteria = [
        ['key' => 'fluency_coherence',          'label' => 'Fluency & Coherence',        'score' => $fluency,  'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ['key' => 'lexical_resource',           'label' => 'Lexical Resource',           'score' => $lexical,  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['key' => 'grammatical_range_accuracy', 'label' => 'Grammar Range & Accuracy',   'score' => $grammar,  'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['key' => 'pronunciation',              'label' => 'Pronunciation',               'score' => $pronun,   'icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
    ];

    $status = $test->status ?? 'processing';

    $bandColor = match(true) {
        $band >= 8   => 'from-emerald-500 to-emerald-700',
        $band >= 7   => 'from-brand-500 to-brand-700',
        $band >= 6   => 'from-brand-400 to-cyan-600',
        $band >= 5   => 'from-amber-500 to-amber-700',
        default      => 'from-red-500 to-red-700',
    };

    $interp = match(true) {
        $band >= 9   => ['Expert user',     'text-emerald-400'],
        $band >= 8   => ['Very good user',  'text-emerald-400'],
        $band >= 7   => ['Good user',       'text-brand-400'],
        $band >= 6   => ['Competent user',  'text-brand-400'],
        $band >= 5   => ['Modest user',     'text-amber-400'],
        $band >= 4   => ['Limited user',    'text-red-400'],
        default      => ['Beginner',        'text-red-400'],
    };

    $feedback = $test->feedback ?? '';
    $audioFiles = $test->audioFiles ?? collect();
    $testQuestions = $test->testQuestions ?? collect();
@endphp

<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}" class="btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
            <span class="tag-cyan">Speaking Complete</span>
        </div>

        {{-- Still processing --}}
        @if(in_array($status, ['processing', 'scoring']))
        <div class="card p-10 text-center mb-6" id="processing-card">
            <div class="w-20 h-20 rounded-full bg-brand-500/15 border border-brand-500/30 flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-surface-50 mb-2">AI Examiner is evaluating your responses</h2>
            <p class="text-surface-400 text-sm mb-2">Transcribing audio and scoring all four IELTS criteria...</p>
            <p class="text-surface-600 text-xs mb-6">This takes 1–3 minutes. This page will refresh automatically.</p>

            <div class="max-w-xs mx-auto mb-6">
                <div class="flex justify-between text-xs text-surface-500 mb-1.5">
                    <span>Evaluating</span>
                    <span id="progress-pct">0%</span>
                </div>
                <div class="w-full bg-surface-700 rounded-full h-2">
                    <div id="progress-fill" class="bg-gradient-to-r from-brand-500 to-brand-400 h-2 rounded-full transition-all duration-1000" style="width:0%"></div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="location.reload()" class="btn-secondary px-6 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh now
                </button>
                <a href="{{ route('dashboard') }}" class="btn-ghost px-6 text-sm">Check later from Dashboard</a>
            </div>
        </div>
        <script>
        const fill  = document.getElementById('progress-fill');
        const label = document.getElementById('progress-pct');
        const statusUrl = '{{ route("test.status", $test->id) }}';

        // Steps shown in the status line
        const stepMessages = [
            'Uploading audio files...',
            'Transcribing Part 1...',
            'Transcribing Part 2...',
            'Transcribing Part 3...',
            'AI examiner is scoring your responses...',
            'Finalising your band score...',
        ];
        let stepIdx = 0;
        const statusLine = document.querySelector('#processing-card p:nth-child(3)');

        async function poll() {
            try {
                const res  = await fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();

                if (data.status === 'completed' || data.status === 'failed') {
                    location.reload();
                    return;
                }

                // Real progress: each transcribed part = ~25%, scoring = last 25%
                const transcribed = data.transcribed ?? 0;
                const total       = Math.max(data.total_audio ?? 3, 1);
                const realPct     = data.status === 'scoring'
                    ? 80 + Math.min(stepIdx * 3, 15)
                    : Math.round((transcribed / total) * 75);

                fill.style.width  = realPct + '%';
                label.textContent = realPct + '%';

                // Cycle through step messages
                stepIdx = data.status === 'scoring' ? 4 : Math.min(transcribed + 1, 3);
                if (statusLine) statusLine.textContent = stepMessages[stepIdx] ?? stepMessages[4];

            } catch (e) {
                // Network error — keep polling
            }
            setTimeout(poll, 3500);
        }

        poll(); // start immediately
        </script>

        @elseif($status === 'failed')
        <div class="card border border-red-500/30 p-10 text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-red-500/15 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-surface-50 mb-2">Evaluation failed</h2>
            <p class="text-surface-400 text-sm mb-6">{{ $feedback ?: 'Something went wrong during evaluation. Please try again.' }}</p>
            <a href="{{ route('speaking.test') }}" class="btn-primary px-6">Try Again</a>
        </div>

        @else
        {{-- ── Score Hero ────────────────────────────────────────────────── --}}
        <div class="card border-glow p-8 mb-6 text-center">
            <p class="text-surface-400 text-xs uppercase tracking-widest mb-4">Overall Band Score</p>
            <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gradient-to-br {{ $bandColor }} text-white text-5xl font-bold mb-4 shadow-glow">
                {{ number_format($band, 1) }}
            </div>
            <p class="text-surface-50 text-2xl font-bold mb-1">Band {{ number_format($band, 1) }}</p>
            <p class="{{ $interp[1] }} font-semibold mb-4">{{ $interp[0] }}</p>

            @if($confidenceRange)
            <p class="text-surface-500 text-xs mb-4">Estimated range: {{ $confidenceRange }}</p>
            @endif

            {{-- mini score bar --}}
            @php $pct = min(100, ($band / 9) * 100); @endphp
            <div class="max-w-xs mx-auto">
                <div class="criterion-bar h-2.5">
                    <div class="criterion-bar-fill" style="width:{{ $pct }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-surface-600 mt-1">
                    <span>0</span><span>4.5</span><span>9</span>
                </div>
            </div>
        </div>

        {{-- ── Four Criteria ─────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            @foreach($criteria as $c)
            @php
                $s   = $c['score'];
                $pct = min(100, ($s / 9) * 100);
                $color = match(true) {
                    $s >= 7 => ['bg-emerald-500/15','text-emerald-400','bg-emerald-400'],
                    $s >= 6 => ['bg-brand-500/15','text-brand-400','bg-brand-400'],
                    $s >= 5 => ['bg-amber-500/15','text-amber-400','bg-amber-400'],
                    default => ['bg-red-500/15','text-red-400','bg-red-400'],
                };
            @endphp
            <div class="card p-5 text-center">
                <div class="w-10 h-10 rounded-xl {{ $color[0] }} flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 {{ $color[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $c['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-xs text-surface-400 mb-2 leading-tight">{{ $c['label'] }}</p>
                <p class="{{ $color[1] }} text-2xl font-bold">{{ $s > 0 ? number_format($s, 1) : '—' }}</p>
                @if($s > 0)
                <div class="criterion-bar mt-2 h-1.5">
                    <div class="h-full rounded-full {{ $color[2] }}" style="width:{{ $pct }}%"></div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- ── Examiner Feedback ─────────────────────────────────────────── --}}
        @if($feedback || count($examinerComments))
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">
                <svg class="w-4 h-4 text-brand-400 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Examiner Feedback
            </h2>

            @if($feedback)
            <p class="text-surface-300 text-sm leading-relaxed mb-4">{{ $feedback }}</p>
            @endif

            @if(count($examinerComments) > 1)
            <div class="space-y-2 mt-3">
                @foreach(array_slice($examinerComments, 1) as $comment)
                <div class="flex items-start gap-2 text-sm text-surface-400">
                    <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $comment }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- ── Part-by-part Transcripts ──────────────────────────────────── --}}
        @if($audioFiles->count() > 0 || $testQuestions->count() > 0)
        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-surface-600">
                <h2 class="section-title">Your Responses — Part by Part</h2>
            </div>
            <div class="divide-y divide-surface-700">
                @foreach($testQuestions->sortBy('part') as $tq)
                @php
                    $audioFile = $audioFiles->get($loop->index);
                    $transcript = $audioFile?->transcript ?? null;
                    $partLabel = ['Part 1 — Personal Questions','Part 2 — Long Turn','Part 3 — Discussion'][$tq->part - 1] ?? "Part {$tq->part}";
                @endphp
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center text-xs font-bold text-brand-400 shrink-0">{{ $tq->part }}</div>
                        <div>
                            <p class="text-xs text-surface-500 font-medium">{{ $partLabel }}</p>
                            <p class="text-sm font-semibold text-surface-200">{{ $tq->question->title ?? "Part {$tq->part} Question" }}</p>
                        </div>
                    </div>

                    @if($audioFile && Storage::disk('public')->exists($audioFile->file_url))
                    <audio controls class="w-full rounded-xl mb-3" style="filter:invert(0.85) hue-rotate(180deg);">
                        <source src="{{ Storage::url($audioFile->file_url) }}" type="audio/webm">
                    </audio>
                    @endif

                    @if($transcript)
                    <div class="bg-surface-700/40 rounded-xl p-4">
                        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-2">Transcript</p>
                        <p class="text-sm text-surface-300 leading-relaxed">{{ $transcript }}</p>
                    </div>
                    @else
                    <p class="text-xs text-surface-600 italic">Transcript not available.</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Speech Analysis ───────────────────────────────────────────── --}}
        @if(!empty($fillerAnalysis) || !empty($repetitionData))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

            {{-- Filler words --}}
            @if(!empty($fillerAnalysis))
            <div class="card p-5">
                <h3 class="font-semibold text-surface-100 text-sm mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Filler Words
                </h3>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-3xl font-bold {{ ($fillerAnalysis['total'] ?? 0) > 10 ? 'text-red-400' : 'text-amber-400' }}">{{ $fillerAnalysis['total'] ?? 0 }}</span>
                    <span class="text-surface-500 text-sm">detected</span>
                    <span class="ml-auto text-xs text-surface-500">{{ $fillerAnalysis['density'] ?? 0 }}% density</span>
                </div>
                @if(!empty($fillerAnalysis['breakdown']))
                <div class="space-y-1.5">
                    @foreach($fillerAnalysis['breakdown'] as $word => $count)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-surface-400">"{{ $word }}"</span>
                        <span class="font-medium {{ $count > 5 ? 'text-red-400' : 'text-amber-400' }}">×{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                <p class="text-xs text-surface-600 mt-3">
                    @if(($fillerAnalysis['total'] ?? 0) <= 5) Great control of hesitation language.
                    @elseif(($fillerAnalysis['total'] ?? 0) <= 10) Moderate filler use — try to reduce hesitation words.
                    @else High filler frequency — focus on pause strategies instead of filler words.
                    @endif
                </p>
            </div>
            @endif

            {{-- Vocabulary range --}}
            @if(!empty($repetitionData))
            <div class="card p-5">
                <h3 class="font-semibold text-surface-100 text-sm mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Vocabulary Range
                </h3>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-3xl font-bold text-brand-400">{{ $repetitionData['unique_words'] ?? 0 }}</span>
                    <span class="text-surface-500 text-sm">unique words</span>
                </div>
                @if(!empty($repetitionData['overused_words']))
                <p class="text-xs text-surface-400 mb-2">Most repeated content words:</p>
                <div class="space-y-1.5">
                    @foreach($repetitionData['overused_words'] as $word => $count)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-surface-400">"{{ $word }}"</span>
                        <span class="font-medium text-amber-400">×{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-emerald-400 mt-2">Good vocabulary variety — no significant over-repetition detected.</p>
                @endif
            </div>
            @endif

        </div>
        @endif

        {{-- ── What to Improve ──────────────────────────────────────────── --}}
        @php
        $tips = [
            'fluency_coherence' => [
                'label' => 'Fluency & Coherence',
                'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'tips'  => [
                    9   => 'Outstanding fluency. Keep practicing extended discourse on complex topics.',
                    7   => 'Good fluency. Work on eliminating remaining hesitation and developing ideas more naturally without reformulation.',
                    6   => 'Use discourse markers (however, furthermore, on the other hand) to connect ideas smoothly. Practise speaking for 2 minutes non-stop on everyday topics.',
                    5   => 'Focus on speaking continuously without long pauses. Record yourself daily for 1 minute and listen back. Replace filler words (um, uh) with a silent pause.',
                    0   => 'Build basic fluency by repeating simple sentences. Shadow native speakers — listen and repeat phrase by phrase. Aim for short, clear sentences first.',
                ],
            ],
            'lexical_resource' => [
                'label' => 'Lexical Resource',
                'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'tips'  => [
                    9   => 'Excellent vocabulary range. Aim for idiomatic precision and register flexibility.',
                    7   => 'Strong vocabulary. Expand collocations and topic-specific word families (e.g., environment: carbon footprint, biodiversity, sustainability).',
                    6   => 'Learn 5 new collocations per day from IELTS topic lists. Avoid repeating the same adjectives (good/bad/nice) — replace them with precise alternatives.',
                    5   => 'Build a core vocabulary bank of 300 high-frequency IELTS words. Group words by topic (technology, health, environment). Use them in practice sentences.',
                    0   => 'Start with IELTS vocabulary lists (Cambridge IELTS books). Learn 3 new words daily. Focus on using them in sentences, not just memorising definitions.',
                ],
            ],
            'grammatical_range_accuracy' => [
                'label' => 'Grammar Range & Accuracy',
                'icon'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'tips'  => [
                    9   => 'Near-perfect grammar. Maintain and explore more complex clause embedding.',
                    7   => 'Good grammar. Focus on consistent use of conditionals (if I were…) and perfect tenses in natural speech.',
                    6   => 'Mix simple and complex sentences. Use relative clauses (which, who, that) and passive voice naturally. Check subject-verb agreement.',
                    5   => 'Practise 3 key structures daily: present perfect, conditionals, and passive voice. Record 5 sentences using each structure.',
                    0   => 'Review basic tenses (present, past, future). Practise short grammatically correct sentences before attempting complex structures.',
                ],
            ],
            'pronunciation' => [
                'label' => 'Pronunciation',
                'icon'  => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
                'tips'  => [
                    9   => 'Excellent pronunciation. Your speech is completely clear and natural.',
                    7   => 'Clear pronunciation. Work on sentence stress and intonation to sound even more natural.',
                    6   => 'Focus on word stress patterns (pho-TO-graph vs PHO-to-gra-phy). Use Cambridge dictionary audio to check individual words. Practise linking words in phrases.',
                    5   => 'Use minimal pairs practice (ship/sheep, live/leave). Record yourself and compare with native speaker recordings. Work on final consonant sounds.',
                    0   => 'Focus on the 44 English phonemes. Use BBC Pronunciation resources. Practise vowel sounds which cause most confusion for your first language.',
                ],
            ],
        ];

        $getTip = function(array $tipMap, float $score): string {
            foreach ($tipMap as $threshold => $tip) {
                if ($score >= $threshold) return $tip;
            }
            return end($tipMap);
        };
        @endphp

        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-surface-600">
                <h2 class="section-title">How to Improve — Per Criterion</h2>
            </div>
            <div class="divide-y divide-surface-700">
                @foreach($tips as $key => $info)
                @php
                    $score   = $scores[$key] ?? 0;
                    $tip     = $getTip($info['tips'], $score);
                    $tagColor = match(true) {
                        $score >= 7  => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                        $score >= 6  => 'bg-brand-500/15 text-brand-400 border-brand-500/30',
                        $score >= 5  => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                        default      => 'bg-red-500/15 text-red-400 border-red-500/30',
                    };
                @endphp
                <div class="p-5 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-xl bg-surface-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $info['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1.5">
                            <p class="text-sm font-semibold text-surface-100">{{ $info['label'] }}</p>
                            @if($score > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $tagColor }}">
                                {{ number_format($score, 1) }}
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-surface-400 leading-relaxed">{{ $tip }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Band descriptor ──────────────────────────────────────────── --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-3">What Band {{ number_format($band, 1) }} means for Speaking</h2>
            <p class="text-surface-300 text-sm leading-relaxed">
                @if($band >= 8)
                    You speak fluently with only rare hesitation. Vocabulary is sophisticated and precise. Grammar is consistently accurate. Pronunciation is clear and easy to understand throughout.
                @elseif($band >= 7)
                    You communicate effectively with some hesitation. Good vocabulary range with minor errors. Grammar is mostly accurate. Pronunciation is generally clear.
                @elseif($band >= 6)
                    You can speak at length with some difficulty. Vocabulary is adequate but repetitive at times. Some grammatical errors that do not impede communication. Pronunciation is generally understandable.
                @elseif($band >= 5)
                    You speak with noticeable pauses and limited vocabulary. Some grammar errors affect clarity. Pronunciation requires effort from the listener at times.
                @else
                    Significant difficulty maintaining a conversation. Focus on fluency, vocabulary building, and pronunciation practice.
                @endif
            </p>
        </div>
        @endif

        {{-- Mock test continuation (speaking) --}}
        @if(session('mock_test_id') && $status === 'completed')
        <div class="card p-5 border border-emerald-500/30 bg-emerald-500/5 mb-4">
            <p class="font-semibold text-emerald-400 text-sm mb-3">✅ Speaking complete — finish your mock test</p>
            <form method="POST" action="{{ route('mock-test.advance', ['mock' => session('mock_test_id'), 'module' => 'speaking']) }}">
                @csrf
                <input type="hidden" name="test_id" value="{{ $test->id }}">
                <button type="submit" class="btn-primary w-full justify-center py-2.5">View Mock Test Results →</button>
            </form>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('speaking.test') }}" class="btn-primary flex-1 justify-center py-3">Practice Again</a>
            @if($status === 'completed')
            <a href="{{ route('study-plan.show', $test) }}" class="btn-secondary flex-1 justify-center py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                4-Week Study Plan
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn-secondary flex-1 justify-center py-3">Back to Dashboard</a>
        </div>

    </div>
</div>
</x-app-layout>
