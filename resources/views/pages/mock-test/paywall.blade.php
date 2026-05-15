<x-app-layout>
<div class="min-h-screen bg-surface-950 py-12 px-4">
<div class="max-w-xl mx-auto">

    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-surface-50 mb-2">Unlock your mock test results</h1>
        <p class="text-surface-400">All 4 modules submitted — pay the unlock fee to see your AI-graded band and module feedback.</p>
    </div>

    {{-- Module status checklist --}}
    <div class="card p-5 mb-6">
        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Submitted</p>
        <div class="space-y-2.5">
            @foreach(['listening'=>'🎧 Listening','reading'=>'📖 Reading','writing'=>'✍️ Writing','speaking'=>'🎤 Speaking'] as $mod => $label)
            <div class="flex items-center justify-between">
                <span class="text-sm text-surface-200">{{ $label }}</span>
                @if($mock->{$mod.'_test_id'})
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Submitted
                </span>
                @else
                <span class="text-xs text-amber-400">Not submitted</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pricing card --}}
    <div class="card p-6 mb-6 border-2 border-brand-500/40 bg-gradient-to-br from-brand-700/20 to-brand-900/20">
        <div class="flex items-baseline gap-2 mb-2">
            <span class="text-5xl font-bold text-brand-300">{{ $cost }}</span>
            <span class="text-surface-300 text-sm">credit{{ $cost === 1 ? '' : 's' }}</span>
        </div>
        <p class="text-surface-400 text-sm mb-5">One-time unlock — covers AI evaluation of all 4 modules + overall band + per-module feedback.</p>

        @if($userCred >= $cost)
        {{-- User has enough credits — show the pay button --}}
        <p class="text-xs text-surface-500 mb-3">
            You currently have <span class="font-semibold text-surface-200">{{ $userCred }} credit{{ $userCred === 1 ? '' : 's' }}</span>.
            Paying will leave you with {{ $userCred - $cost }}.
        </p>
        <form method="POST" action="{{ route('mock-test.unlock', $mock) }}">
            @csrf
            <button type="submit" class="btn-primary w-full justify-center py-3 text-base font-bold shadow-glow">
                Pay {{ $cost }} credit{{ $cost === 1 ? '' : 's' }} and unlock results →
            </button>
        </form>
        @else
        {{-- Not enough credits — direct to the top-up paywall --}}
        <p class="text-xs text-amber-400 mb-3">
            You have <span class="font-semibold">{{ $userCred }} credit{{ $userCred === 1 ? '' : 's' }}</span> — you need {{ $cost - $userCred }} more.
        </p>
        <a href="{{ route('paywall.index', ['from' => 'mock-test']) }}"
           class="btn-primary w-full justify-center py-3 text-base font-bold shadow-glow">
            Buy credits to unlock →
        </a>
        <p class="text-[11px] text-surface-500 text-center mt-3">After top-up, come back to this page to complete the unlock.</p>
        @endif
    </div>

    {{-- What you'll see after unlock --}}
    <div class="card p-5 mb-6 bg-surface-800/50">
        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">What you'll see after unlocking</p>
        <ul class="space-y-2 text-sm text-surface-300">
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>Overall IELTS band (0.0–9.0, rounded to nearest 0.5)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>Per-module bands (Listening, Reading, Writing, Speaking)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>AI examiner feedback for each module (1-3 min after unlock for Writing &amp; Speaking)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>Speaking transcripts with filler / pause / grammar highlights</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>4-week personalised study plan based on weak areas</span>
            </li>
        </ul>
    </div>

    <p class="text-center text-xs text-surface-600">
        Charged just once for this mock test. Your test data is saved and waiting for you.
    </p>

</div>
</div>
</x-app-layout>
