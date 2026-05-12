<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4">
<div class="max-w-md w-full text-center">

    @php $alreadyFailed = ($test->status ?? null) === 'failed'; @endphp

    {{-- ── Evaluating state ────────────────────────────────────────── --}}
    <div id="evaluating-card" class="card p-10 {{ $alreadyFailed ? 'hidden' : '' }}">
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-full border-4 border-surface-700 border-t-indigo-500 animate-spin"></div>
        </div>

        <h1 class="text-xl font-bold text-surface-50 mb-2">Evaluating your writing…</h1>
        <p class="text-surface-400 text-sm mb-6">
            Our AI examiner is reviewing your essay against IELTS band descriptors.<br>
            This usually takes 30–60 seconds.
        </p>

        <div class="bg-surface-900 rounded-lg px-4 py-3 text-xs text-surface-400 text-left space-y-1 mb-6">
            <div id="step1" class="flex items-center gap-2 text-surface-500">
                <span class="w-3 h-3 rounded-full bg-surface-700 shrink-0"></span> Checking task achievement
            </div>
            <div id="step2" class="flex items-center gap-2 text-surface-500">
                <span class="w-3 h-3 rounded-full bg-surface-700 shrink-0"></span> Analyzing coherence &amp; cohesion
            </div>
            <div id="step3" class="flex items-center gap-2 text-surface-500">
                <span class="w-3 h-3 rounded-full bg-surface-700 shrink-0"></span> Scoring lexical resource
            </div>
            <div id="step4" class="flex items-center gap-2 text-surface-500">
                <span class="w-3 h-3 rounded-full bg-surface-700 shrink-0"></span> Reviewing grammar range &amp; accuracy
            </div>
            <div id="step5" class="flex items-center gap-2 text-surface-500">
                <span class="w-3 h-3 rounded-full bg-surface-700 shrink-0"></span> Generating band score &amp; feedback
            </div>
        </div>

        <p id="taking-longer" class="text-amber-400/90 text-xs mb-3 hidden">
            This is taking a bit longer than usual. We'll keep trying.
        </p>
        <p class="text-surface-600 text-xs">You can safely close this tab — your result will be saved.</p>
    </div>

    {{-- ── Failure state (hidden by default; shown if test is already failed) ──────────────────────── --}}
    <div id="failed-card" class="card p-10 {{ $alreadyFailed ? '' : 'hidden' }}">
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 rounded-full bg-rose-500/15 flex items-center justify-center">
                <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
        </div>

        <h1 id="failed-title" class="text-xl font-bold text-surface-50 mb-2">Evaluation didn't complete</h1>
        <p id="failed-message" class="text-surface-400 text-sm mb-6">
            Something went wrong while scoring your essay. Your credit has not been deducted — you can try submitting again.
        </p>

        <div class="space-y-3">
            <a href="{{ route('writing.index') }}" class="btn-primary w-full">Try a new test</a>
            <a href="{{ route('dashboard') }}" class="btn-ghost w-full text-sm">Back to dashboard</a>
        </div>

        <p class="text-surface-600 text-xs mt-6">
            If this keeps happening, please share feedback so we can fix it.
        </p>
    </div>

</div>
</div>

<script>
const testId  = {{ $test->id }};
const steps   = ['step1','step2','step3','step4','step5'];
let stepIdx   = 0;
let pollCount = 0;
const startedAt = Date.now();

// Hard ceiling: stop polling and show failure UI after this many ms.
// 4 min covers the 99th percentile of LLM scoring + network jitter; beyond
// that something is genuinely wrong (queue worker dead, provider down, etc.)
// and the user deserves to know rather than spin forever.
const MAX_WAIT_MS = 4 * 60 * 1000;

const evaluatingCard = document.getElementById('evaluating-card');
const failedCard     = document.getElementById('failed-card');
const failedTitle    = document.getElementById('failed-title');
const failedMessage  = document.getElementById('failed-message');
const takingLonger   = document.getElementById('taking-longer');

function showFailure(title, message) {
    if (title)   failedTitle.textContent   = title;
    if (message) failedMessage.textContent = message;
    evaluatingCard.classList.add('hidden');
    failedCard.classList.remove('hidden');
}

// Animate steps sequentially for UX feel
const stepTimer = setInterval(() => {
    if (stepIdx < steps.length) {
        const el = document.getElementById(steps[stepIdx]);
        el.querySelector('span').className = 'w-3 h-3 rounded-full bg-indigo-500 shrink-0';
        el.className = el.className.replace('text-surface-500', 'text-surface-200');
        stepIdx++;
    } else {
        clearInterval(stepTimer);
    }
}, 6000);

async function poll() {
    pollCount++;

    // Show "this is taking longer" hint after 90s.
    if (Date.now() - startedAt > 90000) {
        takingLonger.classList.remove('hidden');
    }

    // Hard timeout — give up and show failure state.
    if (Date.now() - startedAt > MAX_WAIT_MS) {
        showFailure(
            'Evaluation is taking longer than expected',
            'Your essay was saved, but we couldn\'t get a score back from the AI in time. Please try again — your credit has not been used.'
        );
        return;
    }

    try {
        const r = await fetch(`/api/test/${testId}/status`, { cache: 'no-store' });
        if (!r.ok) throw new Error('fetch failed: ' + r.status);
        const data = await r.json();

        if (data.status === 'completed') {
            window.location.href = `/writing/result/${testId}`;
            return;
        }
        if (data.status === 'failed') {
            showFailure(
                'Evaluation didn\'t complete',
                'Something went wrong while scoring your essay. Your credit has not been deducted — you can try submitting again.'
            );
            return;
        }
        // status is 'evaluating' or 'in_progress' — keep polling
    } catch (e) {
        // Network/server hiccup — keep polling unless we've crossed timeout.
        // Logged silently; user sees the "taking longer" hint after 90s.
    }

    // Poll every 4s for first 2 min, then every 10s
    const delay = pollCount < 30 ? 4000 : 10000;
    setTimeout(poll, delay);
}

// Skip polling entirely when the page was rendered already in failed state.
@if(!$alreadyFailed)
setTimeout(poll, 4000);
@endif
</script>
</x-app-layout>
