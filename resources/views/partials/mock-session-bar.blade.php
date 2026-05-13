{{--
  Mock-test session bar. Renders a thin top strip across L → R → W → S
  showing total elapsed session time, current module, and overall mock
  progress. Real IELTS Listening + Reading + Writing run as ONE continuous
  session — this bar surfaces that across our per-module timed pages.

  No-op when not in mock context (session 'mock_test_id' unset or stale).

  Style is inline so it works in both x-app-layout (Tailwind world) and
  x-exam-layout (vanilla CSS world).
--}}
@php
    $mockTestId = session('mock_test_id');
    $mockTest   = $mockTestId ? \App\Models\MockTest::find($mockTestId) : null;
@endphp

@if($mockTest && $mockTest->started_at && $mockTest->status === 'in_progress')
@php
    $modules      = \App\Models\MockTest::MODULES;
    $currentMod   = $mockTest->current_module ?? 'listening';
    $moduleIdx    = array_search($currentMod, $modules);
    $startedAtMs  = $mockTest->started_at->getTimestampMs();
@endphp

<div id="mockSessionBar" style="background:#003087;color:#fff;padding:6px 20px;font-family:Arial,Helvetica,sans-serif;font-size:11px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;position:sticky;top:0;z-index:60;">
    <span style="font-weight:bold;letter-spacing:0.05em;">📘 MOCK TEST</span>
    <span style="opacity:0.9;">Module {{ $moduleIdx + 1 }} of 4 — <strong style="color:#fff;">{{ ucfirst($currentMod) }}</strong></span>
    <span style="opacity:0.6;">·</span>
    <span style="opacity:0.9;">Session: <span id="mockSessionElapsed" style="font-family:'Courier New',monospace;font-weight:bold;color:#fff;">00:00</span></span>

    <div style="display:flex;gap:4px;margin-left:auto;align-items:center;">
        @foreach($modules as $i => $m)
            @php
                $isPast = $i < $moduleIdx;
                $isCurr = $m === $currentMod;
                $bg = $isPast ? '#10B981' : ($isCurr ? '#FFFFFF' : 'rgba(255,255,255,0.25)');
                $fg = $isPast ? '#fff' : ($isCurr ? '#003087' : 'rgba(255,255,255,0.7)');
            @endphp
            <div title="{{ ucfirst($m) }}" style="width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;background:{{ $bg }};color:{{ $fg }};">
                {{ $isPast ? '✓' : ($i + 1) }}
            </div>
        @endforeach
    </div>
</div>

<script>
(function() {
    const startedAtMs = {{ $startedAtMs }};
    const elapsedEl = document.getElementById('mockSessionElapsed');
    if (!elapsedEl) return;

    function fmt(totalSec) {
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = Math.floor(totalSec % 60);
        const pad = (n) => String(n).padStart(2, '0');
        return h > 0 ? h + ':' + pad(m) + ':' + pad(s) : pad(m) + ':' + pad(s);
    }

    function tick() {
        const elapsed = Math.max(0, Math.floor((Date.now() - startedAtMs) / 1000));
        elapsedEl.textContent = fmt(elapsed);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
