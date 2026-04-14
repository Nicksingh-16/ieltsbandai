<x-app-layout>
@php
    $minWords  = $question->min_words ?? ($task === 'task1' ? 150 : 250);
    $totalTime = $question->time_limit ?? ($task === 'task1' ? 1200 : 2400);
@endphp

<div class="min-h-screen bg-surface-950 flex flex-col">

    {{-- ── Sticky Test Header ── --}}
    <header class="bg-surface-900 border-b border-surface-600 sticky top-0 z-40 print:hidden" x-data="{ exiting: false }">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button x-show="!exiting" @click="exiting = true" class="btn-ghost p-1.5 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span x-show="!exiting" class="text-surface-300 text-sm font-medium truncate">
                    IELTS Writing — <span class="text-surface-100">{{ ucfirst($testType) }} Task {{ substr($task, -1) }}</span>
                </span>
                {{-- Exit confirm --}}
                <div x-show="exiting" class="flex items-center gap-2" style="display:none;">
                    <span class="text-surface-400 text-sm">Exit? Your draft will be saved.</span>
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

            {{-- Timer --}}
            <div class="flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="timer" class="font-mono font-bold text-surface-50 text-base tabular-nums">
                    {{ gmdate('i:s', $totalTime) }}
                </span>
            </div>
        </div>
    </header>

    {{-- ── Main Content ── --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-4 py-5 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            {{-- ── Left: Question + Editor ── --}}
            <div class="lg:col-span-8 flex flex-col gap-4">

                {{-- Question card --}}
                <div class="card p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-surface-500 uppercase tracking-wider mb-0.5">Question</p>
                            <h2 class="text-surface-100 font-semibold leading-snug">{{ $question->title }}</h2>
                        </div>
                    </div>

                    <div class="text-surface-300 leading-relaxed whitespace-pre-line text-sm border-t border-surface-600 pt-4">
                        {{ $question->content }}
                    </div>

                    @php
                        $meta = is_string($question->metadata ?? null)
                            ? json_decode($question->metadata, true)
                            : ($question->metadata ?? []);
                        $chartType = $meta['chart_type'] ?? null;
                    @endphp

                    {{-- Chart / Process Diagram for Task 1 Academic --}}
                    @if($task === 'task1' && $testType === 'academic' && $chartType)
                        <div class="mt-5 bg-surface-900 border border-surface-600 rounded-xl p-4">
                            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider mb-3">
                                {{ $meta['chart_title'] ?? 'Figure' }}
                            </p>

                            @if($chartType === 'process')
                                {{-- Process Diagram --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach(($meta['steps'] ?? []) as $i => $step)
                                        <div class="flex items-center gap-2">
                                            <div class="flex flex-col items-center text-center w-20">
                                                <div class="w-12 h-12 rounded-full bg-surface-700 border-2 border-brand-500/40 flex items-center justify-center text-xl mb-1">
                                                    {{ $step['icon'] ?? '•' }}
                                                </div>
                                                <span class="text-[10px] font-semibold text-surface-300 leading-tight">{{ $step['label'] }}</span>
                                                <span class="text-[9px] text-surface-500 leading-tight mt-0.5">{{ $step['detail'] }}</span>
                                            </div>
                                            @if(!$loop->last)
                                                <svg class="w-4 h-4 text-brand-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($chartType === 'pie')
                                {{-- Pie Chart via Chart.js --}}
                                <div class="flex flex-col sm:flex-row items-center gap-6">
                                    <div class="relative" style="width:220px;height:220px;">
                                        <canvas id="taskChart"></canvas>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(($meta['labels'] ?? []) as $i => $label)
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <span class="w-3 h-3 rounded-sm inline-block" style="background:{{ ['#06b6d4','#f97316','#8b5cf6','#22c55e','#f59e0b','#ef4444'][$i % 6] }}"></span>
                                                <span class="text-surface-300">{{ $label }}: {{ ($meta['datasets'][0]['data'][$i] ?? 0) }}%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Line / Bar Chart via Chart.js --}}
                                <div style="height:220px;">
                                    <canvas id="taskChart"></canvas>
                                </div>
                            @endif

                            @if(!empty($meta['key_features']))
                                <p class="text-xs text-amber-400/80 mt-3 border-t border-surface-600 pt-3">
                                    <span class="font-semibold text-amber-400">Key features to cover:</span> {{ $meta['key_features'] }}
                                </p>
                            @endif
                        </div>

                        @if($chartType !== 'process')
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('taskChart').getContext('2d');
                            const chartType = '{{ $chartType }}';
                            const meta = @json($meta);
                            const pieColors = ['#06b6d4','#f97316','#8b5cf6','#22c55e','#f59e0b','#ef4444'];

                            const datasets = meta.datasets.map((ds, i) => ({
                                label: ds.label,
                                data: ds.data,
                                backgroundColor: chartType === 'pie'
                                    ? pieColors.slice(0, ds.data.length)
                                    : (ds.color + '33'),
                                borderColor: chartType === 'pie' ? pieColors.slice(0, ds.data.length) : ds.color,
                                borderWidth: chartType === 'bar' ? 0 : 2,
                                fill: chartType === 'line',
                                tension: 0.4,
                                pointRadius: chartType === 'line' ? 4 : 0,
                                pointBackgroundColor: ds.color,
                            }));

                            new Chart(ctx, {
                                type: chartType === 'bar' ? 'bar' : chartType,
                                data: { labels: meta.labels, datasets },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { labels: { color: '#94a3b8', font: { size: 11 } } },
                                        tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#94a3b8' }
                                    },
                                    scales: chartType !== 'pie' ? {
                                        x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#1e293b' } },
                                        y: {
                                            ticks: { color: '#64748b', font: { size: 10 } },
                                            grid: { color: '#1e293b' },
                                            title: { display: !!meta.y_label, text: meta.y_label, color: '#64748b', font: { size: 10 } }
                                        }
                                    } : {}
                                }
                            });
                        });
                        </script>
                        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
                        @endif
                    @endif

                    <div class="mt-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-amber-400 font-medium">Write at least {{ $minWords }} words.</p>
                    </div>
                </div>

                {{-- Writing area --}}
                <form method="POST" action="{{ route('writing.submit', $test->id) }}" id="writingForm" class="flex flex-col gap-3">
                    @csrf

                    {{-- Word count bar --}}
                    <div class="flex items-center justify-between text-xs px-1">
                        <span class="text-surface-400">
                            Words: <strong id="wordCount" class="text-surface-200">0</strong>
                        </span>
                        <span id="minWordsStatus" class="text-red-400 font-medium">
                            Need {{ $minWords }} min
                        </span>
                    </div>

                    {{-- Textarea --}}
                    <div class="card overflow-hidden">
                        <textarea
                            name="answer"
                            id="essayEditor"
                            required
                            class="w-full h-[480px] lg:h-[560px] bg-surface-800 text-surface-100 text-sm leading-relaxed p-5 resize-none focus:outline-none placeholder-surface-600 font-mono"
                            placeholder="Start writing your answer here...&#10;&#10;Tip: Write a clear introduction, body paragraphs, and a conclusion."
                        ></textarea>
                    </div>

                    @error('answer')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            {{-- ── Right: Live Analyzer ── --}}
            <div class="lg:col-span-4">
                <div class="sticky top-20 space-y-4">

                    {{-- Live Stats --}}
                    <div class="card p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-brand-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span class="text-sm font-semibold text-surface-200">Live Analysis</span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-surface-600">
                                <span class="text-xs text-surface-400">Unique Words</span>
                                <span id="uniqueWords" class="text-sm font-bold text-brand-400">0</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-surface-600">
                                <span class="text-xs text-surface-400">Avg Sentence</span>
                                <span id="avgSentence" class="text-sm font-bold text-brand-400">0</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-xs text-surface-400">Complex Sentences</span>
                                <span id="complexSentences" class="text-sm font-bold text-brand-400">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Vocabulary Level --}}
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-semibold text-surface-200">Vocabulary</span>
                            <span id="vocabStatus" class="text-xs text-surface-500">Type to analyze</span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-surface-400">Basic</span>
                                    <span id="basicPercent" class="text-surface-300">0%</span>
                                </div>
                                <div class="criterion-bar">
                                    <div id="basicBar" class="h-full rounded-full bg-red-500 transition-all duration-500" style="width:0%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-surface-400">Intermediate</span>
                                    <span id="intermediatePercent" class="text-surface-300">0%</span>
                                </div>
                                <div class="criterion-bar">
                                    <div id="intermediateBar" class="h-full rounded-full bg-amber-500 transition-all duration-500" style="width:0%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-surface-400">Advanced</span>
                                    <span id="advancedPercent" class="text-surface-300">0%</span>
                                </div>
                                <div class="criterion-bar">
                                    <div id="advancedBar" class="criterion-bar-fill" style="width:0%"></div>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-surface-500 mt-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            Aim for 40%+ advanced for Band 7+
                        </p>
                    </div>

                    {{-- Criteria reminder --}}
                    <div class="card p-5">
                        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Scored on 4 criteria</p>
                        <div class="space-y-2">
                            @foreach(['Task Achievement', 'Coherence & Cohesion', 'Lexical Resource', 'Grammar'] as $c)
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-brand-500"></div>
                                <span class="text-xs text-surface-300">{{ $c }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Fixed Submit Bar ── --}}
    <div class="fixed bottom-0 left-0 right-0 bg-surface-900/95 backdrop-blur border-t border-surface-600 z-30 print:hidden">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <p class="text-xs text-surface-500 hidden sm:block">
                <span id="autosaveLabel">Auto-saved every 60s</span> · Review before submitting
            </p>
            <button
                id="submitBtn"
                type="submit"
                form="writingForm"
                disabled
                class="btn-primary px-8 py-3 font-bold disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none ml-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Submit Answer
            </button>
        </div>
    </div>
