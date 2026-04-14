<x-app-layout>
@php
    $totalTime    = 40 * 60; // 30 min listen + 10 min transfer
    $allQuestions = collect($sections['questions'] ?? []);
    $sectionGroups = $allQuestions->groupBy('section');
    $sectionTitles = [
        1 => 'Section 1 — Social Conversation',
        2 => 'Section 2 — Social Monologue',
        3 => 'Section 3 — Educational Discussion',
        4 => 'Section 4 — Academic Lecture',
    ];
    $matchTypes = ['matching_item','heading_match','sentence_ending','feature_match'];
@endphp

<div class="min-h-screen bg-surface-950 flex flex-col">

    {{-- Sticky Header --}}
    <header class="bg-surface-900 border-b border-surface-600 sticky top-0 z-40" x-data="{ exiting: false }">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span x-show="!exiting" class="text-surface-300 text-sm font-medium">
                    IELTS Listening — <span class="text-surface-100">{{ ucfirst($testType) }}</span>
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
                <span id="timer" class="font-mono font-bold text-surface-50 text-base tabular-nums">40:00</span>
            </div>
        </div>
    </header>

    <div class="flex-1 max-w-6xl mx-auto w-full px-4 py-6 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Audio Player + Section Nav (left panel) --}}
            <div class="lg:col-span-2">
                <div class="sticky top-20 space-y-4">
                    <div class="card p-5">
                        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Audio</p>

                        @if(!empty($sections['audio_url']))
                        <audio id="mainAudio" controls class="w-full rounded-lg mb-3" style="filter:invert(0.85) hue-rotate(180deg);">
                            <source src="{{ $sections['audio_url'] }}" type="audio/mpeg">
                        </audio>
                        @else
                        <div class="bg-surface-700 rounded-xl p-6 text-center mb-3">
                            <svg class="w-10 h-10 text-surface-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M8.464 8.464a5 5 0 000 7.072"/>
                            </svg>
                            <p class="text-surface-400 text-sm">Audio plays here</p>
                            <p class="text-surface-600 text-xs mt-1">Listen carefully — it plays once</p>
                        </div>
                        @endif

                        <p class="text-xs text-surface-500">Listen and answer as you go. You have 10 minutes after the audio to review and transfer your answers.</p>
                    </div>

                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Sections</p>
                            <span id="listen-progress" class="text-xs text-brand-400 font-medium">0 / {{ $allQuestions->count() }}</span>
                        </div>
                        {{-- Progress bar --}}
                        <div class="w-full bg-surface-700 rounded-full h-1 mb-3">
                            <div id="listen-progress-bar" class="bg-brand-500 h-1 rounded-full transition-all duration-300" style="width:0%"></div>
                        </div>
                        <div class="space-y-2" id="sectionNav">
                            @foreach([1,2,3,4] as $s)
                            <a href="#section-{{ $s }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-surface-700 transition-colors text-sm">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 flex items-center justify-center text-xs font-bold text-amber-400 shrink-0">{{ $s }}</div>
                                <span class="text-surface-300">Section {{ $s }}</span>
                                <span id="sec-progress-{{ $s }}" class="ml-auto text-[10px] text-surface-500"></span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Questions (right panel) --}}
            <div class="lg:col-span-3">
                <form method="POST" action="{{ route('listening.submit', $test->id) }}" id="listeningForm">
                    @csrf

                    @forelse($sectionGroups as $section => $questions)
                    <div id="section-{{ $section }}" class="card mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-surface-600 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center text-sm font-bold text-amber-400 shrink-0">{{ $section }}</div>
                            <h2 class="font-semibold text-surface-100">{{ $sectionTitles[$section] ?? "Section $section" }}</h2>
                        </div>

                        <div class="p-6 space-y-6">
                            @php $renderedGroups = []; $qNum = 0; @endphp
                            @foreach($questions as $q)
                            @php
                                $type = $q['type'] ?? 'fill';
                                $qNum++;
                                $isMatch = in_array($type, $matchTypes);
                                $groupId = $isMatch ? ($q['group'] ?? 'g_' . $q['id']) : null;
                                $isFirstInGroup = $isMatch && !in_array($groupId, $renderedGroups);
                                if ($isFirstInGroup) $renderedGroups[] = $groupId;
                            @endphp

                            {{-- ── Matching types: show group header once, then individual selects ── --}}
                            @if($isMatch)
                                @if($isFirstInGroup)
                                <div class="bg-surface-700/40 border border-surface-600 rounded-xl p-4 mb-1">
                                    <p class="text-sm font-semibold text-surface-200 mb-3">{{ $q['group_question'] ?? 'Match each item to the correct option.' }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($q['options'] ?? [] as $opt)
                                        <span class="px-3 py-1 rounded-lg bg-surface-600 text-xs font-medium text-surface-200">{{ $opt }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                <div class="flex items-start gap-3">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-2.5">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm text-surface-300 mb-2">{{ $q['question'] ?? $q['stem'] ?? '' }}</p>
                                        <select name="answers[{{ $q['id'] }}]"
                                            class="w-full bg-surface-800 border border-surface-600 rounded-xl px-3 py-2 text-sm text-surface-200 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/40">
                                            <option value="">— select —</option>
                                            @foreach($q['options'] ?? [] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            {{-- ── MCQ (single correct) ── --}}
                            @elseif($type === 'mcq')
                                <div class="flex gap-4">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-3">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm text-surface-200 mb-3 leading-relaxed">{{ $q['question'] }}</p>
                                        <div class="space-y-2">
                                            @foreach($q['options'] ?? [] as $opt)
                                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-surface-700 transition-colors border border-transparent hover:border-surface-600">
                                                <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}"
                                                    class="w-4 h-4 text-brand-500 bg-surface-900 border-surface-600 focus:ring-brand-500 focus:ring-offset-surface-800">
                                                <span class="text-sm text-surface-300">{{ $opt }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            {{-- ── MCQ multi-select (choose 2+) ── --}}
                            @elseif($type === 'mcq_multi')
                                <div class="flex gap-4">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-3">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm text-surface-200 mb-1 leading-relaxed">{{ $q['question'] }}</p>
                                        <p class="text-xs text-amber-400 mb-3">Choose {{ count($q['answers'] ?? []) }} answers.</p>
                                        <div class="space-y-2">
                                            @foreach($q['options'] ?? [] as $opt)
                                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-surface-700 transition-colors border border-transparent hover:border-surface-600">
                                                <input type="checkbox" name="answers[{{ $q['id'] }}][]" value="{{ $opt }}"
                                                    class="w-4 h-4 rounded text-brand-500 bg-surface-900 border-surface-600 focus:ring-brand-500">
                                                <span class="text-sm text-surface-300">{{ $opt }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            {{-- ── Diagram label ── --}}
                            @elseif($type === 'diagram_label')
                                <div class="flex gap-4">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-3">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm text-surface-200 mb-1 leading-relaxed">{{ $q['question'] }}</p>
                                        @if(!empty($q['description']))
                                        <p class="text-xs text-surface-400 mb-3">{{ $q['description'] }}</p>
                                        @endif
                                        <div class="space-y-2 bg-surface-700/30 rounded-xl p-4">
                                            @foreach($q['labels'] ?? [] as $lbl)
                                            <div class="flex items-center gap-3">
                                                <span class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center text-xs font-bold text-amber-400 shrink-0">{{ $loop->iteration }}</span>
                                                <span class="text-sm text-surface-300 flex-1">{{ $lbl['hint'] ?? '' }}</span>
                                                <input type="text" name="answers[{{ $lbl['key'] }}]"
                                                    class="bg-surface-800 border border-surface-600 rounded-lg px-3 py-1.5 text-sm text-surface-200 focus:outline-none focus:border-brand-500 w-36"
                                                    placeholder="Label...">
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            {{-- ── Note/Flow/Summary completion with context ── --}}
                            @elseif(in_array($type, ['note_completion','flow_chart','summary_completion']))
                                <div class="flex gap-4">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-3">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        @if(!empty($q['context']))
                                        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-1">{{ ucwords(str_replace('_', ' ', $type)) }}</p>
                                        <p class="text-xs text-surface-400 bg-surface-700/40 rounded-lg px-3 py-2 mb-3 leading-relaxed">{{ $q['context'] }}</p>
                                        @endif
                                        <p class="text-sm text-surface-200 mb-2 leading-relaxed">{{ $q['question'] }}</p>
                                        <input type="text" name="answers[{{ $q['id'] }}]"
                                            class="input text-sm"
                                            placeholder="Write your answer here...">
                                    </div>
                                </div>

                            {{-- ── All fill / sentence_completion / short_answer ── --}}
                            @else
                                <div class="flex gap-4">
                                    <span class="text-xs font-bold text-surface-500 w-6 shrink-0 pt-3">{{ $qNum }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm text-surface-200 mb-3 leading-relaxed">{{ $q['question'] }}</p>
                                        <input type="text" name="answers[{{ $q['id'] }}]"
                                            class="input text-sm"
                                            placeholder="Write your answer here...">
                                    </div>
                                </div>
                            @endif

                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="card p-12 text-center">
                        <p class="text-surface-400">No questions loaded. Please refresh.</p>
                    </div>
                    @endforelse

                    <div class="card p-5 flex items-center justify-between gap-4" x-data="{ confirming: false }">
                        <p class="text-xs text-surface-500">Review all answers before submitting.</p>
                        <button type="button" x-show="!confirming" @click="confirming = true"
                            class="btn-primary px-8 font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Submit Answers
                        </button>
                        <div x-show="confirming" class="flex items-center gap-2" style="display:none;">
                            <span class="text-sm text-surface-300 hidden sm:inline">Submit all answers?</span>
                            <button type="button" @click="confirming = false"
                                class="px-4 py-2 rounded-lg border border-surface-600 text-surface-300 hover:bg-surface-700 text-sm font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit" id="submitBtn" class="btn-primary px-6 py-2 font-bold text-sm">
                                Yes, Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let time = {{ $totalTime }};
    const timerEl = document.getElementById('timer');

    const timerInterval = setInterval(function() {
        time--;
        const m = String(Math.floor(time / 60)).padStart(2, '0');
        const s = String(time % 60).padStart(2, '0');
        timerEl.textContent = m + ':' + s;

        if (time <= 600) timerEl.className = 'font-mono font-bold text-amber-400 text-base tabular-nums';
        if (time <= 300) timerEl.className = 'font-mono font-bold text-red-400 text-base tabular-nums animate-pulse';
        if (time <= 0) {
            clearInterval(timerInterval);
            document.getElementById('listeningForm').submit();
        }
    }, 1000);

    // Progress tracker
    const form = document.getElementById('listeningForm');
    const totalQ = {{ $allQuestions->count() }};
    const progressEl = document.getElementById('listen-progress');
    const progressBar = document.getElementById('listen-progress-bar');

    @php
    $sectionQCounts = [];
    foreach ($sectionGroups as $sec => $qs) {
        $sectionQCounts[$sec] = $qs->count();
    }
    @endphp
    const sectionCounts = @json($sectionQCounts);

    function updateListenProgress() {
        let answered = 0;
        const sectionAnswered = {};
        const inputs = form.querySelectorAll('input[type="text"], input[type="radio"]:checked, input[type="checkbox"]:checked, select');
        // Track by unique name to avoid double-counting radios/checkboxes
        const nameAnswered = {};
        form.querySelectorAll('input, select, textarea').forEach(el => {
            const name = el.name;
            if (!name) return;
            if (el.type === 'radio' && el.checked) nameAnswered[name] = true;
            else if (el.type === 'checkbox' && el.checked) nameAnswered[name] = true;
            else if (el.type === 'text' && el.value.trim()) nameAnswered[name] = true;
            else if (el.tagName === 'SELECT' && el.value) nameAnswered[name] = true;
        });
        answered = Object.keys(nameAnswered).length;

        // Section-level counts using DOM section elements
        for (let s = 1; s <= 4; s++) {
            const sec = document.getElementById('section-' + s);
            const sp = document.getElementById('sec-progress-' + s);
            if (!sec || !sp) continue;
            const secInputs = sec.querySelectorAll('input, select, textarea');
            const secNames = {};
            secInputs.forEach(el => {
                if (!el.name) return;
                if (el.type === 'radio' && el.checked) secNames[el.name] = true;
                else if (el.type === 'checkbox' && el.checked) secNames[el.name] = true;
                else if (el.type === 'text' && el.value.trim()) secNames[el.name] = true;
                else if (el.tagName === 'SELECT' && el.value) secNames[el.name] = true;
            });
            const secAnswered = Object.keys(secNames).length;
            const secTotal = sectionCounts[s] || 0;
            sp.textContent = secAnswered + '/' + secTotal;
            sp.className = 'ml-auto text-[10px] ' + (secAnswered >= secTotal && secTotal > 0 ? 'text-brand-400 font-medium' : 'text-surface-500');
        }

        if (progressEl) progressEl.textContent = answered + ' / ' + totalQ;
        if (progressBar) progressBar.style.width = (totalQ > 0 ? (answered / totalQ * 100) : 0) + '%';
    }

    form.addEventListener('input', updateListenProgress);
    form.addEventListener('change', updateListenProgress);
    updateListenProgress();
});
</script>
</x-app-layout>
