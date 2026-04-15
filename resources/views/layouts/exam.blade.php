<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $examTitle ?? 'IELTS Exam' }} — IELTS Band AI</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Exam Mode Base ── */
        body {
            background: #ffffff;
            color: #1a1a1a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── Exam Header ── */
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
        .exam-header-brand {
            font-size: 11px;
            font-weight: bold;
            color: #003087;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            min-width: 200px;
        }
        .exam-header-title {
            flex: 1;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
        }
        .exam-header-timer {
            min-width: 200px;
            text-align: right;
            font-size: 13px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }
        .exam-timer-value {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            color: #003087;
            letter-spacing: 0.05em;
        }
        .exam-timer-value.warning { color: #CC7700; }
        .exam-timer-value.danger  { color: #CC0000; animation: pulse 1s infinite; }

        /* ── Toolbar ── */
        .exam-toolbar {
            height: 36px;
            background: #F0F2F7;
            border-bottom: 1px solid #D0D3DC;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 8px;
        }
        .exam-tool-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            font-size: 12px;
            border: 1px solid #B0B3BC;
            background: #ffffff;
            color: #333;
            cursor: pointer;
            border-radius: 2px;
            font-family: Arial, sans-serif;
            transition: background 0.15s;
            user-select: none;
        }
        .exam-tool-btn:hover { background: #E0E3EC; }
        .exam-tool-btn.active { background: #003087; color: #fff; border-color: #003087; }
        .exam-tool-sep { width: 1px; height: 20px; background: #C0C3CC; margin: 0 4px; }

        /* Highlight colors */
        .highlight-yellow { background: #FFEF9F; cursor: pointer; }
        .highlight-green  { background: #BDFFC7; cursor: pointer; }
        .highlight-pink   { background: #FFCECE; cursor: pointer; }

        /* ── Passage Text ── */
        .exam-passage {
            font-size: 13.5px;
            line-height: 1.75;
            color: #1a1a1a;
        }
        .exam-passage p { margin-bottom: 0.9em; }

        /* ── Question styles ── */
        .exam-question-block {
            border-bottom: 1px solid #E4E6EF;
            padding: 14px 0;
        }
        .exam-q-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #003087;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .exam-q-number.answered { background: #003087; }
        .exam-q-number.flagged  { background: #E07B00; }
        .exam-flag-btn {
            border: none;
            background: none;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            padding: 2px 4px;
            line-height: 1;
            transition: color 0.15s;
        }
        .exam-flag-btn:hover { color: #E07B00; }
        .exam-flag-btn.flagged { color: #E07B00; }

        /* Radio/Checkbox options */
        .exam-option {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 2px;
        }
        .exam-option:hover { background: #F0F2F7; }
        .exam-option input[type="radio"],
        .exam-option input[type="checkbox"] {
            margin-top: 2px;
            accent-color: #003087;
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }
        .exam-text-input {
            border: 1px solid #B0B3BC;
            padding: 5px 8px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            border-radius: 2px;
            outline: none;
            transition: border-color 0.15s;
        }
        .exam-text-input:focus { border-color: #003087; box-shadow: 0 0 0 2px rgba(0,48,135,0.12); }

        .exam-select {
            border: 1px solid #B0B3BC;
            padding: 5px 8px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            border-radius: 2px;
            background: #fff;
            outline: none;
        }
        .exam-select:focus { border-color: #003087; }

        /* ── Question Navigator (bottom panel) ── */
        .exam-nav-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #F0F2F7;
            border-top: 2px solid #003087;
            z-index: 40;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .exam-nav-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            flex: 1;
        }
        .exam-nav-dot {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #B0B3BC;
            background: #fff;
            color: #555;
            cursor: pointer;
            border-radius: 2px;
            transition: all 0.1s;
            user-select: none;
        }
        .exam-nav-dot:hover     { border-color: #003087; color: #003087; }
        .exam-nav-dot.answered  { background: #003087; color: #fff; border-color: #003087; }
        .exam-nav-dot.flagged   { background: #E07B00; color: #fff; border-color: #E07B00; }
        .exam-nav-dot.current   { outline: 2px solid #003087; outline-offset: 1px; }

        .exam-nav-legend {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 11px;
            color: #555;
            min-width: 120px;
        }
        .exam-nav-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 2px;
            border: 1px solid #B0B3BC;
        }
        .legend-dot.answered { background: #003087; border-color: #003087; }
        .legend-dot.flagged  { background: #E07B00; border-color: #E07B00; }
        .legend-dot.empty    { background: #fff; }

        /* ── Notes Panel ── */
        .exam-notes-panel {
            position: fixed;
            right: 0;
            top: 50px;
            width: 280px;
            bottom: 60px;
            background: #FFFEF0;
            border-left: 2px solid #003087;
            z-index: 45;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.2s ease;
        }
        .exam-notes-panel.open { transform: translateX(0); }
        .exam-notes-header {
            padding: 10px 14px;
            background: #003087;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .exam-notes-textarea {
            flex: 1;
            padding: 12px;
            border: none;
            resize: none;
            font-size: 13px;
            font-family: Arial, sans-serif;
            background: #FFFEF0;
            outline: none;
            color: #1a1a1a;
        }

        /* ── Submit Button ── */
        .exam-submit-btn {
            padding: 7px 20px;
            background: #003087;
            color: #fff;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            font-family: Arial, sans-serif;
            border-radius: 2px;
            transition: background 0.15s;
            white-space: nowrap;
        }
        .exam-submit-btn:hover { background: #002060; }

        /* ── Warning Modal ── */
        .exam-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .exam-modal {
            background: #fff;
            border: 2px solid #003087;
            border-radius: 2px;
            padding: 24px 28px;
            max-width: 440px;
            width: 90%;
        }
        .exam-modal-title {
            font-size: 16px;
            font-weight: bold;
            color: #003087;
            margin-bottom: 12px;
        }
        .exam-modal-body {
            font-size: 13px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .exam-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .exam-modal-cancel {
            padding: 6px 16px;
            border: 1px solid #B0B3BC;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
            border-radius: 2px;
            font-family: Arial, sans-serif;
        }
        .exam-modal-cancel:hover { background: #F0F2F7; }
        .exam-modal-confirm {
            padding: 6px 16px;
            background: #003087;
            color: #fff;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 2px;
            font-family: Arial, sans-serif;
        }
        .exam-modal-confirm:hover { background: #002060; }

        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }

        /* No-scrollbar utility */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Section header */
        .exam-section-header {
            background: #E8EAF0;
            border: 1px solid #C8CADC;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: bold;
            color: #003087;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
    </style>

    @stack('head')
</head>
<body class="h-full">
    {{ $slot }}
    @stack('scripts')
</body>
</html>