</div>

{{-- ── Validation Modal (injected by JS) ── --}}
<style>
@keyframes scale-in {
    from { opacity:0; transform:scale(0.95) translateY(8px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.animate-scale-in { animation: scale-in 0.2s ease-out; }
</style>

<script>
let validationPassed = false;

document.getElementById('writingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (validationPassed) { submitForm(); return; }
    showValidationModal();
});

function showValidationModal() {
    const text = document.getElementById('essayEditor').value.trim();
    const minWords = {{ $minWords }};
    const task = '{{ $task }}';
    const checks = performTaskChecks(text, minWords, task);
    const allPassed = checks.every(c => c.passed);

    const checksHTML = checks.map(c => `
        <div class="flex items-start gap-3 p-3.5 rounded-xl ${c.passed
            ? 'bg-emerald-500/10 border border-emerald-500/30'
            : 'bg-red-500/10 border border-red-500/30'}">
            ${c.passed
                ? '<svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'}
            <div>
                <p class="text-sm font-semibold ${c.passed ? 'text-emerald-300' : 'text-red-300'}">${c.label}</p>
                <p class="text-xs mt-0.5 ${c.passed ? 'text-emerald-400/80' : 'text-red-400/80'}">${c.message}</p>
            </div>
        </div>
    `).join('');

    document.body.insertAdjacentHTML('beforeend', `
        <div id="validationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-800 border border-surface-600 rounded-2xl shadow-card-hover max-w-lg w-full p-6 animate-scale-in">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-brand-500/15 flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-surface-50">Pre-Submission Check</h3>
                        <p class="text-xs text-surface-400">Reviewing your essay before scoring</p>
                    </div>
                </div>

                <div class="space-y-2 mb-5">${checksHTML}</div>

                <div class="p-3 rounded-xl mb-5 ${allPassed
                    ? 'bg-emerald-500/10 border border-emerald-500/30'
                    : 'bg-amber-500/10 border border-amber-500/30'}">
                    <p class="text-sm font-medium text-center ${allPassed ? 'text-emerald-300' : 'text-amber-300'}">
                        ${allPassed
                            ? 'All checks passed — your essay is ready to submit.'
                            : 'Some issues detected. You can still submit or go back and fix them.'}
                    </p>
                </div>

                <div class="flex gap-3">
                    <button onclick="closeValidationModal()"
                        class="btn-secondary flex-1">
                        Edit More
                    </button>
                    <button onclick="proceedWithSubmission()"
                        class="btn-primary flex-1">
                        Submit Now
                    </button>
                </div>
            </div>
        </div>
    `);
}

function performTaskChecks(text, minWords, task) {
    const words = text.split(/\s+/).length;
    const paragraphs = text.split(/\n\s*\n/).filter(p => p.trim().length > 0);
    const checks = [
        {
            label: 'Word Count',
            passed: words >= minWords,
            message: words >= minWords ? `${words} words (minimum: ${minWords})` : `Only ${words} words — need at least ${minWords}.`
        },
        {
            label: 'Paragraph Structure',
            passed: paragraphs.length >= 3,
            message: paragraphs.length >= 3 ? `${paragraphs.length} paragraphs (intro, body, conclusion)` : `Only ${paragraphs.length} paragraph(s) — aim for at least 3.`
        },
        {
            label: 'Introduction',
            passed: paragraphs.length > 0 && paragraphs[0].split(/\s+/).length >= 20,
            message: paragraphs.length > 0 && paragraphs[0].split(/\s+/).length >= 20 ? 'Introduction detected' : 'Introduction seems too short or missing'
        },
        {
            label: 'Conclusion',
            passed: paragraphs.length > 0 && paragraphs[paragraphs.length - 1].split(/\s+/).length >= 20,
            message: paragraphs.length > 0 && paragraphs[paragraphs.length - 1].split(/\s+/).length >= 20 ? 'Conclusion detected' : 'Conclusion seems too short or missing'
        }
    ];
    if (task === 'task2') {
        const hasPosition = /\b(I (believe|think|agree|disagree)|In my (opinion|view)|personally)\b/i.test(text);
        checks.push({
            label: 'Clear Position (Task 2)',
            passed: hasPosition,
            message: hasPosition ? 'Position statement detected' : 'Use phrases like "I believe..." or "In my opinion..."'
        });
    }
    return checks;
}

function closeValidationModal() {
    const m = document.getElementById('validationModal');
    if (m) m.remove();
}

function proceedWithSubmission() {
    validationPassed = true;
    closeValidationModal();
    document.getElementById('writingForm').dispatchEvent(new Event('submit'));
}

async function submitForm() {
    const form = document.getElementById('writingForm');
    const btn  = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Submitting...';

    showEvaluatingOverlay();

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
            body: new FormData(form)
        });
        const data = await res.json();
        if (data.success && data.redirect) {
            localStorage.removeItem('writing_draft_{{ $test->id }}');
            window.location.href = data.redirect;
        } else throw new Error(data.message);
    } catch(err) {
        hideEvaluatingOverlay();
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Submit Answer';
        validationPassed = false;
        showSubmitError(err?.message || 'Evaluation timed out. Please try again.');
    }
}

