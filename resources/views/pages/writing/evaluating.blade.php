<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4">
<div class="max-w-md w-full text-center">

    <div class="card p-10">
        {{-- Spinner --}}
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

        <p class="text-surface-600 text-xs">You can safely close this tab — your result will be saved.</p>
    </div>

</div>
</div>

<script>
const testId  = {{ $test->id }};
const steps   = ['step1','step2','step3','step4','step5'];
let stepIdx   = 0;
let pollCount = 0;

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

// Poll for completion
async function poll() {
    pollCount++;
    try {
        const r = await fetch(`/api/test/${testId}/status`);
        if (!r.ok) throw new Error('fetch failed');
        const data = await r.json();

        if (data.status === 'completed') {
            window.location.href = `/writing/result/${testId}`;
            return;
        }
        if (data.status === 'failed') {
            window.location.href = '/writing?error=evaluation_failed';
            return;
        }
    } catch (e) {
        // network hiccup — keep polling
    }

    // Poll every 4s for first 2 min, then every 10s
    const delay = pollCount < 30 ? 4000 : 10000;
    setTimeout(poll, delay);
}

setTimeout(poll, 4000);
</script>
</x-app-layout>
