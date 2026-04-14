<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Free IELTS Writing Checker — Instant AI Band Score | IELTS Band AI</title>
    <meta name="description" content="Check your IELTS writing instantly with AI. Get a band score estimate for Task Response and Vocabulary — free, no signup required for basic feedback.">
    <meta name="keywords" content="IELTS writing checker, IELTS band score checker, free IELTS writing feedback, IELTS essay checker">
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

<main class="max-w-3xl mx-auto px-4 py-12">

    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-surface-50 mb-3">
            Free IELTS Writing Checker
        </h1>
        <p class="text-surface-400 text-lg">Paste your essay — get an instant AI band score estimate. No signup needed for basic feedback.</p>
    </div>

    <div x-data="writingChecker()" class="space-y-5">

        {{-- Task type selector --}}
        <div class="flex gap-3">
            <button @click="taskType='task1'" :class="taskType==='task1' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-surface-700 text-surface-400'"
                class="flex-1 border rounded-xl px-4 py-3 text-sm font-semibold transition-all">
                Task 1 <span class="font-normal opacity-70">(Graph / Chart / Map)</span>
            </button>
            <button @click="taskType='task2'" :class="taskType==='task2' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-surface-700 text-surface-400'"
                class="flex-1 border rounded-xl px-4 py-3 text-sm font-semibold transition-all">
                Task 2 <span class="font-normal opacity-70">(Essay / Argument)</span>
            </button>
        </div>

        {{-- Essay textarea --}}
        <div class="relative">
            <textarea x-model="essay" @input="wordCount = essay.trim().split(/\s+/).filter(w=>w).length"
                placeholder="Paste your IELTS writing here... (minimum 50 words for analysis)"
                rows="12"
                class="w-full bg-surface-900 border border-surface-700 rounded-xl px-5 py-4 text-surface-100 text-sm focus:outline-none focus:border-brand-500 resize-none leading-relaxed">
            </textarea>
            <div class="absolute bottom-3 right-4 text-xs text-surface-500">
                <span :class="wordCount < 150 ? 'text-amber-400' : 'text-emerald-400'" x-text="wordCount"></span> words
            </div>
        </div>

        <button @click="analyze()" :disabled="loading || essay.trim().length < 50"
            class="btn-primary w-full py-3.5 text-base font-bold justify-center disabled:opacity-40 disabled:cursor-not-allowed">
            <span x-show="!loading">Check My Writing →</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                Analysing with AI…
            </span>
        </button>

        {{-- Results --}}
        <div x-show="result" x-transition class="space-y-4">

            {{-- Band scores --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="card p-5 text-center">
                    <p class="text-xs text-surface-400 uppercase tracking-wider mb-2">Task Response</p>
                    <p class="text-4xl font-bold text-brand-400" x-text="result?.task_response_band ?? '—'"></p>
                    <p class="text-xs text-surface-500 mt-2" x-text="result?.task_comment"></p>
                </div>
                <div class="card p-5 text-center">
                    <p class="text-xs text-surface-400 uppercase tracking-wider mb-2">Vocabulary</p>
                    <p class="text-4xl font-bold text-purple-400" x-text="result?.lexical_band ?? '—'"></p>
                    <p class="text-xs text-surface-500 mt-2" x-text="result?.lexical_comment"></p>
                </div>
            </div>

            {{-- Top improvement --}}
            <div class="card p-5 border border-amber-500/20 bg-amber-500/5">
                <p class="text-xs text-amber-400 uppercase tracking-wider mb-2">Top Priority Improvement</p>
                <p class="text-surface-200 text-sm" x-text="result?.top_improvement"></p>
            </div>

            {{-- Word count warning --}}
            <template x-if="result && !result.word_count_ok">
            <div class="card p-4 border border-red-500/20 bg-red-500/5 text-sm text-red-400">
                ⚠️ Word count may be too low. Task 1 requires 150+ words, Task 2 requires 250+ words.
                Your submission has <span x-text="result?.word_count"></span> words.
            </div>
            </template>

            {{-- CTA for full feedback --}}
            <div class="card p-6 text-center border border-brand-500/20 bg-brand-500/5">
                <p class="font-semibold text-surface-100 mb-2">Want Your Full Band Score?</p>
                <p class="text-surface-400 text-sm mb-4">
                    Get scores for all 4 criteria (Task Response, Coherence, Vocabulary, Grammar),
                    a Band 9 model answer, and detailed examiner feedback.
                </p>
                <a href="{{ route('register') }}" class="btn-primary justify-center px-8 py-3 font-bold inline-flex">
                    Get Full Feedback Free →
                </a>
                <p class="text-xs text-surface-600 mt-3">Free account includes 3 full writing tests</p>
            </div>
        </div>

        <div x-show="error" x-transition class="card p-4 border border-red-500/20 bg-red-500/5 text-red-400 text-sm" x-text="error"></div>

    </div>

    {{-- SEO content --}}
    <div class="mt-16 space-y-6 text-surface-400 text-sm leading-relaxed">
        <h2 class="text-xl font-semibold text-surface-200">How does the IELTS Writing Checker work?</h2>
        <p>Our AI writing checker uses the official IELTS band descriptors from Cambridge Assessment English to evaluate your essay. It checks your Task Response (are you answering the question?), Lexical Resource (variety and accuracy of vocabulary), Coherence and Cohesion (organisation and flow), and Grammatical Range and Accuracy.</p>
        <p>The free checker gives you a score for Task Response and Vocabulary. Create a free account to get all 4 criteria scored, a Band 9 model answer, and specific examiner-style feedback on what to improve.</p>

        <h2 class="text-xl font-semibold text-surface-200">What is a good IELTS Writing score?</h2>
        <p>Band 7+ is generally required for most UK/Australian university programs. Band 6.5 is common for nursing and healthcare registration. Band 6 is a typical immigration threshold. Most test-takers score between 5.5 and 6.5 on writing — it's the hardest module to improve quickly without targeted practice.</p>
    </div>

</main>

<script>
function writingChecker() {
    return {
        essay: '', taskType: 'task2', wordCount: 0,
        loading: false, result: null, error: null,
        async analyze() {
            this.loading = true; this.result = null; this.error = null;
            try {
                const res = await fetch('{{ route('tools.writing-checker.analyze') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ essay: this.essay, task_type: this.taskType }),
                });
                const data = await res.json();
                if (data.success) { this.result = data; }
                else { this.error = data.message || 'Analysis failed. Please try again.'; }
            } catch { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        }
    };
}
</script>
</body>
</html>