function showSubmitError(message) {
    document.body.insertAdjacentHTML('beforeend', `
        <div id="submitErrorModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-surface-800 border border-red-500/30 rounded-2xl shadow-card-hover max-w-md w-full p-6 animate-scale-in">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-surface-50">Evaluation Failed</h3>
                        <p class="text-xs text-surface-400 mt-0.5">${message}</p>
                    </div>
                </div>
                <p class="text-sm text-surface-300 mb-5">Your essay is saved as a draft. You can try submitting again — your work won't be lost.</p>
                <div class="flex gap-3">
                    <button onclick="document.getElementById('submitErrorModal').remove()"
                        class="btn-secondary flex-1 text-sm">Close</button>
                    <button onclick="document.getElementById('submitErrorModal').remove(); validationPassed = true; submitForm();"
                        class="btn-primary flex-1 text-sm">Try Again</button>
                </div>
            </div>
        </div>
    `);
}

function showEvaluatingOverlay() {
    const steps = [
        { icon: '📝', text: 'Reading your essay...' },
        { icon: '🎯', text: 'Checking task achievement...' },
        { icon: '🔗', text: 'Analysing coherence & cohesion...' },
        { icon: '📚', text: 'Evaluating vocabulary range...' },
        { icon: '✍️', text: 'Assessing grammar accuracy...' },
        { icon: '🤖', text: 'Calculating band scores...' },
        { icon: '✅', text: 'Preparing your result...' },
    ];

    let stepIndex = 0;
    let elapsed = 0;
    const overlay = document.createElement('div');
    overlay.id = 'evaluatingOverlay';
    overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center bg-surface-950/95 backdrop-blur-sm';
    overlay.innerHTML = `
        <div class="text-center max-w-sm w-full px-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center mx-auto mb-6 shadow-glow">
                <span id="evalIcon" class="text-4xl">📝</span>
            </div>
            <h2 class="text-xl font-bold text-surface-50 mb-2">AI Examiner Evaluating</h2>
            <p id="evalStep" class="text-sm text-brand-400 font-medium mb-6">Reading your essay...</p>
            <div class="w-full bg-surface-800 rounded-full h-1.5 mb-2">
                <div id="evalBar" class="h-1.5 rounded-full bg-gradient-to-r from-brand-600 to-brand-400 transition-all duration-700" style="width:4%"></div>
            </div>
            <p id="evalTime" class="text-xs text-surface-500">This usually takes 20–40 seconds</p>
        </div>
    `;
    document.body.appendChild(overlay);

    const interval = setInterval(() => {
        elapsed++;
        stepIndex = (stepIndex + 1) % steps.length; // loop instead of stopping
        document.getElementById('evalIcon').textContent = steps[stepIndex].icon;
        document.getElementById('evalStep').textContent = steps[stepIndex].text;
        // Bar grows to 85% in first pass, then pulses between 85-95%
        const pct = elapsed < steps.length
            ? ((elapsed + 1) / steps.length * 85)
            : (85 + (elapsed % 2) * 5);
        document.getElementById('evalBar').style.width = Math.min(pct, 95) + '%';
        if (elapsed >= steps.length) {
            document.getElementById('evalTime').textContent = 'Still working — complex essays take up to 60s…';
        }
    }, 2800);

    overlay._interval = interval;
}

