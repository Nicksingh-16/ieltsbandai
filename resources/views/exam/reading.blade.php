<x-exam-layout>
@php
    $totalTime    = 60 * 60;
    $passage      = $meta['passage'] ?? '';
    $passageTitle = $meta['title'] ?? $question->title;
    $allQuestions = collect($meta['questions'] ?? []);
    $matchTypes   = ['matching_item','heading_match','sentence_ending','feature_match'];
@endphp

{{-- ── Exam Header ── --}}
<div class="exam-header">
    <div class="exam-header-brand">IELTS Band AI</div>
    <div class="exam-header-title">Academic Reading Test</div>
    <div class="exam-header-timer">
        <span style="font-size:12px;color:#555;">Time Remaining</span>
        <span id="examTimer" class="exam-timer-value">01:00:00</span>
    </div>
</div>

{{-- ── Toolbar ── --}}
<div class="exam-toolbar">
    <button class="exam-tool-btn" id="highlightYellowBtn" onclick="setHighlightColor('yellow')" title="Highlight in yellow">
        <span style="width:12px;height:12px;background:#FFEF9F;border:1px solid #ccc;display:inline-block;border-radius:1px;"></span>
        Highlight
    </button>
    <button class="exam-tool-btn" id="highlightGreenBtn" onclick="setHighlightColor('green')" title="Highlight in green">
        <span style="width:12px;height:12px;background:#BDFFC7;border:1px solid #ccc;display:inline-block;border-radius:1px;"></span>
    </button>
    <button class="exam-tool-btn" id="highlightPinkBtn" onclick="setHighlightColor('pink')" title="Highlight in pink">
        <span style="width:12px;height:12px;background:#FFCECE;border:1px solid #ccc;display:inline-block;border-radius:1px;"></span>
    </button>
    <button class="exam-tool-btn" onclick="setHighlightColor(null)" title="Remove highlight">
        ✕ Remove
    </button>
    <div class="exam-tool-sep"></div>
    <button class="exam-tool-btn" onclick="toggleNotes()" title="Open scratchpad notes">
        📝 Notes
    </button>
    <div class="exam-tool-sep"></div>
    <span style="font-size:12px;color:#666;margin-left:4px;">
        <span id="answeredCount">0</span> / {{ $allQuestions->count() }} answered
    </span>
</div>

