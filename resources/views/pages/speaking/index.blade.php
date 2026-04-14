<x-app-layout>
<div class="min-h-screen bg-surface-950">

    {{-- Progress bar --}}
    <div class="fixed top-0 left-0 right-0 h-0.5 bg-surface-700 z-50">
        <div id="progress-bar" class="h-full bg-gradient-to-r from-brand-500 to-brand-400 transition-all duration-500 shadow-glow" style="width:0%"></div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8 pt-10">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}"
               class="btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
            <div class="flex items-center gap-2 bg-surface-800 border border-surface-600 px-4 py-2 rounded-full">
                <div class="w-2 h-2 bg-brand-400 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-surface-200">Part <span id="current-part">1</span> of 3</span>
            </div>
        </div>

        {{-- Main card --}}
        <div class="card overflow-hidden mb-6">

            {{-- Question area --}}
            <div class="bg-gradient-to-br from-brand-700/40 to-brand-900/60 border-b border-brand-700/40 p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand-500/20 border border-brand-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-brand-300 text-xs font-semibold uppercase tracking-wider mb-2">Question</p>
                        <p id="question-box" class="text-surface-50 text-lg sm:text-xl font-medium leading-relaxed"></p>
                    </div>
                </div>
            </div>

            {{-- Timer + Controls --}}
            <div class="p-6 sm:p-8 flex flex-col items-center gap-8">

                {{-- Timer circle --}}
                <div class="relative">
                    <svg class="w-36 h-36" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#1e293b" stroke-width="8"/>
                        <circle id="timer-circle" cx="60" cy="60" r="52" fill="none"
                            stroke="#06b6d4" stroke-width="8"
                            stroke-dasharray="326.7" stroke-dashoffset="0"
                            stroke-linecap="round"
                            class="progress-ring transition-all duration-1000 ease-linear"
                            style="filter:drop-shadow(0 0 6px rgba(6,182,212,0.6))"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p id="timer" class="text-3xl font-bold font-mono tabular-nums text-surface-50">00:00</p>
                            <p class="text-xs text-surface-500">remaining</p>
                        </div>
                    </div>
                </div>

                {{-- Record button --}}
                <div class="flex flex-col items-center gap-4">
                    <div class="relative">
                        <button id="recordBtn"
                            class="relative w-24 h-24 rounded-full flex items-center justify-center shadow-card-hover transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-surface-800 bg-red-600 hover:bg-red-500 active:scale-95">
                            <svg id="mic-icon" class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                            <div id="recording-pulse" class="hidden absolute inset-0 rounded-full border-4 border-red-400 animate-ping opacity-75"></div>
                        </button>
                    </div>

                    <div class="text-center">
                        <p id="record-status" class="text-sm font-semibold text-surface-200">Tap to start recording</p>
                        <div id="waveform" class="hidden items-center justify-center gap-1 mt-3 h-8">
                            <div class="wave-bar h-3"></div>
                            <div class="wave-bar h-6"></div>
                            <div class="wave-bar h-8"></div>
                            <div class="wave-bar h-5"></div>
                            <div class="wave-bar h-8"></div>
                            <div class="wave-bar h-6"></div>
                            <div class="wave-bar h-3"></div>
                        </div>
                    </div>
                </div>

                {{-- Tip --}}
                <div class="w-full flex gap-3 bg-brand-500/10 border border-brand-500/20 rounded-xl p-4">
                    <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.998.213-1.927.484-2.768A8 8 0 1012 14z"/>
                    </svg>
                    <p class="text-brand-300 text-xs leading-relaxed">Speak naturally and clearly. Cover all points in the question. Use varied vocabulary and proper grammar for a higher band score.</p>
                </div>
            </div>
        </div>

        {{-- Next / Continue button --}}
        <div class="flex justify-center">
            <button id="nextBtn"
                class="hidden btn-primary px-8 py-3 font-bold text-base">
                Continue to Next Part
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </div>
    </div>

    <input type="hidden" id="questionsData" value='@json($questions)'>
</div>

