<x-exam-layout>
@php
    $minWords  = $question->min_words ?? ($task === 'task1' ? 150 : 250);
    $totalTime = $question->time_limit ?? ($task === 'task1' ? 1200 : 2400);
    $meta      = is_string($question->metadata ?? null) ? json_decode($question->metadata, true) : ($question->metadata ?? []);
    $chartType = $meta['chart_type'] ?? null;
    $taskLabel = $task === 'task1' ? 'Task 1' : 'Task 2';
    $typeLabel = ucfirst($testType);
@endphp

@include('partials.mock-session-bar')

{{-- ── Exam Header ── --}}
<div class="exam-header">
    <div class="exam-header-brand">IELTS Band AI</div>
    <div class="exam-header-title">{{ $typeLabel }} Writing Test — {{ $taskLabel }}</div>
    <div class="exam-header-timer">
        <span style="font-size:12px;color:#555;">Time Remaining</span>
        <span id="examTimer" class="exam-timer-value">{{ gmdate('i:s', $totalTime) }}</span>
    </div>
</div>

{{-- ── Toolbar ── --}}
<div class="exam-toolbar">
    <span style="font-size:12px;color:#333;">
        Minimum words: <strong>{{ $minWords }}</strong>
    </span>
    <div class="exam-tool-sep"></div>
    <span style="font-size:12px;color:#666;">
        Word count: <strong id="wordCount">0</strong>
        <span id="wordCountStatus" style="margin-left:6px;font-size:11px;color:#999;"></span>
    </span>
    <div class="exam-tool-sep"></div>
    <button class="exam-tool-btn" onclick="toggleNotes()">📝 Notes</button>
</div>

