<x-exam-layout>
@php
    $totalTime    = 40 * 60;
    $allQuestions = collect($sections['questions'] ?? []);
    $sectionGroups = $allQuestions->groupBy('section');
    $sectionTitles = [
        1 => 'Section 1 — Social Conversation',
        2 => 'Section 2 — Social Monologue',
        3 => 'Section 3 — Educational Discussion',
        4 => 'Section 4 — Academic Lecture',
    ];
    $matchTypes = ['matching_item','heading_match','sentence_ending','feature_match'];
@endphp

{{-- ── Exam Header ── --}}
<div class="exam-header">
    <div class="exam-header-brand">IELTS Band AI</div>
    <div class="exam-header-title">Academic Listening Test</div>
    <div class="exam-header-timer">
        <span style="font-size:12px;color:#555;">Time Remaining</span>
        <span id="examTimer" class="exam-timer-value">40:00</span>
    </div>
</div>

{{-- ── Toolbar ── --}}
<div class="exam-toolbar">
    <span style="font-size:12px;color:#333;font-weight:bold;">
        🎧 Listen carefully — the audio plays <strong>once only</strong>.
    </span>
    <div class="exam-tool-sep"></div>
    <button class="exam-tool-btn" onclick="toggleNotes()">📝 Notes</button>
    <div class="exam-tool-sep"></div>
    <span style="font-size:12px;color:#666;margin-left:4px;">
        <span id="answeredCount">0</span> / {{ $allQuestions->count() }} answered
    </span>
</div>

{{-- ── Audio Bar (fixed below toolbar) ── --}}
<div style="background:#EEF0F8;border-bottom:1px solid #D0D3DC;padding:10px 24px;display:flex;align-items:center;gap:16px;">
    @if(!empty($sections['audio_url']))
    <audio id="mainAudio" controls style="flex:1;max-width:640px;height:36px;" controlsList="nodownload noplaybackrate">
        <source src="{{ $sections['audio_url'] }}" type="audio/mpeg">
    </audio>
    @else
    <div style="flex:1;max-width:640px;height:36px;background:#fff;border:1px solid #D0D3DC;border-radius:2px;display:flex;align-items:center;padding:0 14px;color:#999;font-size:12px;">
        🔇 Audio will load here when available
    </div>
    @endif
    <div style="display:flex;gap:6px;">
        @foreach($sectionGroups->keys() as $sNum)
        <a href="#section-{{ $sNum }}" style="padding:4px 12px;font-size:12px;font-weight:bold;background:#fff;border:1px solid #B0B3BC;color:#003087;text-decoration:none;border-radius:2px;">
            S{{ $sNum }}
        </a>
        @endforeach
    </div>
</div>