<script>
try {
    const data      = JSON.parse(document.getElementById("questionsData").value);
    const questions = [data.part1, data.part2, data.part3];
    const durations = [60, 120, 90];
    let index = 0, uploadCount = 0;

    const qBox              = document.getElementById("question-box");
    const timerEl           = document.getElementById("timer");
    const timerCircle       = document.getElementById("timer-circle");
    const nextBtn           = document.getElementById("nextBtn");
    const recordBtn         = document.getElementById("recordBtn");
    const recordStatus      = document.getElementById("record-status");
    const recordingPulse    = document.getElementById("recording-pulse");
    const micIcon           = document.getElementById("mic-icon");
    const progressBar       = document.getElementById("progress-bar");
    const currentPartEl     = document.getElementById("current-part");
    const waveform          = document.getElementById("waveform");

    let interval, timeLeft, initialTime;
    let mediaRecorder, audioChunks = [], startTime;
    let isRecording = false, mediaStream = null;
    let prepInterval = null;

    const CIRCUMFERENCE = 326.7;

    function format(sec) {
        return String(Math.floor(sec / 60)).padStart(2,'0') + ':' + String(sec % 60).padStart(2,'0');
    }

    function updateTimerCircle() {
        const offset = CIRCUMFERENCE * (1 - timeLeft / initialTime);
        timerCircle.style.strokeDashoffset = offset;
        // Turn red when < 20%
        timerCircle.style.stroke = (timeLeft / initialTime < 0.2) ? '#ef4444' : '#06b6d4';
    }

    function setRecordBtn(state) {
        const states = {
            idle: {
                bg: 'bg-red-600 hover:bg-red-500',
                icon: '<path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>',
            },
            recording: {
                bg: 'bg-surface-700 hover:bg-surface-600',
                icon: '<rect x="6" y="6" width="12" height="12" rx="2"/>',
            },
            success: {
                bg: 'bg-emerald-600 cursor-not-allowed',
                icon: '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            },
        };
        const s = states[state];
        recordBtn.className = `relative w-24 h-24 rounded-full flex items-center justify-center shadow-card-hover transition-all duration-300 focus:outline-none text-white ${s.bg}`;
        micIcon.innerHTML = s.icon;
    }

    function loadQuestion() {
        if (!questions[index]?.title) { alert('Question data missing. Please refresh.'); return; }
        qBox.textContent = questions[index].title;
        timeLeft = durations[index];
        initialTime = timeLeft;
        timerEl.textContent = format(timeLeft);
        timerCircle.style.strokeDashoffset = '0';
        timerCircle.style.stroke = '#06b6d4';
        nextBtn.classList.add("hidden");
        recordBtn.disabled = false;
        setRecordBtn('idle');
        recordStatus.textContent = "Tap to start recording";
        recordingPulse.classList.add("hidden");
        waveform.classList.add("hidden");
        waveform.classList.remove("flex");
        audioChunks = [];
        isRecording = false;
        currentPartEl.textContent = index + 1;
        progressBar.style.width = (index / 3 * 100) + '%';
    }

    recordBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        if (recordBtn.disabled) return;

        if (!isRecording) {
            try {
                recordBtn.disabled = true;
                recordStatus.textContent = "Requesting microphone...";
                mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });

                const opts = { mimeType: 'audio/webm;codecs=opus' };
                mediaRecorder = MediaRecorder.isTypeSupported(opts.mimeType)
                    ? new MediaRecorder(mediaStream, opts)
                    : new MediaRecorder(mediaStream);

                audioChunks = [];
                mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
                mediaRecorder.onstop = uploadAudio;
                mediaRecorder.start();
                startTime = Date.now();
                isRecording = true;

                recordBtn.disabled = false;
                setRecordBtn('recording');
                recordStatus.textContent = "Tap to stop recording";
                recordingPulse.classList.remove("hidden");
                waveform.classList.remove("hidden");
                waveform.classList.add("flex");

                interval = setInterval(() => {
                    timeLeft--;
                    timerEl.textContent = format(timeLeft);
                    updateTimerCircle();
                    if (timeLeft <= 0) stopRecording();
                }, 1000);
            } catch(err) {
                recordBtn.disabled = false;
                setRecordBtn('idle');
                recordStatus.textContent = "Tap to start recording";
                alert('Could not access microphone. Please check permissions.');
            }
        } else {
            stopRecording();
        }
    });

    function stopRecording() {
        clearInterval(interval);
        if (mediaRecorder?.state !== 'inactive') mediaRecorder.stop();
        mediaStream?.getTracks().forEach(t => t.stop());
        mediaStream = null;
        isRecording = false;
        recordBtn.disabled = true;
        recordStatus.textContent = "Processing your response...";
        recordingPulse.classList.add("hidden");
        waveform.classList.add("hidden");
        waveform.classList.remove("flex");
    }

    async function uploadAudio() {
        if (!audioChunks.length) {
            recordBtn.disabled = false;
            setRecordBtn('idle');
            recordStatus.textContent = "Tap to start recording";
            alert('No audio recorded. Please try again.');
            return;
        }

        recordStatus.textContent = "Uploading...";
        try {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            const fd   = new FormData();
            fd.append("audio", blob, "recording.webm");
            fd.append("duration", Math.round((Date.now() - startTime) / 1000));
            fd.append("test_id", "{{ $test->id }}");

            const res  = await fetch("{{ route('speaking.upload.audio') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: fd
            });

            if (!res.headers.get('content-type')?.includes('application/json'))
                throw new Error('Non-JSON response from server');

            const data = await res.json();
            if (data.success) {
                uploadCount++;
                if (data.is_last || uploadCount >= 3) {
                    recordStatus.textContent = "All done! Redirecting to results...";
                    progressBar.style.width = "100%";
                    setRecordBtn('success');
                    setTimeout(() => { window.location.href = "{{ route('test.result', $test->id) }}"; }, 1500);
                } else if (data.next) {
                    setRecordBtn('success');
                    recordStatus.textContent = "Response recorded!";
                    nextBtn.classList.remove("hidden");
                    progressBar.style.width = ((index + 1) / 3 * 100) + "%";
                }
            } else throw new Error(data.error || 'Upload failed');
        } catch(err) {
            recordBtn.disabled = false;
            setRecordBtn('idle');
            recordStatus.textContent = "Tap to start recording";
            alert('Upload failed: ' + (err.message || 'Please try again.'));
        }
    }

    nextBtn.addEventListener("click", () => {
        index++;
        if (index >= 3) { window.location.href = "{{ route('test.result', $test->id) }}"; return; }
        // Part 2 (index === 1) gets 60 seconds preparation time
        if (index === 1) {
            showPrepTime(loadQuestion);
        } else {
            loadQuestion();
        }
    });

    function showPrepTime(onDone) {
        let prepLeft = 60;
        recordBtn.disabled = true;
        nextBtn.classList.add("hidden");
        qBox.textContent = questions[1]?.title ?? '';
        timerEl.textContent = format(prepLeft);
        timerCircle.style.stroke = '#f59e0b'; // amber during prep
        timerCircle.style.strokeDashoffset = '0';
        recordStatus.textContent = "Prepare your answer — recording starts automatically";

        // Show prep banner
        const banner = document.createElement('div');
        banner.id = 'prep-banner';
        banner.className = 'w-full text-center py-2 px-4 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-semibold mt-2';
        banner.textContent = 'Part 2 — 1 minute preparation. Make notes. Recording begins when timer ends.';
        currentPartEl.textContent = '2 (Prep)';
        recordBtn.parentElement.parentElement.appendChild(banner);

        initialTime = prepLeft;
        prepInterval = setInterval(() => {
            prepLeft--;
            timerEl.textContent = format(prepLeft);
            const offset = CIRCUMFERENCE * (1 - prepLeft / 60);
            timerCircle.style.strokeDashoffset = offset;
            if (prepLeft <= 0) {
                clearInterval(prepInterval);
                banner.remove();
                onDone();
            }
        }, 1000);
    }

    loadQuestion();
} catch(err) {
    alert('Page load error: ' + err.message + '. Please refresh.');
}
</script>
</x-app-layout>
