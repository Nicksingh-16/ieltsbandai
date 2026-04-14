<x-app-layout>
@php
    $allQuestions = collect($sections['questions'] ?? []);
    $correct      = $result['correct'] ?? 0;
    $total        = $result['total'] ?? 40;
    $pct          = $result['percentage'] ?? 0;
    $band         = $test->overall_band ?? $test->score ?? 0;
    $matchTypes   = ['matching_item','heading_match','sentence_ending','feature_match'];
@endphp

<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}" class="btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Dashboard
            </a>
            <span class="tag-amber">Listening Complete</span>
        </div>

        {{-- Score hero --}}
        <div class="card border-glow p-8 mb-8 text-center">
            <p class="text-surface-400 text-sm uppercase tracking-wider mb-4">Your Result</p>
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-amber-500 to-amber-700 text-white text-4xl font-bold mb-4 shadow-glow">
                {{ number_format($band, 1) }}
            </div>
            <p class="text-surface-50 text-xl font-bold mb-2">Band {{ number_format($band, 1) }}</p>
            <p class="text-surface-400 text-sm">{{ $correct }} / {{ $total }} correct ({{ $pct }}%)</p>

            <div class="max-w-sm mx-auto mt-6">
                <div class="criterion-bar h-3">
                    <div class="criterion-bar-fill" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>

        {{-- Band interpretation --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">Band {{ number_format($band, 1) }} — What it means</h2>
            @php
                $interpretations = [
                    9.0 => ['Expert user', 'text-emerald-400'],
                    8.0 => ['Very good user', 'text-emerald-400'],
                    7.0 => ['Good user', 'text-brand-400'],
                    6.0 => ['Competent user', 'text-brand-400'],
                    5.0 => ['Modest user', 'text-amber-400'],
                    4.0 => ['Limited user', 'text-red-400'],
                ];
                $interp = 'Limited user'; $interpColor = 'text-red-400';
                foreach ($interpretations as $threshold => $data) {
                    if ($band >= $threshold) { $interp = $data[0]; $interpColor = $data[1]; break; }
                }
            @endphp
            <p class="{{ $interpColor }} font-semibold text-lg">{{ $interp }}</p>
            <p class="text-surface-400 text-sm mt-2">
                @if($band >= 7)
                    Excellent comprehension. You can understand complex listening passages with ease.
                @elseif($band >= 6)
                    Good comprehension. You understand most main ideas and specific details.
                @elseif($band >= 5)
                    Moderate comprehension. Work on understanding detail and inference questions.
                @else
                    Keep practicing. Focus on listening for specific information and main ideas.
                @endif
            </p>
        </div>

        {{-- Answer review --}}
        <div class="card overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-surface-600">
                <h2 class="section-title">Answer Review</h2>
            </div>
            <div class="divide-y divide-surface-600">
                @forelse($allQuestions as $q)
                @php
                    $type = $q['type'] ?? 'fill';
                    $qNum = $loop->iteration;
                @endphp

                {{-- ── Diagram label: one row per label ── --}}
                @if($type === 'diagram_label')
                    @foreach($q['labels'] ?? [] as $lbl)
                    @php
                        $given     = $answers[$lbl['key']] ?? '';
                        $isCorrect = strtolower(trim($given)) === strtolower(trim($lbl['answer'] ?? ''));
                    @endphp
                    <div class="px-6 py-4 flex items-start gap-4">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $isCorrect ? 'bg-emerald-500/15' : 'bg-red-500/15' }}">
                            @if($isCorrect)
                                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-surface-200 mb-1">{{ $lbl['hint'] ?? 'Label' }}</p>
                            <div class="flex flex-wrap gap-3 text-xs">
                                <span class="{{ $isCorrect ? 'text-emerald-400' : 'text-red-400' }}">Your answer: <strong>{{ $given ?: '(blank)' }}</strong></span>
                                @if(!$isCorrect)<span class="text-emerald-400">Correct: <strong>{{ $lbl['answer'] }}</strong></span>@endif
                            </div>
                        </div>
                        <span class="text-xs text-surface-500 shrink-0">Q{{ $qNum }}</span>
                    </div>
                    @endforeach

                {{-- ── MCQ multi: compare arrays ── --}}
                @elseif($type === 'mcq_multi')
                @php
                    $raw      = $answers[$q['id']] ?? [];
                    $selected = is_array($raw) ? $raw : [$raw];
                    $expected = $q['answers'] ?? [];
                    $selLower = array_map('strtolower', array_map('trim', $selected));
                    $expLower = array_map('strtolower', array_map('trim', $expected));
                    sort($selLower); sort($expLower);
                    $isCorrect = $selLower === $expLower;
                @endphp
                <div class="px-6 py-4 flex items-start gap-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $isCorrect ? 'bg-emerald-500/15' : 'bg-red-500/15' }}">
                        @if($isCorrect)
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-surface-200 mb-1">{{ $q['question'] }}</p>
                        <div class="flex flex-wrap gap-3 text-xs">
                            <span class="{{ $isCorrect ? 'text-emerald-400' : 'text-red-400' }}">Your answer: <strong>{{ implode(', ', $selected) ?: '(blank)' }}</strong></span>
                            @if(!$isCorrect)<span class="text-emerald-400">Correct: <strong>{{ implode(', ', $expected) }}</strong></span>@endif
                        </div>
                    </div>
                    <span class="text-xs text-surface-500 shrink-0">Q{{ $qNum }}</span>
                </div>

                {{-- ── All other types ── --}}
                @else
                @php
                    $given     = $answers[$q['id']] ?? '';
                    $isCorrect = strtolower(trim($given)) === strtolower(trim($q['answer'] ?? ''));
                @endphp
                <div class="px-6 py-4 flex items-start gap-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $isCorrect ? 'bg-emerald-500/15' : 'bg-red-500/15' }}">
                        @if($isCorrect)
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-surface-200 mb-1">{{ $q['question'] }}</p>
                        <div class="flex flex-wrap gap-3 text-xs">
                            <span class="{{ $isCorrect ? 'text-emerald-400' : 'text-red-400' }}">Your answer: <strong>{{ $given ?: '(blank)' }}</strong></span>
                            @if(!$isCorrect)<span class="text-emerald-400">Correct: <strong>{{ $q['answer'] }}</strong></span>@endif
                        </div>
                    </div>
                    <span class="text-xs text-surface-500 shrink-0">Q{{ $qNum }}</span>
                </div>
                @endif

                @empty
                <div class="p-8 text-center text-surface-500 text-sm">No questions found.</div>
                @endforelse
            </div>
        </div>

        {{-- Mock test continuation --}}
        @if(session('mock_test_id'))
        <div class="card p-5 border border-brand-500/30 bg-brand-500/5 mb-4">
            <p class="font-semibold text-brand-400 text-sm mb-3">✅ Listening complete — continue your mock test</p>
            <form method="POST" action="{{ route('mock-test.advance', ['mock' => session('mock_test_id'), 'module' => 'listening']) }}">
                @csrf
                <input type="hidden" name="test_id" value="{{ $test->id }}">
                <button type="submit" class="btn-primary w-full justify-center py-2.5">Continue to Reading →</button>
            </form>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('listening.index') }}" class="btn-primary flex-1 justify-center py-3">Practice Again</a>
            <a href="{{ route('dashboard') }}" class="btn-secondary flex-1 justify-center py-3">Back to Dashboard</a>
        </div>

    </div>
</div>
</x-app-layout>