{{-- ── Questions ── --}}
<div style="height:calc(100vh - 50px - 36px - 58px - 52px);overflow-y:auto;background:#FAFBFE;" class="no-scrollbar">
    <form method="POST" action="{{ route('listening.submit', $test->id) }}" id="examListeningForm">
        @csrf
        <div style="max-width:900px;margin:0 auto;padding:20px 28px 80px;">

            @foreach($sectionGroups as $sNum => $sectionQuestions)
            <div id="section-{{ $sNum }}" style="margin-bottom:32px;">
                <div class="exam-section-header">{{ $sectionTitles[$sNum] ?? 'Section ' . $sNum }}</div>

                @php $renderedGroups = []; @endphp
                @foreach($sectionQuestions as $q)
                @php
                    $type    = $q['type'] ?? 'fill';
                    $isMatch = in_array($type, $matchTypes);
                    $groupId = $isMatch ? ($q['group'] ?? 'g_' . $q['id']) : null;
                    $isFirstInGroup = $isMatch && !in_array($groupId, $renderedGroups);
                    if ($isFirstInGroup) $renderedGroups[] = $groupId;
                    $qIdx = $allQuestions->search(fn($item) => $item['id'] === $q['id']) + 1;
                @endphp

                @if($isMatch && $isFirstInGroup)
                <div style="background:#EEF0F8;border:1px solid #C8CADC;padding:10px 14px;margin-bottom:6px;border-radius:2px;">
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

                <div class="exam-question-block" id="qblock-{{ $q['id'] }}">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <span class="exam-q-number" id="qnum-{{ $q['id'] }}">{{ $qIdx }}</span>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <p style="font-size:13.5px;color:#1a1a1a;line-height:1.55;margin:0;">
                                    {{ $q['question'] ?? $q['stem'] ?? '' }}
                                </p>
                                <button type="button" class="exam-flag-btn" id="flag-{{ $q['id'] }}"
                                    onclick="toggleFlag('{{ $q['id'] }}')" title="Flag for review">⚑</button>
                            </div>

                            @if($isMatch)
                            <select name="answers[{{ $q['id'] }}]" class="exam-select" style="width:100%;max-width:320px;"
                                onchange="markAnswered('{{ $q['id'] }}')">
                                <option value="">— Select answer —</option>
                                @foreach($q['options'] ?? [] as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>

                            @elseif($type === 'tfng')
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach(['True','False','Not Given'] as $opt)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}" style="display:none;" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span class="exam-tfng-pill" data-qid="{{ $q['id'] }}" data-val="{{ $opt }}"
                                        style="display:inline-block;padding:5px 14px;border:1px solid #B0B3BC;font-size:13px;cursor:pointer;border-radius:2px;background:#fff;transition:all .15s;"
                                        onclick="selectPill(this)">{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

                            @elseif($type === 'yngng')
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach(['Yes','No','Not Given'] as $opt)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}" style="display:none;" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span class="exam-tfng-pill" data-qid="{{ $q['id'] }}" data-val="{{ $opt }}"
                                        style="display:inline-block;padding:5px 14px;border:1px solid #B0B3BC;font-size:13px;cursor:pointer;border-radius:2px;background:#fff;transition:all .15s;"
                                        onclick="selectPill(this)">{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

                            @elseif($type === 'mcq')
                            <div style="display:flex;flex-direction:column;gap:2px;">
                                @foreach($q['options'] ?? [] as $opt)
                                <label class="exam-option">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt }}" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span style="font-size:13px;"><strong>{{ chr(65 + $loop->index) }}.</strong> {{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

                            @elseif($type === 'mcq_multi')
                            <div style="font-size:12px;color:#7B4F00;background:#FFF3CD;border:1px solid #FFDDA0;padding:4px 10px;border-radius:2px;margin-bottom:8px;">
                                Choose {{ count($q['answers'] ?? []) }} answers.
                            </div>
                            <div style="display:flex;flex-direction:column;gap:2px;">
                                @foreach($q['options'] ?? [] as $opt)
                                <label class="exam-option">
                                    <input type="checkbox" name="answers[{{ $q['id'] }}][]" value="{{ $opt }}" onchange="markAnswered('{{ $q['id'] }}')">
                                    <span style="font-size:13px;">{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

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
                                        class="exam-text-input" style="width:140px;" placeholder="Write answer..."
                                        onchange="markAnswered('{{ $q['id'] }}')">
                                </div>
                                @endforeach
                            </div>

                            @else
                            <input type="text" name="answers[{{ $q['id'] }}]"
                                class="exam-text-input" style="width:100%;max-width:380px;"
                                placeholder="Write your answer..."
                                oninput="markAnswered('{{ $q['id'] }}')">
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach

        </div>
    </form>
</div>

{{-- ── Notes Panel ── --}}
<div class="exam-notes-panel" id="notesPanel">
    <div class="exam-notes-header">
        <span>📝 Scratchpad Notes</span>
        <button onclick="toggleNotes()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;line-height:1;">✕</button>
    </div>
    <textarea class="exam-notes-textarea" id="notesText" placeholder="Use this for rough notes. Notes are not submitted."></textarea>
</div>

{{-- ── Question Navigator ── --}}
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
    <div>
        <button type="button" class="exam-submit-btn" onclick="confirmSubmit()">Submit Test →</button>
    </div>
</div>

{{-- ── Modals ── --}}
<div id="submitModal" class="exam-modal-overlay" style="display:none;">
    <div class="exam-modal">
        <div class="exam-modal-title">Submit Listening Test</div>
        <div class="exam-modal-body">
            Are you sure you want to submit? <strong>This cannot be undone.</strong><br><br>
            Answered: <strong id="modalAnswered">0</strong> / {{ $allQuestions->count() }}
        </div>
        <div class="exam-modal-actions">
            <button class="exam-modal-cancel" onclick="document.getElementById('submitModal').style.display='none'">Return to Test</button>
            <button class="exam-modal-confirm" onclick="document.getElementById('examListeningForm').submit()">Submit Now</button>
        </div>
    </div>
</div>

<div id="warningModal" class="exam-modal-overlay" style="display:none;">
    <div class="exam-modal">
        <div class="exam-modal-title" id="warningTitle">⚠ Time Warning</div>
        <div class="exam-modal-body" id="warningBody"></div>
        <div class="exam-modal-actions">
            <button class="exam-modal-confirm" onclick="document.getElementById('warningModal').style.display='none'">Continue</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Timer (MM:SS for listening) ──
    let totalSeconds = {{ $totalTime }};
    let warned10 = false, warned5 = false;
    const timerEl = document.getElementById('examTimer');

    const timerInterval = setInterval(function () {
        totalSeconds--;
        const m = String(Math.floor(totalSeconds / 60)).padStart(2,'0');
        const s = String(totalSeconds % 60).padStart(2,'0');
        timerEl.textContent = m + ':' + s;

        if (totalSeconds <= 600 && !warned10) {
            warned10 = true;
            timerEl.className = 'exam-timer-value warning';
            showWarning('10 Minutes Remaining', 'You have <strong>10 minutes</strong> left, including answer transfer time.');
        }
        if (totalSeconds <= 300 && !warned5) {
            warned5 = true;
            timerEl.className = 'exam-timer-value danger';
            showWarning('5 Minutes Remaining', 'You have <strong>5 minutes</strong> left. Finalise your answers now.');
        }
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            document.getElementById('examListeningForm').submit();
        }
    }, 1000);

    // Prevent audio replay
    const audio = document.getElementById('mainAudio');
    if (audio) {
        let hasPlayed = false;
        audio.addEventListener('play', function() { hasPlayed = true; });
        audio.addEventListener('ended', function() {
            audio.controls = false;
        });
    }

    // ── Notes ──
    window.toggleNotes = function() {
        document.getElementById('notesPanel').classList.toggle('open');
    };
    const notesText = document.getElementById('notesText');
    try { notesText.value = localStorage.getItem('exam_notes_listen_{{ $test->id }}') || ''; } catch(e) {}
    notesText.addEventListener('input', function() {
        try { localStorage.setItem('exam_notes_listen_{{ $test->id }}', notesText.value); } catch(e) {}
    });

    // ── Flagging ──
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

    // ── Pill Selection ──
    window.selectPill = function(el) {
        const qid = el.dataset.qid;
        const val = el.dataset.val;
        document.querySelectorAll('.exam-tfng-pill[data-qid="' + qid + '"]').forEach(p => {
            p.style.background = '#fff'; p.style.color = '#1a1a1a'; p.style.borderColor = '#B0B3BC';
        });
        el.style.background = '#003087'; el.style.color = '#fff'; el.style.borderColor = '#003087';
        const radio = document.querySelector('input[name="answers[' + qid + ']"][value="' + val + '"]');
        if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
    };

    document.getElementById('examListeningForm').addEventListener('change', function(e) {
        const match = (e.target.name || '').match(/answers\[([^\]]+)\]/);
        if (match) markAnswered(match[1]);
    });
    document.getElementById('examListeningForm').addEventListener('input', function(e) {
        const match = (e.target.name || '').match(/answers\[([^\]]+)\]/);
        if (match) markAnswered(match[1]);
    });

    window.scrollToQuestion = function(qid) {
        const block = document.getElementById('qblock-' + qid);
        if (block) {
            block.scrollIntoView({ behavior: 'smooth', block: 'start' });
            block.style.background = '#FFF9E0';
            setTimeout(() => block.style.background = '', 1200);
        }
    };

    window.confirmSubmit = function() {
        document.getElementById('modalAnswered').textContent = updateAnsweredCount();
        document.getElementById('submitModal').style.display = 'flex';
    };

    function showWarning(title, body) {
        document.getElementById('warningTitle').innerHTML = '⚠ ' + title;
        document.getElementById('warningBody').innerHTML = body;
        document.getElementById('warningModal').style.display = 'flex';
    }
});
</script>
@endpush

</x-exam-layout>
