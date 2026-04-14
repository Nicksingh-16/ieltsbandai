<x-app-layout>
@php
    $totalTime    = 60 * 60; // 60 minutes
    $passage      = $meta['passage'] ?? '';
    $passageTitle = $meta['title'] ?? $question->title;
    $allQuestions = collect($meta['questions'] ?? []);
    $matchTypes   = ['matching_item','heading_match','sentence_ending','feature_match'];
@endphp

<div class="h-[calc(100vh-4rem)] bg-surface-950 flex flex-col overflow-hidden">

    {{-- Sticky Header --}}
    <header class="bg-surface-900 border-b border-surface-600 sticky top-0 z-40" x-data="{ exiting: false }">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
            {{-- Exit flow --}}
            <div class="flex items-center gap-2">
                <span x-show="!exiting" class="text-surface-300 text-sm font-medium">
                    IELTS Reading — <span class="text-surface-100">{{ ucfirst($testType) }}</span>
                </span>
                <span x-show="exiting" class="text-surface-300 text-sm" style="display:none;">
                    Exit test? Your progress will be lost.
                </span>
                <button x-show="!exiting" @click="exiting = true"
                    class="ml-2 text-xs text-surface-500 hover:text-red-400 transition-colors border border-surface-700 hover:border-red-500/50 px-2.5 py-1 rounded-lg">
                    Exit
                </button>
                <div x-show="exiting" class="flex items-center gap-1.5" style="display:none;">
                    <button @click="exiting = false"
                        class="text-xs px-3 py-1 rounded-lg border border-surface-600 text-surface-400 hover:bg-surface-700 transition-colors">
                        Stay
                    </button>
                    <a href="{{ route('dashboard') }}"
                        class="text-xs px-3 py-1 rounded-lg bg-red-600/20 border border-red-500/40 text-red-400 hover:bg-red-600/30 transition-colors">
                        Yes, Exit
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="timer" class="font-mono font-bold text-surface-50 text-base tabular-nums">60:00</span>
            </div>
        </div>
    </header>

    <div class="flex-1 max-w-7xl mx-auto w-full min-h-0">
        <div class="grid grid-cols-1 lg:grid-cols-2 h-full">

            {{-- Left: Reading Passage --}}
            <div class="lg:border-r border-surface-600 lg:overflow-y-auto lg:h-[calc(100vh-7.5rem)] no-scrollbar">
                <div class="p-6">
                    <div class="mb-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/15 text-rose-300 border border-rose-500/30 mb-2">Passage</span>
                        <h2 class="text-lg font-bold text-surface-50 leading-snug">{{ $passageTitle }}</h2>
                    </div>
                    <div class="prose prose-invert prose-sm max-w-none text-surface-300 leading-relaxed">
                        {!! nl2br(e($passage)) !!}
                    </div>
                </div>
            </div>

            {{-- Right: Questions --}}
            <div class="lg:overflow-y-auto lg:h-[calc(100vh-7.5rem)] no-scrollbar">
                <form method="POST" action="{{ route('reading.submit', $test->id) }}" id="readingForm">
                    @csrf

                    <div class="p-6 space-y-5 pb-24">
                        @php $renderedGroups = []; @endphp
                        @forelse($allQuestions as $q)
                        @php
                            $type = $q['type'] ?? 'fill';
                            $isMatch = in_array($type, $matchTypes);
                            $groupId = $isMatch ? ($q['group'] ?? 'g_' . $q['id']) : null;
                            $isFirstInGroup = $isMatch && !in_array($groupId, $renderedGroups);
                            if ($isFirstInGroup) $renderedGroups[] = $groupId;
                        @endphp

                        {{-- ── Matching types ── --}}
                        @if($isMatch)
                            @if($isFirstInGroup)
                            <div class="bg-surface-700/40 border border-surface-600 rounded-xl p-4">
                                <p class="text-sm font-semibold text-surface-200 mb-3">{{ $q['group_question'] ?? 'Match each item to the correct option.' }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($q['options'] ?? [] as $opt)
                                    <span class="px-3 py-1 rounded-lg bg-surface-600 text-xs font-medium text-surface-200">{{ $opt }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] ?? $q['stem'] ?? '' }}</p>
                                </div>
                                <div class="ml-10">
                                    <select name="answers[{{ $q['id'] }}]"
                                        class="w-full bg-surface-800 border border-surface-600 rounded-xl px-3 py-2 text-sm text-surface-200 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/40">
                                        <option value="">— select —</option>
                                        @foreach($q['options'] ?? [] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        {{-- ── True / False / Not Given ── --}}
                        @elseif($type === 'tfng')
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                </div>
                                <div class="flex gap-2 ml-10 flex-wrap">
                                    @foreach(['True','False','Not Given'] as $opt)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}" class="sr-only peer">
                                        <div class="px-4 py-2 rounded-xl border border-surface-600 text-sm font-medium text-surface-300
                                                    peer-checked:border-brand-500 peer-checked:bg-brand-500/15 peer-checked:text-brand-300
                                                    hover:border-surface-500 transition-all cursor-pointer">
                                            {{ $opt }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        {{-- ── Yes / No / Not Given ── --}}
                        @elseif($type === 'yngng')
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                </div>
                                <div class="flex gap-2 ml-10 flex-wrap">
                                    @foreach(['Yes','No','Not Given'] as $opt)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}" class="sr-only peer">
                                        <div class="px-4 py-2 rounded-xl border border-surface-600 text-sm font-medium text-surface-300
                                                    peer-checked:border-brand-500 peer-checked:bg-brand-500/15 peer-checked:text-brand-300
                                                    hover:border-surface-500 transition-all cursor-pointer">
                                            {{ $opt }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        {{-- ── MCQ single ── --}}
                        @elseif($type === 'mcq')
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                </div>
                                <div class="space-y-2 ml-10">
                                    @foreach($q['options'] ?? [] as $opt)
                                    <label class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer hover:bg-surface-700 transition-colors">
                                        <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}"
                                            class="w-4 h-4 text-brand-500 bg-surface-900 border-surface-600 focus:ring-brand-500">
                                        <span class="text-sm text-surface-300">{{ $opt }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        {{-- ── MCQ multi-select ── --}}
                        @elseif($type === 'mcq_multi')
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-1">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                </div>
                                <p class="text-xs text-amber-400 ml-10 mb-3">Choose {{ count($q['answers'] ?? []) }} answers.</p>
                                <div class="space-y-2 ml-10">
                                    @foreach($q['options'] ?? [] as $opt)
                                    <label class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer hover:bg-surface-700 transition-colors">
                                        <input type="checkbox" name="answers[{{ $q['id'] }}][]" value="{{ $opt }}"
                                            class="w-4 h-4 rounded text-brand-500 bg-surface-900 border-surface-600 focus:ring-brand-500">
                                        <span class="text-sm text-surface-300">{{ $opt }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        {{-- ── Diagram label ── --}}
                        @elseif($type === 'diagram_label')
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <div>
                                        <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                        @if(!empty($q['description']))
                                        <p class="text-xs text-surface-400 mt-1">{{ $q['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-y-2 ml-10 bg-surface-700/30 rounded-xl p-4">
                                    @foreach($q['labels'] ?? [] as $lbl)
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                        <span class="text-sm text-surface-300 flex-1">{{ $lbl['hint'] ?? '' }}</span>
                                        <input type="text" name="answers[{{ $lbl['key'] }}]"
                                            class="bg-surface-800 border border-surface-600 rounded-lg px-3 py-1.5 text-sm text-surface-200 focus:outline-none focus:border-brand-500 w-36"
                                            placeholder="Label...">
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                        {{-- ── Note/Flow/Summary completion ── --}}
                        @elseif(in_array($type, ['note_completion','flow_chart','summary_completion']))
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <div class="flex-1">
                                        @if(!empty($q['context']))
                                        <p class="text-xs text-surface-400 bg-surface-700/40 rounded-lg px-3 py-2 mb-2 leading-relaxed">{{ $q['context'] }}</p>
                                        @endif
                                        <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                    </div>
                                </div>
                                <div class="ml-10">
                                    <input type="text" name="answers[{{ $q['id'] }}]"
                                        class="input text-sm" placeholder="Write your answer...">
                                </div>
                            </div>

                        {{-- ── All other types: fill / sentence_completion / short_answer ── --}}
                        @else
                            <div class="card p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-rose-500/15 flex items-center justify-center text-xs font-bold text-rose-400 shrink-0">{{ $loop->iteration }}</span>
                                    <p class="text-sm text-surface-200 leading-relaxed">{{ $q['question'] }}</p>
                                </div>
                                <div class="ml-10">
                                    <input type="text" name="answers[{{ $q['id'] }}]"
                                        class="input text-sm" placeholder="Write your answer...">
                                </div>
                            </div>
                        @endif

                        @empty
                        <div class="card p-12 text-center">
                            <p class="text-surface-400">No questions loaded. Please refresh.</p>
                        </div>
                        @endforelse
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Fixed submit bar --}}
    <div class="fixed bottom-0 left-0 right-0 bg-surface-900/95 backdrop-blur border-t border-surface-600 z-30" x-data="{ confirming: false }">
        {{-- Question progress dots --}}
        <div class="border-b border-surface-700/50 px-4 py-1.5 hidden sm:block">
            <div class="max-w-7xl mx-auto flex items-center gap-1.5 flex-wrap">
                <span class="text-[10px] text-surface-500 mr-1">Progress:</span>
                @foreach($allQuestions as $i => $q)
                <div id="qdot-{{ $q['id'] }}"
                    class="w-5 h-5 rounded text-[9px] font-bold flex items-center justify-center transition-colors bg-surface-700 text-surface-500"
                    title="Q{{ $i + 1 }}">{{ $i + 1 }}</div>
                @endforeach
                <span id="progress-count" class="ml-auto text-xs text-surface-400">0 / {{ $allQuestions->count() }} answered</span>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <p class="text-xs text-surface-500 hidden sm:block">
                {{ $allQuestions->count() }} questions · Review all before submitting
            </p>
            {{-- Normal state --}}
            <button type="button" x-show="!confirming" @click="confirming = true"
                class="btn-primary px-8 py-3 font-bold ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Submit Answers
            </button>
            {{-- Confirm state --}}
            <div x-show="confirming" class="flex items-center gap-2 ml-auto" style="display:none;">
                <span class="text-sm text-surface-300 hidden sm:inline">Submit all answers? Cannot be undone.</span>
                <button type="button" @click="confirming = false"
                    class="px-4 py-2 rounded-lg border border-surface-600 text-surface-300 hover:bg-surface-700 text-sm font-medium transition-colors">
                    Cancel
                </button>
                <button type="submit" form="readingForm"
                    class="btn-primary px-6 py-2 font-bold text-sm">
                    Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Timer
    let time = {{ $totalTime }};
    const timerEl = document.getElementById('timer');
    setInterval(function() {
        time--;
        const m = String(Math.floor(time / 60)).padStart(2,'0');
        const s = String(time % 60).padStart(2,'0');
        timerEl.textContent = m + ':' + s;
        if (time <= 600) timerEl.className = 'font-mono font-bold text-amber-400 text-base tabular-nums';
        if (time <= 300) timerEl.className = 'font-mono font-bold text-red-400 text-base tabular-nums animate-pulse';
        if (time <= 0) document.getElementById('readingForm').submit();
    }, 1000);

    // Question progress tracker
    const form = document.getElementById('readingForm');
    const progressCount = document.getElementById('progress-count');
    const totalQ = {{ $allQuestions->count() }};

    function updateProgress() {
        let answered = 0;
        @foreach($allQuestions as $i => $q)
        (function() {
            const id = {{ $q['id'] }};
            const dot = document.getElementById('qdot-' + id);
            if (!dot) return;
            let filled = false;
            @php $qtype = $q['type'] ?? 'fill'; @endphp
            @if(($q['type'] ?? 'fill') === 'mcq_multi')
            const checks = form.querySelectorAll('input[name="answers[' + id + '][]"]:checked');
            filled = checks.length > 0;
            @elseif(in_array($q['type'] ?? 'fill', ['tfng','yngng']))
            const pill = form.querySelector('input[name="answers[' + id + ']"]:checked');
            filled = !!pill;
            @elseif(($q['type'] ?? 'fill') === 'mcq')
            const radio = form.querySelector('input[name="answers[' + id + ']"]:checked');
            filled = !!radio;
            @elseif(($q['type'] ?? 'fill') === 'diagram_label')
            @php $lblKeys = array_map(fn($l) => $l['key'], $q['labels'] ?? []); @endphp
            const diagInputs = [{{ implode(',', array_map(fn($k) => '"' . addslashes($k) . '"', $lblKeys)) }}]
                .map(k => form.querySelector('[name="answers[' + k + ']"]'))
                .filter(el => el && el.value.trim() !== '');
            filled = diagInputs.length > 0;
            @else
            const inp = form.querySelector('[name="answers[' + id + ']"]');
            filled = inp && inp.value.trim() !== '';
            @endif
            if (filled) {
                dot.className = 'w-5 h-5 rounded text-[9px] font-bold flex items-center justify-center transition-colors bg-brand-500 text-white';
                answered++;
            } else {
                dot.className = 'w-5 h-5 rounded text-[9px] font-bold flex items-center justify-center transition-colors bg-surface-700 text-surface-500';
            }
        })();
        @endforeach
        if (progressCount) progressCount.textContent = answered + ' / ' + totalQ + ' answered';
    }

    // Update on any input change
    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);
    updateProgress();
});
</script>
</x-app-layout>
