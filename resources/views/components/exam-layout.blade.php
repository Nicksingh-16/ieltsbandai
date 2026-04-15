@props(['examTitle' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $examTitle ?? 'IELTS Exam' }} — IELTS Band AI</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #ffffff;
            color: #1a1a1a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .exam-header {
            height: 50px;
            background: #ffffff;
            border-bottom: 2px solid #003087;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 50;
            user-select: none;
        }
        .exam-header-brand { font-size: 11px; font-weight: bold; color: #003087; letter-spacing: .05em; text-transform: uppercase; min-width: 200px; }
        .exam-header-title { flex: 1; text-align: center; font-size: 15px; font-weight: bold; color: #1a1a1a; }
        .exam-header-timer { min-width: 200px; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
        .exam-timer-value { font-family: 'Courier New', monospace; font-size: 16px; font-weight: bold; color: #003087; letter-spacing: .05em; }
        .exam-timer-value.warning { color: #CC7700; }
        .exam-timer-value.danger  { color: #CC0000; animation: timerPulse 1s infinite; }
        @keyframes timerPulse { 0%,100%{opacity:1;} 50%{opacity:.5;} }

        .exam-toolbar { height: 36px; background: #F0F2F7; border-bottom: 1px solid #D0D3DC; display: flex; align-items: center; padding: 0 16px; gap: 8px; }
        .exam-tool-btn { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; font-size: 12px; border: 1px solid #B0B3BC; background: #ffffff; color: #333; cursor: pointer; border-radius: 2px; font-family: Arial, sans-serif; transition: background .15s; user-select: none; }
        .exam-tool-btn:hover { background: #E0E3EC; }
        .exam-tool-btn.active { background: #003087; color: #fff; border-color: #003087; }
        .exam-tool-sep { width: 1px; height: 20px; background: #C0C3CC; margin: 0 4px; }

        .highlight-yellow { background: #FFEF9F; cursor: pointer; }
        .highlight-green  { background: #BDFFC7; cursor: pointer; }
        .highlight-pink   { background: #FFCECE; cursor: pointer; }

        .exam-passage { font-size: 13.5px; line-height: 1.75; color: #1a1a1a; }
        .exam-passage p { margin-bottom: .9em; }

        .exam-question-block { border-bottom: 1px solid #E4E6EF; padding: 14px 0; }
        .exam-q-number { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #003087; color: #fff; font-size: 11px; font-weight: bold; border-radius: 2px; flex-shrink: 0; }
        .exam-flag-btn { border: none; background: none; cursor: pointer; color: #bbb; font-size: 14px; padding: 2px 4px; line-height: 1; transition: color .15s; }
        .exam-flag-btn:hover { color: #E07B00; }
        .exam-flag-btn.flagged { color: #E07B00; }

        .exam-option { display: flex; align-items: flex-start; gap: 8px; padding: 6px 8px; cursor: pointer; border-radius: 2px; }
        .exam-option:hover { background: #F0F2F7; }
        .exam-option input[type="radio"], .exam-option input[type="checkbox"] { margin-top: 2px; accent-color: #003087; width: 14px; height: 14px; flex-shrink: 0; }
        .exam-text-input { border: 1px solid #B0B3BC; padding: 5px 8px; font-size: 13px; font-family: Arial, sans-serif; border-radius: 2px; outline: none; transition: border-color .15s; }
        .exam-text-input:focus { border-color: #003087; box-shadow: 0 0 0 2px rgba(0,48,135,.12); }
        .exam-select { border: 1px solid #B0B3BC; padding: 5px 8px; font-size: 13px; font-family: Arial, sans-serif; border-radius: 2px; background: #fff; outline: none; }
        .exam-select:focus { border-color: #003087; }

        .exam-nav-panel { position: fixed; bottom: 0; left: 0; right: 0; background: #F0F2F7; border-top: 2px solid #003087; z-index: 40; padding: 8px 16px; display: flex; align-items: center; gap: 12px; }
        .exam-nav-grid { display: flex; flex-wrap: wrap; gap: 4px; flex: 1; }
        .exam-nav-dot { width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; border: 1px solid #B0B3BC; background: #fff; color: #555; cursor: pointer; border-radius: 2px; transition: all .1s; user-select: none; }
        .exam-nav-dot:hover { border-color: #003087; color: #003087; }
        .exam-nav-dot.answered { background: #003087; color: #fff; border-color: #003087; }
        .exam-nav-dot.flagged  { background: #E07B00; color: #fff; border-color: #E07B00; }

        .exam-nav-legend { display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: #555; min-width: 120px; }
        .exam-nav-legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-dot { width: 14px; height: 14px; border-radius: 2px; border: 1px solid #B0B3BC; }
        .legend-dot.answered { background: #003087; border-color: #003087; }
        .legend-dot.flagged  { background: #E07B00; border-color: #E07B00; }
        .legend-dot.empty    { background: #fff; }

        .exam-notes-panel { position: fixed; right: 0; top: 50px; width: 280px; bottom: 60px; background: #FFFEF0; border-left: 2px solid #003087; z-index: 45; display: flex; flex-direction: column; transform: translateX(100%); transition: transform .2s ease; }
        .exam-notes-panel.open { transform: translateX(0); }
        .exam-notes-header { padding: 10px 14px; background: #003087; color: #fff; font-size: 13px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .exam-notes-textarea { flex: 1; padding: 12px; border: none; resize: none; font-size: 13px; font-family: Arial, sans-serif; background: #FFFEF0; outline: none; color: #1a1a1a; }

        .exam-submit-btn { padding: 7px 20px; background: #003087; color: #fff; border: none; font-size: 13px; font-weight: bold; cursor: pointer; font-family: Arial, sans-serif; border-radius: 2px; transition: background .15s; white-space: nowrap; }
        .exam-submit-btn:hover { background: #002060; }

        .exam-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 999; display: flex; align-items: center; justify-content: center; }
        .exam-modal { background: #fff; border: 2px solid #003087; border-radius: 2px; padding: 24px 28px; max-width: 440px; width: 90%; }
        .exam-modal-title { font-size: 16px; font-weight: bold; color: #003087; margin-bottom: 12px; }
        .exam-modal-body { font-size: 13px; color: #333; margin-bottom: 20px; line-height: 1.6; }
        .exam-modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .exam-modal-cancel { padding: 6px 16px; border: 1px solid #B0B3BC; background: #fff; font-size: 13px; cursor: pointer; border-radius: 2px; font-family: Arial, sans-serif; }
        .exam-modal-cancel:hover { background: #F0F2F7; }
        .exam-modal-confirm { padding: 6px 16px; background: #003087; color: #fff; border: none; font-size: 13px; font-weight: bold; cursor: pointer; border-radius: 2px; font-family: Arial, sans-serif; }
        .exam-modal-confirm:hover { background: #002060; }

        .exam-section-header { background: #E8EAF0; border: 1px solid #C8CADC; padding: 8px 14px; font-size: 12px; font-weight: bold; color: #003087; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @stack('head')

    <style>
        /* Tab-switch warning overlay */
        #tabWarningOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        #tabWarningOverlay.show { display: flex; }
        .tab-warning-box {
            background: #fff;
            border: 3px solid #CC0000;
            border-radius: 4px;
            padding: 32px 40px;
            max-width: 420px;
        }
        .tab-warning-icon { font-size: 48px; margin-bottom: 12px; }
        .tab-warning-title { font-size: 20px; font-weight: bold; color: #CC0000; margin-bottom: 10px; }
        .tab-warning-body  { font-size: 14px; color: #333; line-height: 1.6; margin-bottom: 20px; }
        .tab-warning-count { font-size: 13px; color: #777; margin-bottom: 20px; }
        .tab-warning-btn   { padding: 10px 28px; background: #003087; color: #fff; border: none; font-size: 14px; font-weight: bold; cursor: pointer; border-radius: 2px; font-family: Arial, sans-serif; }

        /* Fullscreen prompt overlay */
        #fullscreenPrompt {
            position: fixed;
            inset: 0;
            background: #0F172A;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        #fullscreenPrompt.hidden { display: none; }
    </style>
</head>
<body class="h-full" oncontextmenu="return false;">
    {{-- Fullscreen entry prompt --}}
    <div id="fullscreenPrompt">
        <div style="text-align:center;max-width:460px;padding:40px;">
            <div style="font-size:48px;margin-bottom:16px;">🎓</div>
            <h2 style="font-size:22px;font-weight:bold;color:#fff;margin-bottom:10px;">Exam Simulation Mode</h2>
            <p style="font-size:14px;color:#94a3b8;line-height:1.7;margin-bottom:8px;">
                This exam runs in <strong style="color:#fff;">fullscreen mode</strong> to replicate real IELTS conditions.
            </p>
            <ul style="text-align:left;font-size:13px;color:#64748b;margin:16px 0 24px;padding-left:20px;line-height:2;">
                <li>Tab switching will be recorded</li>
                <li>Copy &amp; paste is disabled</li>
                <li>Right-click is disabled</li>
                <li>Timer runs continuously — auto-submits at zero</li>
            </ul>
            <button id="enterFullscreenBtn" style="padding:12px 32px;background:#003087;color:#fff;border:none;font-size:15px;font-weight:bold;cursor:pointer;border-radius:2px;font-family:Arial,sans-serif;width:100%;">
                Enter Exam — Go Fullscreen
            </button>
            <p style="font-size:11px;color:#475569;margin-top:12px;">Press Esc to exit fullscreen at any time (exam will continue running)</p>
        </div>
    </div>

    {{-- Tab-switch warning --}}
    <div id="tabWarningOverlay">
        <div class="tab-warning-box">
            <div class="tab-warning-icon">⚠️</div>
            <div class="tab-warning-title">Tab Switch Detected</div>
            <div class="tab-warning-body">
                You left this exam window. In the real IELTS exam this would be flagged by the proctor.
            </div>
            <div class="tab-warning-count" id="tabSwitchCount"></div>
            <button class="tab-warning-btn" onclick="dismissTabWarning()">Return to Exam</button>
        </div>
    </div>

    {{ $slot }}

    @stack('scripts')

    <script>
    (function () {
        // ── Fullscreen ──
        const prompt = document.getElementById('fullscreenPrompt');
        const enterBtn = document.getElementById('enterFullscreenBtn');

        function requestFS() {
            const el = document.documentElement;
            (el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen || function(){}).call(el);
        }

        enterBtn && enterBtn.addEventListener('click', function () {
            requestFS();
            prompt.classList.add('hidden');
        });

        // Re-prompt if fullscreen exited (user pressed Esc)
        document.addEventListener('fullscreenchange', function () {
            if (!document.fullscreenElement && prompt && prompt.classList.contains('hidden')) {
                // Just show a small toast — don't block the exam
                showFSToast();
            }
        });

        function showFSToast() {
            let toast = document.getElementById('fsToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'fsToast';
                toast.style.cssText = 'position:fixed;top:60px;right:16px;background:#CC0000;color:#fff;font-family:Arial,sans-serif;font-size:13px;padding:10px 16px;border-radius:2px;z-index:9998;cursor:pointer;';
                toast.innerHTML = '⚠ Fullscreen exited — <u>click to re-enter</u>';
                toast.onclick = function() { requestFS(); toast.remove(); };
                document.body.appendChild(toast);
                setTimeout(() => toast && toast.remove(), 6000);
            }
        }

        // ── Tab / window visibility detection ──
        let tabSwitches = 0;
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                tabSwitches++;
                try { sessionStorage.setItem('exam_tab_switches', tabSwitches); } catch(e) {}
            } else {
                if (tabSwitches > 0) showTabWarning();
            }
        });

        window.showTabWarning = function () {
            const count = document.getElementById('tabSwitchCount');
            if (count) count.textContent = 'Tab switch count: ' + tabSwitches + (tabSwitches >= 3 ? ' — This would be a serious violation in a real exam.' : '');
            document.getElementById('tabWarningOverlay').classList.add('show');
        };

        window.dismissTabWarning = function () {
            document.getElementById('tabWarningOverlay').classList.remove('show');
            requestFS();
        };

        // ── Disable copy / paste / cut ──
        ['copy','cut','paste'].forEach(function(evt) {
            document.addEventListener(evt, function(e) { e.preventDefault(); });
        });

        // ── Disable select-all, and common shortcuts ──
        document.addEventListener('keydown', function(e) {
            const blocked = (
                (e.ctrlKey || e.metaKey) && ['a','c','v','x','u','s','p'].includes(e.key.toLowerCase())
            ) || e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase()));

            if (blocked) e.preventDefault();
        });

        // Disable text selection on question panels only (not textarea/input)
        document.addEventListener('selectstart', function(e) {
            const tag = e.target.tagName.toLowerCase();
            if (tag === 'textarea' || tag === 'input') return;
            // Allow selection in passage panel for highlighting
            if (e.target.closest('#passageText') || e.target.closest('#passagePanel')) return;
            e.preventDefault();
        });

    })();
    </script>
</body>
</html>