{{-- ── Split Panel ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;height:calc(100vh - 50px - 36px - 52px);overflow:hidden;">

    {{-- Left: Task Description ── --}}
    <div style="overflow-y:auto;padding:24px 28px;border-right:1px solid #D0D3DC;" class="no-scrollbar">

        <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">
            Writing {{ $taskLabel }}
        </div>
        <h2 style="font-size:16px;font-weight:bold;color:#1a1a1a;margin-bottom:14px;line-height:1.3;">
            {{ $question->title }}
        </h2>
        <div style="font-size:13.5px;color:#1a1a1a;line-height:1.7;white-space:pre-line;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #E0E2EE;">
            {{ $question->content }}
        </div>

        {{-- Chart / diagram for Task 1 Academic ── --}}
        @if($task === 'task1' && $testType === 'academic' && $chartType)
        <div style="background:#F5F6FA;border:1px solid #D0D3DC;border-radius:2px;padding:16px;">
            <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                {{ $meta['chart_title'] ?? 'Figure' }}
            </div>

            @if($chartType === 'process')
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                @foreach(($meta['steps'] ?? []) as $step)
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="text-align:center;width:80px;">
                        <div style="width:44px;height:44px;border-radius:50%;background:#E8EAF0;border:2px solid #003087;display:flex;align-items:center;justify-content:center;font-size:18px;margin:0 auto 4px;">
                            {{ $step['icon'] ?? '•' }}
                        </div>
                        <div style="font-size:10px;font-weight:bold;color:#333;line-height:1.2;">{{ $step['label'] }}</div>
                        <div style="font-size:9px;color:#777;">{{ $step['detail'] ?? '' }}</div>
                    </div>
                    @if(!$loop->last)
                    <div style="font-size:18px;color:#003087;">→</div>
                    @endif
                </div>
                @endforeach
            </div>

            @elseif($chartType === 'pie')
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach(($meta['segments'] ?? []) as $seg)
                <div style="padding:6px 12px;background:#fff;border:1px solid #D0D3DC;border-radius:2px;font-size:12px;">
                    <strong>{{ $seg['label'] }}</strong>: {{ $seg['value'] }}{{ isset($seg['unit']) ? $seg['unit'] : '' }}
                </div>
                @endforeach
            </div>

            @elseif(in_array($chartType, ['bar','line','table']))
            <canvas id="examChart" style="max-height:220px;"></canvas>
            @push('head')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
            @endpush
            @push('scripts')
            <script>
            (function() {
                const ctx = document.getElementById('examChart');
                if (!ctx) return;
                const meta = @json($meta);
                new Chart(ctx, {
                    type: meta.chart_type === 'table' ? 'bar' : meta.chart_type,
                    data: {
                        labels: meta.labels || [],
                        datasets: (meta.datasets || []).map((ds, i) => ({
                            label: ds.label || '',
                            data: ds.data || [],
                            backgroundColor: ['#003087','#0066CC','#4499DD','#88BBEE','#CCDDFF'][i] || '#003087',
                            borderColor: ['#002060','#004499','#3377BB','#6699CC','#99BBDD'][i] || '#002060',
                            borderWidth: 1,
                            fill: false,
                            tension: 0.4,
                        }))
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { labels: { font: { family:'Arial', size:11 } } } },
                        scales: {
                            y: { ticks: { font: { family:'Arial', size:11 } } },
                            x: { ticks: { font: { family:'Arial', size:11 } } }
                        }
                    }
                });
            })();
            </script>
            @endpush
            @endif
        </div>
        @endif

        {{-- Task tips ── --}}
        <div style="margin-top:20px;background:#EEF0F8;border-left:3px solid #003087;padding:12px 14px;">
            <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">
                Examiner Reminders
            </div>
            @if($task === 'task1')
            <ul style="font-size:12px;color:#333;margin:0;padding-left:16px;line-height:1.7;">
                <li>Write at least <strong>150 words</strong></li>
                <li>Include a clear <strong>overview</strong> of the main features</li>
                <li>Select and compare <strong>key data points</strong></li>
                <li>Do <strong>not</strong> include personal opinion</li>
            </ul>
            @else
            <ul style="font-size:12px;color:#333;margin:0;padding-left:16px;line-height:1.7;">
                <li>Write at least <strong>250 words</strong></li>
                <li>State and maintain a clear <strong>position</strong></li>
                <li>Develop ideas with <strong>examples and evidence</strong></li>
                <li>Write a proper <strong>introduction and conclusion</strong></li>
            </ul>
            @endif
        </div>
    </div>

    {{-- Right: Answer Area ── --}}
    <div style="display:flex;flex-direction:column;height:100%;background:#FAFBFE;">
        <div style="padding:14px 20px 8px;border-bottom:1px solid #E0E2EE;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12px;color:#555;">Type your answer below</span>
            <span style="font-size:11px;color:#999;">Your response is auto-saved as you type</span>
        </div>
        <textarea id="examAnswer" name="answer"
            style="flex:1;width:100%;padding:18px 20px;border:none;resize:none;font-size:14px;font-family:Arial,sans-serif;line-height:1.75;color:#1a1a1a;background:#FAFBFE;outline:none;border-bottom:1px solid #E0E2EE;"
            placeholder="Begin typing your answer here..."></textarea>
        <div style="padding:10px 20px;display:flex;align-items:center;justify-content:space-between;background:#F0F2F7;border-top:1px solid #D0D3DC;">
            <div style="font-size:12px;color:#555;">
                <span id="wordCountBar">0</span> words
                <span id="minWordBar" style="margin-left:10px;font-size:11px;"></span>
            </div>
            <button type="button" class="exam-submit-btn" onclick="confirmSubmit()">
                Submit Writing →
            </button>
        </div>
    </div>
</div>

{{-- ── Notes Panel ── --}}
<div class="exam-notes-panel" id="notesPanel">
    <div class="exam-notes-header">
        <span>📝 Scratchpad Notes</span>
        <button onclick="toggleNotes()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;line-height:1;">✕</button>
    </div>
    <textarea class="exam-notes-textarea" id="notesText" placeholder="Plan your essay here. Notes are not submitted."></textarea>
</div>

{{-- ── Submit Modal ── --}}
<div id="submitModal" class="exam-modal-overlay" style="display:none;">
    <div class="exam-modal">
        <div class="exam-modal-title">Submit Writing Test</div>
        <div class="exam-modal-body">
            Are you ready to submit your answer? <strong>This cannot be undone.</strong><br><br>
            Word count: <strong id="modalWordCount">0</strong>
            &nbsp;|&nbsp; Minimum required: <strong>{{ $minWords }}</strong>
            <div id="wordWarning" style="display:none;margin-top:12px;padding:10px 12px;background:#FDECEC;border:1px solid #F5B7B1;border-radius:2px;font-size:13px;color:#922B21;">
                <strong>⚠ Below the IELTS minimum.</strong><br>
                Real-exam responses under {{ $minWords }} words cap Task Achievement around Band 5. Edit your essay or tick the box to submit anyway.
                <label style="display:flex;align-items:flex-start;gap:8px;margin-top:10px;cursor:pointer;font-weight:normal;">
                    <input type="checkbox" id="acceptPenaltyExam" style="margin-top:3px;" onchange="document.querySelector('.exam-modal-confirm').disabled = !this.checked;">
                    <span style="font-size:12px;color:#7B2424;">I understand my band will be capped because my response is under {{ $minWords }} words.</span>
                </label>
            </div>
        </div>
        <div class="exam-modal-actions">
            <button class="exam-modal-cancel" onclick="document.getElementById('submitModal').style.display='none'">Return to Test</button>
            <button class="exam-modal-confirm" onclick="doSubmit()">Submit Now</button>
        </div>
    </div>
</div>

{{-- ── Time Warning Modal ── --}}
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

    // ── Timer ──
    let totalSeconds = {{ $totalTime }};
    let warned = false, warned5 = false;
    const timerEl = document.getElementById('examTimer');

    function formatTime(s) {
        const m = String(Math.floor(s / 60)).padStart(2,'0');
        const sec = String(s % 60).padStart(2,'0');
        return m + ':' + sec;
    }

    const timerInterval = setInterval(function () {
        totalSeconds--;
        timerEl.textContent = formatTime(totalSeconds);
        if (totalSeconds <= Math.floor({{ $totalTime }} * 0.25) && !warned) {
            warned = true;
            timerEl.className = 'exam-timer-value warning';
            showWarning('Quarter Time Remaining', 'You have used 75% of your time. Focus on completing and reviewing your answer.');
        }
        if (totalSeconds <= 300 && !warned5) {
            warned5 = true;
            timerEl.className = 'exam-timer-value danger';
            showWarning('5 Minutes Remaining', 'You have <strong>5 minutes</strong> left. Finalise your answer now.');
        }
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            // Real IELTS: time-out collects the response as-is. Force the
            // accept-penalty flag so the backend accepts an under-length
            // submission instead of bouncing it with a 422.
            const cb = document.getElementById('acceptPenaltyExam');
            if (cb) cb.checked = true;
            doSubmit();
        }
    }, 1000);

    // ── Word Counter ──
    const textarea = document.getElementById('examAnswer');
    const wordCountEl = document.getElementById('wordCount');
    const wordCountBar = document.getElementById('wordCountBar');
    const wordCountStatus = document.getElementById('wordCountStatus');
    const minWordBar = document.getElementById('minWordBar');
    const minWords = {{ $minWords }};

    function countWords(text) {
        return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    }

    function updateWordCount() {
        const count = countWords(textarea.value);
        wordCountEl.textContent = count;
        wordCountBar.textContent = count;
        if (count >= minWords) {
            wordCountStatus.textContent = '✓ Minimum met';
            wordCountStatus.style.color = '#006600';
            minWordBar.textContent = '';
        } else {
            wordCountStatus.textContent = '';
            minWordBar.textContent = (minWords - count) + ' more words needed';
            minWordBar.style.color = '#CC7700';
        }
        try { localStorage.setItem('exam_writing_{{ $test->id }}', textarea.value); } catch(e) {}
    }

    // Restore draft
    try {
        const saved = localStorage.getItem('exam_writing_{{ $test->id }}');
        if (saved) { textarea.value = saved; updateWordCount(); }
    } catch(e) {}

    textarea.addEventListener('input', updateWordCount);
    updateWordCount();

    // ── Notes ──
    window.toggleNotes = function() {
        document.getElementById('notesPanel').classList.toggle('open');
    };
    const notesText = document.getElementById('notesText');
    try { notesText.value = localStorage.getItem('exam_notes_write_{{ $test->id }}') || ''; } catch(e) {}
    notesText.addEventListener('input', function() {
        try { localStorage.setItem('exam_notes_write_{{ $test->id }}', notesText.value); } catch(e) {}
    });

    // ── Submit ──
    window.confirmSubmit = function() {
        const count = countWords(textarea.value);
        const underMin = count < minWords;
        document.getElementById('modalWordCount').textContent = count;
        document.getElementById('wordWarning').style.display = underMin ? 'block' : 'none';
        // Under-length submissions require explicit acknowledgement of the
        // band penalty — disable the confirm button until the checkbox ticks.
        const checkbox = document.getElementById('acceptPenaltyExam');
        const confirmBtn = document.querySelector('.exam-modal-confirm');
        if (checkbox) checkbox.checked = false;
        if (confirmBtn) confirmBtn.disabled = underMin;
        document.getElementById('submitModal').style.display = 'flex';
    };

    window.doSubmit = function() {
        const answer = textarea.value;
        const acceptPenalty = document.getElementById('acceptPenaltyExam')?.checked ? 1 : 0;
        const btn = document.querySelector('.exam-modal-confirm');
        if (btn) { btn.textContent = 'Submitting…'; btn.disabled = true; }

        const resetBtn = () => {
            if (btn) { btn.textContent = 'Submit Now'; btn.disabled = false; }
        };

        // Hard-cap the submit POST at 20s. The server now returns within
        // ~200ms (job runs after response), so anything past that means
        // the request is genuinely stuck — show a clear error instead of
        // letting the modal sit at "Submitting…" indefinitely.
        const ctrl = new AbortController();
        const timeoutId = setTimeout(() => ctrl.abort(), 20000);

        fetch('{{ route('writing.submit', $test->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ answer, accept_penalty: acceptPenalty }),
            signal: ctrl.signal,
        })
        .then(async r => {
            clearTimeout(timeoutId);
            // Read body as text and parse defensively. If the dev server ever
            // appends stray output after the JSON (e.g. a PHP notice with
            // APP_DEBUG=true), we still extract the leading {...} and don't
            // break the user's submission.
            const raw = await r.text();
            let data = null;
            try {
                data = JSON.parse(raw);
            } catch (_) {
                const m = raw && raw.match(/^\s*(\{[\s\S]*?\})/);
                if (m) {
                    try { data = JSON.parse(m[1]); } catch (_) { /* fall through */ }
                }
            }

            if (!r.ok) {
                // 422 validation, 429 rate limit, 5xx — surface a useful message
                const msg = (data && (data.message || (data.errors && Object.values(data.errors).flat().join(' '))))
                    || `Submission failed (HTTP ${r.status}). Please try again.`;
                resetBtn();
                alert(msg);
                return;
            }

            if (data && data.redirect) {
                try { localStorage.removeItem('exam_writing_{{ $test->id }}'); } catch(e) {}
                window.location.href = data.redirect;
                return;
            }

            // Unexpected shape — recover instead of hanging
            resetBtn();
            alert('Submission did not complete. Please try again.');
        })
        .catch(err => {
            clearTimeout(timeoutId);
            resetBtn();
            const wasTimeout = err && err.name === 'AbortError';
            alert(wasTimeout
                ? 'The server took too long to respond. Your answer is auto-saved — please try submitting again.'
                : 'Network error during submission. Please check your connection and try again.');
        });
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
