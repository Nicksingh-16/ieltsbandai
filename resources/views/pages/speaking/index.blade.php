<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- Progress Bar -->
        <div class="fixed top-0 left-0 right-0 h-1 bg-gray-200 z-50">
            <div id="progress-bar" class="h-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" style="width: 0%"></div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pt-12">
            <!-- Header with Back Button -->
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="font-medium">Back to Dashboard</span>
                </a>
                <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-gray-700">Part <span id="current-part">1</span> of 3</span>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden mb-8">
                <!-- Question Section -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 sm:p-10">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-white/80 text-sm font-medium mb-2">Question</h2>
                            <p id="question-box" class="text-white text-xl sm:text-2xl font-semibold leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                <!-- Timer Section -->
                <div class="bg-gradient-to-b from-gray-50 to-white p-8">
                    <div class="flex flex-col items-center mb-8">
                        <p class="text-gray-500 text-sm font-medium mb-3">Time Remaining</p>
                        <div class="relative">
                            <!-- Circular Progress -->
                            <svg class="transform -rotate-90 w-40 h-40">
                                <circle cx="80" cy="80" r="70" stroke="#E5E7EB" stroke-width="8" fill="none"/>
                                <circle id="timer-circle" cx="80" cy="80" r="70" stroke="url(#gradient)" stroke-width="8" fill="none" 
                                    stroke-dasharray="439.6" stroke-dashoffset="0" 
                                    class="transition-all duration-1000 ease-linear"/>
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#A855F7;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div id="timer" class="text-5xl font-bold text-gray-900 mb-1"></div>
                                    <div class="text-xs text-gray-500 font-medium">seconds</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recording Button -->
                    <div class="flex flex-col items-center mb-8">
                        <button id="recordBtn" 
                            class="group relative bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                            <svg id="mic-icon" class="w-16 h-16 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                            <div id="recording-pulse" class="hidden absolute inset-0 rounded-full border-4 border-red-500 animate-ping"></div>
                        </button>
                        <p id="record-status" class="mt-6 text-lg font-semibold text-gray-700">
                            Tap to start recording
                        </p>
                        <div id="recording-indicator" class="hidden flex items-center gap-2 mt-4 bg-red-50 px-4 py-2 rounded-full">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-red-700 font-medium">Recording in progress</span>
                        </div>
                    </div>

                    <!-- Hint Box -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-indigo-500 rounded-xl p-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-indigo-900 mb-2">💡 Pro Tip</h4>
                                <p class="text-gray-700 text-sm leading-relaxed">Speak naturally and clearly. Try to cover all points mentioned in the question. Use varied vocabulary and proper grammar to achieve a higher band score.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Button -->
            <div class="flex justify-center">
                <button id="nextBtn" 
                    class="hidden group bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 active:scale-95">
                    <span class="flex items-center gap-2">
                        Continue to Next Part
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </span>
                </button>
            </div>
        </div>

        <!-- Hidden Input -->
        <input type="hidden" id="questionsData" value='@json($questions)'>
    </div>

    <script>
        try {
            const data = JSON.parse(document.getElementById("questionsData").value);
            const questions = [data.part1, data.part2, data.part3];
            const durations = [60, 120, 90];
            let index = 0;
            let uploadCount = 0;

            const qBox = document.getElementById("question-box");
            const timerEl = document.getElementById("timer");
            const timerCircle = document.getElementById("timer-circle");
            const nextBtn = document.getElementById("nextBtn");
            const recordBtn = document.getElementById("recordBtn");
            const recordStatus = document.getElementById("record-status");
            const recordingIndicator = document.getElementById("recording-indicator");
            const recordingPulse = document.getElementById("recording-pulse");
            const micIcon = document.getElementById("mic-icon");
            const progressBar = document.getElementById("progress-bar");
            const currentPart = document.getElementById("current-part");

            if (!qBox || !timerEl || !nextBtn || !recordBtn) {
                console.error('Required elements not found');
                alert('Page elements not loaded correctly. Please refresh.');
            }

            let interval;
            let timeLeft;
            let initialTime;
            let mediaRecorder, audioChunks = [], startTime;
            let isRecording = false;
            let mediaStream = null;

            function loadQuestion() {
                if (index >= questions.length) {
                    console.error('Question index out of bounds');
                    return;
                }
                
                if (!questions[index] || !questions[index].title) {
                    console.error('Question data missing for index:', index);
                    alert('Question data is missing. Please refresh the page.');
                    return;
                }
                
                qBox.innerText = questions[index].title;
                timeLeft = durations[index];
                initialTime = timeLeft;
                timerEl.innerText = format(timeLeft);
                nextBtn.classList.add("hidden");
                recordBtn.disabled = false;
                recordStatus.innerText = "Tap to start recording";
                recordBtn.className = "group relative bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95";
                isRecording = false;
                audioChunks = [];
                recordingIndicator.classList.add("hidden");
                recordingPulse.classList.add("hidden");
                currentPart.innerText = index + 1;
                progressBar.style.width = ((index) / 3 * 100) + "%";
                
                // Reset timer circle
                timerCircle.style.strokeDashoffset = "0";
            }

            function format(sec) {
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                return `${m}:${s}`;
            }

            function updateTimerCircle() {
                const circumference = 439.6;
                const progress = (timeLeft / initialTime);
                const offset = circumference * (1 - progress);
                timerCircle.style.strokeDashoffset = offset;
            }

            recordBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                
                if (recordBtn.disabled) return;
                
                if (!isRecording) {
                    try {
                        recordBtn.disabled = true;
                        recordStatus.innerText = "Requesting microphone...";
                        
                        mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        
                        const options = { mimeType: 'audio/webm;codecs=opus' };
                        if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                            mediaRecorder = new MediaRecorder(mediaStream);
                        } else {
                            mediaRecorder = new MediaRecorder(mediaStream, options);
                        }
                        
                        audioChunks = [];
                        
                        mediaRecorder.ondataavailable = e => {
                            if (e.data.size > 0) audioChunks.push(e.data);
                        };
                        
                        mediaRecorder.onstop = uploadAudio;

                        mediaRecorder.start();
                        startTime = Date.now();
                        isRecording = true;

                        recordBtn.disabled = false;
                        recordStatus.innerText = "Tap to stop recording";
                        recordBtn.className = "group relative bg-gray-700 hover:bg-gray-800 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95";
                        recordingIndicator.classList.remove("hidden");
                        recordingPulse.classList.remove("hidden");
                        
                        // Change icon to stop
                        micIcon.innerHTML = '<rect x="6" y="6" width="12" height="12" rx="2"/>';

                        interval = setInterval(() => {
                            timeLeft--;
                            timerEl.innerText = format(timeLeft);
                            updateTimerCircle();
                            if (timeLeft <= 0) stopRecording();
                        }, 1000);
                    } catch (error) {
                        console.error('Error accessing microphone:', error);
                        recordBtn.disabled = false;
                        recordStatus.innerText = "Tap to start recording";
                        recordBtn.className = "group relative bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95";
                        alert('Could not access microphone. Please check permissions and try again.');
                    }
                } else {
                    stopRecording();
                }
            });

            function stopRecording() {
                clearInterval(interval);
                if (mediaRecorder && mediaRecorder.state !== "inactive") {
                    mediaRecorder.stop();
                }
                if (mediaStream) {
                    mediaStream.getTracks().forEach(track => track.stop());
                    mediaStream = null;
                }
                isRecording = false;
                recordBtn.disabled = true;
                recordStatus.innerText = "Processing your response...";
                recordingIndicator.classList.add("hidden");
                recordingPulse.classList.add("hidden");
            }

            async function uploadAudio() {
                if (audioChunks.length === 0) {
                    recordBtn.disabled = false;
                    recordStatus.innerText = "Tap to start recording";
                    recordBtn.className = "group relative bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95";
                    alert('No audio recorded. Please try again.');
                    return;
                }

                recordStatus.innerText = "Uploading your response...";
                
                try {
                    const webmBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const duration = Math.round((Date.now() - startTime) / 1000);
                    const formData = new FormData();
                    formData.append("audio", webmBlob, "recording.webm");
                    formData.append("duration", duration);
                    formData.append("test_id", "{{ $test->id }}");

                    const response = await fetch("{{ route('speaking.upload.audio') }}", {
                        method: "POST",
                        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: formData
                    });

                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        const text = await response.text();
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    }

                    const data = await response.json();

                    if (data.success) {
                        uploadCount++;
                        
                        if (data.is_last || uploadCount >= 3) {
                            recordStatus.innerText = "Complete! Redirecting to results...";
                            progressBar.style.width = "100%";
                            setTimeout(() => {
                                window.location.href = "{{ route('test.result', $test->id) }}";
                            }, 1500);
                        } else if (data.next) {
                            nextBtn.classList.remove("hidden");
                            recordBtn.disabled = true;
                            recordStatus.innerText = "✅ Response recorded successfully!";
                            recordBtn.className = "group relative bg-green-500 text-white rounded-full p-8 shadow-2xl cursor-not-allowed";
                            micIcon.innerHTML = '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                            progressBar.style.width = ((index + 1) / 3 * 100) + "%";
                        }
                    } else {
                        throw new Error(data.error || 'Upload failed');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    recordBtn.disabled = false;
                    recordStatus.innerText = "Tap to start recording";
                    recordBtn.className = "group relative bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-8 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 active:scale-95";
                    alert('Failed to upload audio: ' + (error.message || 'Please try again.'));
                }
            }

            nextBtn.addEventListener("click", () => {
                index++;
                if (index >= 3) {
                    window.location.href = "{{ route('test.result', $test->id) }}";
                    return;
                }
                loadQuestion();
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadQuestion);
            } else {
                loadQuestion();
            }
        } catch (error) {
            console.error('Initialization error:', error);
            alert('Error loading page: ' + error.message + '. Please refresh.');
        }
    </script>
</x-app-layout>