{{-- ── Main Content: Split Panel ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;height:calc(100vh - 50px - 36px - 52px);overflow:hidden;">

    {{-- Left: Passage --}}
    <div id="passagePanel" style="overflow-y:auto;padding:24px 28px;border-right:1px solid #D0D3DC;" class="no-scrollbar">
        <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">
            Reading Passage
        </div>
        <h2 style="font-size:17px;font-weight:bold;color:#1a1a1a;margin-bottom:18px;line-height:1.3;">
            {{ $passageTitle }}
        </h2>
        <div id="passageText" class="exam-passage">
            {!! nl2br(e($passage)) !!}
        </div>
    </div>

    {{-- Right: Questions --}}
    <div style="overflow-y:auto;background:#FAFBFE;" class="no-scrollbar">
        <form method="POST" action="{{ route('reading.submit', $test->id) }}" id="examReadingForm">
            @csrf
            <div style="padding:20px 24px 80px;">
                @php $renderedGroups = []; $qIdx = 0; @endphp

                @forelse($allQuestions as $q)
                @php
                    $qIdx++;
                    $type    = $q['type'] ?? 'fill';
                    $isMatch = in_array($type, $matchTypes);
                    $groupId = $isMatch ? ($q['group'] ?? 'g_' . $q['id']) : null;
                    $isFirstInGroup = $isMatch && !in_array($groupId, $renderedGroups);
                    if ($isFirstInGroup) $renderedGroups[] = $groupId;
                @endphp

                {{-- Matching group header --}}
                @if($isMatch && $isFirstInGroup)
                <div style="background:#EEF0F8;border:1px solid #C8CADC;padding:10px 14px;margin-bottom:8px;border-radius:2px;">
                    <div style="font-size:12px;font-weight:bold;color:#003087;margin-bottom:6px;">
                        {{ $q['group_question'] ?? 'Match each item to the correct option.' }}
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($q['options'] ?? [] as $opt)
                        <span style="padding:3px 10px;background:#fff;border:1px solid #C8CADC;font-size:12px;border-radius:2px;">{{ $opt }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Question block --}}
                <div class="exam-question-block" id="qblock-{{ $q['id'] }}">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <span class="exam-q-number" id="qnum-{{ $q['id'] }}">{{ $qIdx }}</span>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <p style="font-size:13.5px;color:#1a1a1a;line-height:1.55;margin:0;">
                                    {{ $q['question'] ?? $q['stem'] ?? '' }}
                                </p>
                                <button type="button" class="exam-flag-btn" id="flag-{{ $q['id'] }}"
                                    onclick="toggleFlag('{{ $q['id'] }}')" title="Flag for review">
                                    ⚑
                                </button>
                            </div>

                            {{-- ── Matching ── --}}
                            @if($isMatch)
                            <select name="answers[{{ $q['id'] }}]" class="exam-select" style="width:100%;max-width:320px;"
                                onchange="markAnswered('{{ $q['id'] }}')">
                                <option value="">— Select answer —</option>
                                @foreach($q['options'] ?? [] as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>

                            {{-- ── TFNG ── --}}
                            @elseif($type === 'tfng')
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach(['True','False','Not Given'] as $opt)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}"
                                        style="display:none;" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span class="exam-tfng-pill" data-qid="{{ $q['id'] }}" data-val="{{ $opt }}"
                                        style="display:inline-block;padding:5px 14px;border:1px solid #B0B3BC;font-size:13px;cursor:pointer;border-radius:2px;background:#fff;transition:all .15s;"
                                        onclick="selectPill(this)">
                                        {{ $opt }}
                                    </span>
                                </label>
                                @endforeach
                            </div>

                            {{-- ── YNGNG ── --}}
                            @elseif($type === 'yngng')
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach(['Yes','No','Not Given'] as $opt)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}"
                                        style="display:none;" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span class="exam-tfng-pill" data-qid="{{ $q['id'] }}" data-val="{{ $opt }}"
                                        style="display:inline-block;padding:5px 14px;border:1px solid #B0B3BC;font-size:13px;cursor:pointer;border-radius:2px;background:#fff;transition:all .15s;"
                                        onclick="selectPill(this)">
                                        {{ $opt }}
                                    </span>
                                </label>
                                @endforeach
                            </div>

                            {{-- ── MCQ single ── --}}
                            @elseif($type === 'mcq')
                            <div style="display:flex;flex-direction:column;gap:2px;">
                                @foreach($q['options'] ?? [] as $key => $opt)
                                <label class="exam-option">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}"
                                        onchange="markAnswered('{{ $q['id'] }}')">
                                    <span style="font-size:13px;">
                                        <strong>{{ chr(65 + $loop->index) }}.</strong> {{ $opt }}
                                    </span>
                                </label>
                                @endforeach
                            </div>

                            {{-- ── MCQ multi ── --}}
                            @elseif($type === 'mcq_multi')
                            <div style="font-size:12px;color:#7B4F00;background:#FFF3CD;border:1px solid #FFDDA0;padding:4px 10px;border-radius:2px;margin-bottom:8px;">
                                Choose {{ count($q['answers'] ?? []) }} answers.
                            </div>
                            <div style="display:flex;flex-direction:column;gap:2px;">
                                @foreach($q['options'] ?? [] as $opt)
                                <label class="exam-option">
                                    <input type="checkbox" name="answers[{{ $q['id'] }}][]" value="{{ $opt }}"
                                        onchange="markAnswered('{{ $q['id'] }}')">
                                    <span style="font-size:13px;">{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

                            {{-- ── Diagram label ── --}}
                            @elseif($type === 'diagram_label')
                            @if(!empty($q['description']))
                            <p style="font-size:12px;color:#555;font-style:italic;margin-bottom:8px;">{{ $q['description'] }}</p>
                            @endif
                            <div style="display:flex;flex-direction:column;gap:6px;background:#F5F6FA;border:1px solid #D0D3DC;padding:12px;border-radius:2px;">
                                @foreach($q['labels'] ?? [] as $lbl)
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:20px;height:20px;background:#003087;color:#fff;font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $loop->iteration }}</span>
                                    <span style="font-size:12px;color:#555;flex:1;">{{ $lbl['hint'] ?? '' }}</span>
                                    <input type="text" name="answers[{{ $lbl['key'] }}]"
                                        class="exam-text-input" style="width:140px;"
                                        placeholder="Write answer..."
                                        onchange="markAnswered('{{ $q['id'] }}')">
                                </div>
                                @endforeach
                            </div>

                            {{-- ── Fill / completions / short answer ── --}}
                            @else
                            <input type="text" name="answers[{{ $q['id'] }}]"
                                class="exam-text-input" style="width:100%;max-width:380px;"
                                placeholder="Write your answer here..."
                                oninput="markAnswered('{{ $q['id'] }}')">
                            @endif

                        </div>
                    </div>
                </div>

                @empty
                <p style="color:#999;text-align:center;padding:40px 0;">No questions loaded.</p>
                @endforelse
            </div>
        </form>
    </div>
</div>

{{-- ── Notes Panel (slide-in) ── --}}
<div class="exam-notes-panel" id="notesPanel">
    <div class="exam-notes-header">
        <span>📝 Scratchpad Notes</span>
        <button onclick="toggleNotes()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;line-height:1;">✕</button>
    </div>
    <textarea class="exam-notes-textarea" id="notesText" placeholder="Use this space for notes during the exam. Notes are not submitted."></textarea>
</div>

{{-- ── Question Navigator (bottom) ── --}}
<div class="exam-nav-panel">
    <div class="exam-nav-legend">
        <div class="exam-nav-legend-item"><div class="legend-dot answered"></div> Answered</div>
        <div class="exam-nav-legend-item"><div class="legend-dot flagged"></div> Flagged</div>
        <div class="exam-nav-legend-item"><div class="legend-dot empty"></div> Not answered</div>
    </div>
    <div class="exam-nav-grid" id="navGrid">
        @foreach($allQuestions as $i => $q)
        <div class="exam-nav-dot" id="nav-{{ $q['id'] }}" onclick="scrollToQuestion('{{ $q['id'] }}')" title="Question {{ $i + 1 }}">
            {{ $i + 1 }}
        </div>
        @endforeach
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
        <button type="button" class="exam-submit-btn" onclick="confirmSubmit()">
            Submit Test →
        </button>
    </div>
</div>

{{-- ── Submit Confirmation Modal ── --}}
<div id="submitModal" class="exam-modal-overlay" style="display:none;">
    <div class="exam-modal">
        <div class="exam-modal-title">Submit Reading Test</div>
        <div class="exam-modal-body" id="submitModalBody">
            Are you sure you want to submit? <strong>This cannot be undone.</strong><br><br>
            Answered: <strong id="modalAnswered">0</strong> / {{ $allQuestions->count() }}
        </div>
        <div class="exam-modal-actions">
            <button class="exam-modal-cancel" onclick="document.getElementById('submitModal').style.display='none'">
                Return to Test
            </button>
            <button class="exam-modal-confirm" onclick="document.getElementById('examReadingForm').submit()">
                Submit Now
            </button>
        </div>
    </div>
</div>

{{-- ── Time Warning Modal ── --}}
<div id="warningModal" class="exam-modal-overlay" style="display:none;">
    <div class="exam-modal">
        <div class="exam-modal-title" id="warningTitle">⚠ Time Warning</div>
        <div class="exam-modal-body" id="warningBody"></div>
        <div class="exam-modal-actions">
            <button class="exam-modal-confirm" onclick="document.getElementById('warningModal').style.display='none'">
                Continue
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Timer ──
    let totalSeconds = {{ $totalTime }};
    let warned10 = false, warned5 = false;
    const timerEl = document.getElementById('examTimer');

    function formatTime(s) {
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
    }

    const timerInterval = setInterval(function () {
        totalSeconds--;
        timerEl.textContent = formatTime(totalSeconds);

        if (totalSeconds <= 600 && !warned10) {
            warned10 = true;
            timerEl.className = 'exam-timer-value warning';
            showWarning('10 Minutes Remaining', 'You have <strong>10 minutes</strong> left. Begin reviewing your answers.');
        }
        if (totalSeconds <= 300 && !warned5) {
            warned5 = true;
            timerEl.className = 'exam-timer-value danger';
            showWarning('5 Minutes Remaining', 'You have <strong>5 minutes</strong> left. Make sure all questions are answered.');
        }
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            document.getElementById('examReadingForm').submit();
        }
    }, 1000);

    // ── Highlight Tool ──
    let currentHighlightColor = null;
    const passageText = document.getElementById('passageText');

    window.setHighlightColor = function(color) {
        currentHighlightColor = color;
        document.querySelectorAll('.exam-tool-btn').forEach(b => b.classList.remove('active'));
        if (color) {
            const btn = document.getElementById('highlight' + color.charAt(0).toUpperCase() + color.slice(1) + 'Btn');
            if (btn) btn.classList.add('active');
        }
    };

    passageText.addEventListener('mouseup', function () {
        if (!currentHighlightColor) return;
        const selection = window.getSelection();
        if (!selection || !selection.rangeCount || selection.toString().trim() === '') return;

        const range = selection.getRangeAt(0);
        // Ensure selection is within passage
        if (!passageText.contains(range.commonAncestorContainer)) return;

        const mark = document.createElement('mark');
        mark.className = 'highlight-' + currentHighlightColor;
        mark.dataset.color = currentHighlightColor;

        try {
            range.surroundContents(mark);
            selection.removeAllRanges();
            saveHighlights();
        } catch (e) {
            // Selection spans multiple elements — skip
        }
    });

    // Click to remove highlight
    passageText.addEventListener('click', function (e) {
        if (e.target.tagName === 'MARK' && currentHighlightColor === null) {
            const parent = e.target.parentNode;
            while (e.target.firstChild) parent.insertBefore(e.target.firstChild, e.target);
            parent.removeChild(e.target);
            saveHighlights();
        }
    });

    function saveHighlights() {
        try {
            localStorage.setItem('exam_highlights_{{ $test->id }}', passageText.innerHTML);
        } catch(e) {}
    }

    // Restore highlights on load
    try {
        const saved = localStorage.getItem('exam_highlights_{{ $test->id }}');
        if (saved) passageText.innerHTML = saved;
    } catch(e) {}

    // ── Notes ──
    window.toggleNotes = function() {
        const panel = document.getElementById('notesPanel');
        panel.classList.toggle('open');
    };
    const notesText = document.getElementById('notesText');
    try {
        notesText.value = localStorage.getItem('exam_notes_{{ $test->id }}') || '';
    } catch(e) {}
    notesText.addEventListener('input', function() {
        try { localStorage.setItem('exam_notes_{{ $test->id }}', notesText.value); } catch(e) {}
    });

    // ── Question Flagging ──
    const flagged = {};
    window.toggleFlag = function(qid) {
        flagged[qid] = !flagged[qid];
        const btn = document.getElementById('flag-' + qid);
        const navDot = document.getElementById('nav-' + qid);
        if (flagged[qid]) {
            btn && btn.classList.add('flagged');
            navDot && navDot.classList.add('flagged');
        } else {
            btn && btn.classList.remove('flagged');
            navDot && navDot.classList.remove('flagged');
            // Restore answered state if applicable
            if (answered[qid]) navDot && navDot.classList.add('answered');
        }
    };

    // ── Answer Tracking ──
    const answered = {};
    window.markAnswered = function(qid) {
        answered[qid] = true;
        const navDot = document.getElementById('nav-' + qid);
        if (navDot && !flagged[qid]) navDot.classList.add('answered');
        updateAnsweredCount();
    };

    function updateAnsweredCount() {
        const count = Object.values(answered).filter(Boolean).length;
        document.getElementById('answeredCount').textContent = count;
        return count;
    }

    // ── Pill Selection (TFNG / YNGNG) ──
    window.selectPill = function(el) {
        const qid = el.dataset.qid;
        const val = el.dataset.val;
        // Deselect all pills for this question
        document.querySelectorAll('.exam-tfng-pill[data-qid="' + qid + '"]').forEach(p => {
            p.style.background = '#fff';
            p.style.color = '#1a1a1a';
            p.style.borderColor = '#B0B3BC';
        });
        // Select clicked pill
        el.style.background = '#003087';
        el.style.color = '#fff';
        el.style.borderColor = '#003087';
        // Check the hidden radio
        const radio = document.querySelector('input[name="answers[' + qid + ']"][value="' + val + '"]');
        if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
    };

    // Track all inputs
    document.getElementById('examReadingForm').addEventListener('input', function(e) {
        const name = e.target.name;
        if (!name) return;
        const match = name.match(/answers\[([^\]]+)\]/);
        if (match) markAnswered(match[1]);
    });
    document.getElementById('examReadingForm').addEventListener('change', function(e) {
        const name = e.target.name;
        if (!name) return;
        const match = name.match(/answers\[([^\]]+)\]/);
        if (match) markAnswered(match[1]);
    });

    // ── Question Navigator ──
    window.scrollToQuestion = function(qid) {
        const block = document.getElementById('qblock-' + qid);
        if (block) {
            block.scrollIntoView({ behavior: 'smooth', block: 'start' });
            block.style.background = '#FFF9E0';
            setTimeout(() => block.style.background = '', 1200);
        }
    };

    // ── Submit Confirm ──
    window.confirmSubmit = function() {
        const count = updateAnsweredCount();
        document.getElementById('modalAnswered').textContent = count;
        document.getElementById('submitModal').style.display = 'flex';
    };

    // ── Time Warning ──
    function showWarning(title, body) {
        document.getElementById('warningTitle').innerHTML = '⚠ ' + title;
        document.getElementById('warningBody').innerHTML = body;
        document.getElementById('warningModal').style.display = 'flex';
    }
});
</script>
@endpush

</x-exam-layout>
