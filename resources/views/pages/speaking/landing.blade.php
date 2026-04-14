<x-app-layout>
<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}" class="btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
            <span class="tag-cyan">Speaking Test</span>
        </div>

        {{-- Hero --}}
        <div class="card border-glow p-8 mb-6 text-center">
            <div class="w-20 h-20 rounded-2xl bg-brand-500/15 border border-brand-500/30 flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-brand-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-surface-50 mb-2">IELTS Speaking Test</h1>
            <p class="text-surface-400 text-sm max-w-md mx-auto">
                Full 3-part AI-evaluated speaking test. Scored against official IELTS band descriptors.
                Allow microphone access when prompted.
            </p>
        </div>

        {{-- Test structure --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">Test Structure</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 bg-surface-700/40 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center text-xs font-bold text-brand-400 shrink-0">1</div>
                    <div>
                        <p class="text-sm font-semibold text-surface-100">Part 1 — Introduction & Interview</p>
                        <p class="text-xs text-surface-400 mt-0.5">4–5 questions on familiar topics (hometown, work, hobbies). ~4–5 minutes.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 bg-surface-700/40 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center text-xs font-bold text-brand-400 shrink-0">2</div>
                    <div>
                        <p class="text-sm font-semibold text-surface-100">Part 2 — Long Turn (Cue Card)</p>
                        <p class="text-xs text-surface-400 mt-0.5">1 minute to prepare, then speak for 1–2 minutes on the given topic.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 bg-surface-700/40 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center text-xs font-bold text-brand-400 shrink-0">3</div>
                    <div>
                        <p class="text-sm font-semibold text-surface-100">Part 3 — Two-way Discussion</p>
                        <p class="text-xs text-surface-400 mt-0.5">Abstract questions related to Part 2. ~4–5 minutes.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scoring criteria --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">Scoring Criteria</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['Fluency & Coherence','Lexical Resource','Grammatical Range & Accuracy','Pronunciation'] as $criterion)
                <div class="flex items-center gap-2 p-3 bg-surface-700/40 rounded-xl">
                    <div class="w-2 h-2 rounded-full bg-brand-400 shrink-0"></div>
                    <span class="text-xs text-surface-300">{{ $criterion }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tips --}}
        <div class="card p-6 mb-8">
            <h2 class="section-title mb-3">Before You Start</h2>
            <ul class="space-y-2 text-sm text-surface-400">
                <li class="flex items-start gap-2"><span class="text-brand-400 mt-0.5">✓</span> Use headphones or be in a quiet room</li>
                <li class="flex items-start gap-2"><span class="text-brand-400 mt-0.5">✓</span> Allow microphone access when your browser asks</li>
                <li class="flex items-start gap-2"><span class="text-brand-400 mt-0.5">✓</span> Speak naturally — don't read from a script</li>
                <li class="flex items-start gap-2"><span class="text-brand-400 mt-0.5">✓</span> This uses 1 test credit and cannot be paused</li>
            </ul>
        </div>

        {{-- CTA --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('speaking.test') }}"
               onclick="return confirm('This will use 1 test credit and start the speaking test. Are you ready?')"
               class="btn-primary flex-1 justify-center py-3 text-base font-bold">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                </svg>
                Start Speaking Test
            </a>
            <a href="{{ route('dashboard') }}" class="btn-secondary flex-1 justify-center py-3">Back</a>
        </div>

    </div>
</div>
</x-app-layout>