function hideEvaluatingOverlay() {
    const overlay = document.getElementById('evaluatingOverlay');
    if (overlay) {
        if (overlay._interval) clearInterval(overlay._interval);
        overlay.remove();
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor      = document.getElementById('essayEditor');
    const wordCountEl = document.getElementById('wordCount');
    const minStatus   = document.getElementById('minWordsStatus');
    const submitBtn   = document.getElementById('submitBtn');
    const minWords    = {{ $minWords }};
    const draftKey    = 'writing_draft_{{ $test->id }}';

    function updateWordCount() {
        const text  = editor.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        wordCountEl.textContent = words;
        if (words >= minWords) {
            minStatus.textContent = `${words} / ${minWords} — Ready`;
            minStatus.className = 'text-emerald-400 font-medium text-xs';
            submitBtn.disabled = false;
        } else {
            minStatus.textContent = `${words} / ${minWords} min`;
            minStatus.className = 'text-red-400 font-medium text-xs';
            submitBtn.disabled = true;
        }
        updateRealTimeStats(text, words);
    }

    let vocabTimeout = null, lastVocabCount = 0;
    function updateRealTimeStats(text, wordCount) {
        if (!text || wordCount === 0) {
            document.getElementById('uniqueWords').textContent = '0';
            document.getElementById('avgSentence').textContent = '0';
            document.getElementById('complexSentences').textContent = '0';
            return;
        }
        const wordsArr  = text.toLowerCase().match(/\b\w+\b/g) || [];
        document.getElementById('uniqueWords').textContent = new Set(wordsArr).size;
        const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
        document.getElementById('avgSentence').textContent = sentences.length > 0 ? Math.round(wordCount / sentences.length) + ' wds' : '0';
        document.getElementById('complexSentences').textContent = sentences.filter(s =>
            s.includes(',') || /\b(and|but|because|although|however|moreover|furthermore|nevertheless)\b/i.test(s)
        ).length;

        if (wordCount >= 50 && Math.abs(wordCount - lastVocabCount) >= 30) {
            clearTimeout(vocabTimeout);
            vocabTimeout = setTimeout(() => { analyzeVocabulary(text); lastVocabCount = wordCount; }, 2000);
        }
    }

    async function analyzeVocabulary(text) {
        const statusEl = document.getElementById('vocabStatus');
        statusEl.textContent = 'Analyzing...';
        statusEl.className = 'text-xs text-surface-400';
        try {
            const res  = await fetch('{{ route("writing.analyze.vocabulary") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
                body: JSON.stringify({ text })
            });
            const data = await res.json();
            document.getElementById('basicPercent').textContent       = data.basic + '%';
            document.getElementById('basicBar').style.width           = data.basic + '%';
            document.getElementById('intermediatePercent').textContent = data.intermediate + '%';
            document.getElementById('intermediateBar').style.width     = data.intermediate + '%';
            document.getElementById('advancedPercent').textContent     = data.advanced + '%';
            document.getElementById('advancedBar').style.width         = data.advanced + '%';
            if (data.advanced >= 40) { statusEl.textContent = 'Excellent'; statusEl.className = 'text-xs text-emerald-400 font-semibold'; }
            else if (data.advanced >= 25) { statusEl.textContent = 'Good'; statusEl.className = 'text-xs text-amber-400 font-semibold'; }
            else { statusEl.textContent = 'Needs work'; statusEl.className = 'text-xs text-red-400 font-semibold'; }
        } catch { statusEl.textContent = 'Unavailable'; statusEl.className = 'text-xs text-surface-500'; }
    }

    editor.addEventListener('input', updateWordCount);

    const saved = localStorage.getItem(draftKey);
    if (saved) { editor.value = saved; updateWordCount(); }
    // localStorage autosave every 20s
    setInterval(() => { if (editor.value.trim()) localStorage.setItem(draftKey, editor.value); }, 20000);

    // DB autosave every 60s (silent — user never loses work even if browser crashes)
    const draftUrl   = '{{ route('writing.draft', $test->id) }}';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content;
    const savedLabel = document.getElementById('autosaveLabel');
    setInterval(async () => {
        const task1 = document.getElementById('task1_response')?.value ?? '';
        const task2 = document.getElementById('task2_response')?.value ?? editor.value ?? '';
        if (!task1 && !task2) return;
        try {
            await fetch(draftUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ task1_response: task1, task2_response: task2 }),
            });
            if (savedLabel) { savedLabel.textContent = 'Saved ' + new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); }
        } catch { /* silent — localStorage is backup */ }
    }, 60000);

    // Timer
    let time = {{ $totalTime }};
    const timerEl = document.getElementById('timer');
    function updateTimer() {
        const m = String(Math.floor(time / 60)).padStart(2, '0');
        const s = String(time % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
        if (time <= 300) timerEl.className = 'font-mono font-bold text-red-400 text-base tabular-nums animate-pulse';
        if (time === 600) { if (Notification.permission === 'granted') new Notification('IELTS Writing — 10 min remaining'); }
        if (time === 300) { if (Notification.permission === 'granted') new Notification('IELTS Writing — 5 min remaining'); }
        if (time <= 0) { clearInterval(timerInterval); submitBtn.click(); return; }
        time--;
    }
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
});
</script>

</x-app-layout>
