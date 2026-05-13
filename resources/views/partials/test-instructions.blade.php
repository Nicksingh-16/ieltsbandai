{{--
  Pre-test instructions overlay. Blocks the test page until the user
  acknowledges the IELTS rules for this module and clicks "Begin".

  Each test view's timer/audio code should defer auto-start until the
  custom `test:begin` event fires (or check window.__testBegun on load).

  Usage:
    @include('partials.test-instructions', [
        'module'        => 'listening',           // listening|reading|writing|speaking
        'title'         => 'IELTS Listening',
        'timeLabel'     => '40 minutes (30 listen + 10 transfer)',
        'rules'         => [                      // array of plain strings
            'You will hear 4 sections...',
            'Each section plays ONCE — no replay or seek.',
            ...
        ],
        'startLabel'    => "I'm ready — Begin Listening test",  // optional
        'ackLabel'      => 'I have read the instructions...',   // optional
    ])
--}}
@php
    $module     = $module     ?? 'test';
    $title      = $title      ?? 'IELTS Test';
    $timeLabel  = $timeLabel  ?? '';
    $rules      = $rules      ?? [];
    $startLabel = $startLabel ?? "I'm ready — Begin test";
    $ackLabel   = $ackLabel   ?? 'I have read these instructions and I\'m ready to start the timer.';

    $iconMap = [
        'listening' => '🎧',
        'reading'   => '📖',
        'writing'   => '✍️',
        'speaking'  => '🎤',
    ];
    $icon = $iconMap[$module] ?? '📝';
@endphp

<div id="testInstructionsOverlay"
     class="fixed inset-0 z-[100] bg-surface-950/97 backdrop-blur-sm overflow-y-auto"
     aria-modal="true" role="dialog" aria-labelledby="testInstructionsTitle">
    <div class="min-h-full flex items-start sm:items-center justify-center p-4 py-8">
        <div class="max-w-2xl w-full bg-surface-800 border border-surface-600 rounded-2xl shadow-2xl p-6 sm:p-8 animate-fade-up">

            <div class="flex items-start gap-4 mb-5">
                <div class="text-5xl leading-none shrink-0">{{ $icon }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-brand-400 uppercase tracking-widest mb-1">Test Instructions</p>
                    <h2 id="testInstructionsTitle" class="text-2xl sm:text-3xl font-extrabold text-surface-50 leading-tight">
                        {{ $title }}
                    </h2>
                    @if($timeLabel)
                    <p class="text-sm text-surface-400 mt-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong class="text-surface-200">{{ $timeLabel }}</strong></span>
                    </p>
                    @endif
                </div>
            </div>

            <div class="bg-surface-900/60 border border-surface-700 rounded-xl p-4 sm:p-5 mb-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Read carefully — these are real-IELTS rules</p>
                <ul class="space-y-2.5">
                    @foreach($rules as $rule)
                    <li class="flex items-start gap-2.5 text-sm text-surface-200 leading-relaxed">
                        <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{!! $rule !!}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <label class="flex items-start gap-3 p-3.5 rounded-xl bg-surface-700/60 border border-surface-600 mb-5 cursor-pointer hover:bg-surface-700/80 transition">
                <input type="checkbox" id="beginAck"
                    class="mt-0.5 w-4 h-4 rounded border-surface-500 bg-surface-800 text-brand-500 focus:ring-brand-500 focus:ring-offset-0 cursor-pointer shrink-0"
                    onchange="document.getElementById('beginTestBtn').disabled = !this.checked;">
                <span class="text-sm text-surface-200 leading-relaxed">{{ $ackLabel }}</span>
            </label>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('dashboard') }}"
                   class="btn-ghost flex-1 justify-center text-sm py-2.5">
                    Cancel — return to dashboard
                </a>
                <button id="beginTestBtn" type="button" onclick="window.beginTest()" disabled
                    class="flex-1 px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-semibold text-sm transition disabled:opacity-40 disabled:cursor-not-allowed shadow-glow disabled:shadow-none">
                    {{ $startLabel }}
                </button>
            </div>

            <p class="text-[11px] text-surface-500 text-center mt-4">
                The timer starts the moment you tap Begin. You cannot pause the test.
            </p>

        </div>
    </div>
</div>

<style>
@keyframes fade-up {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-up { animation: fade-up 0.25s ease-out; }
</style>

<script>
// Test pages should defer their timer auto-start until this event fires.
// Pattern in each test view:
//   function startTimer() { ... existing setInterval ... }
//   if (window.__testBegun) startTimer();
//   else window.addEventListener('test:begin', startTimer, { once: true });
window.__testBegun = false;
window.beginTest = function() {
    const cb = document.getElementById('beginAck');
    if (cb && !cb.checked) return;
    window.__testBegun = true;
    window.dispatchEvent(new Event('test:begin'));
    const overlay = document.getElementById('testInstructionsOverlay');
    if (overlay) {
        overlay.style.transition = 'opacity 0.2s ease-out';
        overlay.style.opacity = '0';
        setTimeout(function() { overlay.remove(); }, 200);
    }
};
</script>
