<x-app-layout>
@php
    $minWords  = $question->min_words ?? ($task === 'task1' ? 150 : 250);
    $totalTime = $question->time_limit ?? ($task === 'task1' ? 1200 : 2400); // seconds
@endphp

<style>
@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}
</style>

<div class="min-h-screen bg-gray-100">

    <!-- HEADER -->
    <header class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <h1 class="font-semibold text-gray-900">
                IELTS Writing – {{ ucfirst($testType) }} Task {{ substr($task, -1) }}
            </h1>

            <!-- TIMER -->
            <div id="timer" class="font-bold text-red-600 text-lg">
                00:00
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- MAIN CONTENT (LEFT) -->
            <div class="lg:col-span-8">
                <!-- QUESTION -->
                <div class="bg-white border rounded-lg p-6 mb-6">
                    <h2 class="font-bold text-lg mb-3">{{ $question->title }}</h2>

                    <div class="whitespace-pre-line text-gray-800 leading-relaxed">
                        {{ $question->content }}
                    </div>

                    <p class="mt-4 text-sm text-gray-600">
                        <strong>Write at least {{ $minWords }} words.</strong>
                    </p>
                </div>

                <!-- WRITING FORM -->
                <form method="POST" action="{{ route('writing.submit', $test->id) }}" id="writingForm">
                    @csrf

                    <div class="bg-white border rounded-lg p-6">

                        <!-- WORD COUNT BAR -->
                        <div class="flex justify-between items-center mb-3 text-sm text-gray-700">
                            <span>
                                Word count:
                                <strong id="wordCount">0</strong>
                            </span>

                            <span id="minWordsStatus" class="text-red-600">
                                Minimum: {{ $minWords }}
                            </span>
                        </div>

                        <!-- TEXTAREA -->
                        <textarea
                            name="answer"
                            id="essayEditor"
                            required
                            class="w-full h-[520px] border border-gray-400 p-4 text-base leading-relaxed focus:outline-none focus:ring-1 focus:ring-gray-600 resize-none"
                            placeholder="Write your answer here..."
                        ></textarea>

                        @error('answer')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </form>
            </div>

            <!-- REAL-TIME ANALYZER SIDEBAR (RIGHT) -->
            <div class="lg:col-span-4">
                <div class="sticky top-20">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <h3 class="font-bold text-gray-900">Writing Analysis</h3>
                        </div>

                        <!-- STATS -->
                        <div class="space-y-4">
                            <!-- Unique Words -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Unique Words</span>
                                    <span id="uniqueWords" class="font-bold text-indigo-600">0</span>
                                </div>
                            </div>

                            <!-- Avg Sentence Length -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Avg Sentence Length</span>
                                    <span id="avgSentence" class="font-bold text-indigo-600">0</span>
                                </div>
                            </div>

                            <!-- Complex Sentences -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Complex Sentences</span>
                                    <span id="complexSentences" class="font-bold text-indigo-600">0</span>
                                </div>
                            </div>

                            <hr class="border-indigo-200">

                            <!-- Vocabulary Level -->
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-700 font-semibold">Vocabulary Level</span>
                                    <span id="vocabStatus" class="text-xs text-gray-500">Analyzing...</span>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="space-y-2">
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span>Basic</span>
                                            <span id="basicPercent">0%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div id="basicBar" class="bg-red-400 h-2 rounded-full transition-all" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span>Intermediate</span>
                                            <span id="intermediatePercent">0%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div id="intermediateBar" class="bg-yellow-400 h-2 rounded-full transition-all" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span>Advanced</span>
                                            <span id="advancedPercent">0%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div id="advancedBar" class="bg-green-500 h-2 rounded-full transition-all" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-gray-600 mt-3">
                                    💡 Aim for 40%+ advanced vocabulary for Band 7+
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SUBMIT BAR -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">

            <span class="text-sm text-gray-600">
                Make sure you review your answer before submitting.
            </span>

            <button
                id="submitBtn"
                type="submit"
                form="writingForm"
                disabled
                onclick="return confirm('You cannot edit after submission. Submit now?')"
                class="bg-blue-700 disabled:bg-gray-400 hover:bg-blue-800 text-white font-semibold px-8 py-3 rounded"
            >
                Submit Answer
            </button>
        </div>
    </div>
</div>

<!-- ================= SCRIPT SECTION ================= -->


<script>
// Task Response Checker Modal
let validationModal = null;
let validationPassed = false;

document.getElementById('writingForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    // If validation already passed, proceed with submission
    if (validationPassed) {
        submitForm();
        return;
    }

    // Show validation modal
    showValidationModal();
});

function showValidationModal() {
    const editor = document.getElementById('essayEditor');
    const text = editor.value.trim();
    const minWords = {{ $minWords }};
    const task = '{{ $task }}';
    
    // Perform checks
    const checks = performTaskChecks(text, minWords, task);
    
    // Create modal HTML
    const modalHTML = `
        <div id="validationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 transform animate-scale-in">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Pre-Submission Check</h3>
                        <p class="text-sm text-gray-600">Let's make sure your essay is ready</p>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    ${checks.map(check => `
                        <div class="flex items-start gap-3 p-4 rounded-lg ${check.passed ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}">
                            <div class="flex-shrink-0 mt-0.5">
                                ${check.passed 
                                    ? '<svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                    : '<svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                                }
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold ${check.passed ? 'text-green-900' : 'text-red-900'}">${check.label}</p>
                                <p class="text-sm ${check.passed ? 'text-green-700' : 'text-red-700'} mt-1">${check.message}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>

                ${checks.every(c => c.passed) 
                    ? `<div class="bg-green-100 border-2 border-green-300 rounded-xl p-4 mb-6">
                        <p class="text-green-900 font-semibold text-center">✓ All checks passed! Your essay looks good to submit.</p>
                       </div>`
                    : `<div class="bg-amber-100 border-2 border-amber-300 rounded-xl p-4 mb-6">
                        <p class="text-amber-900 font-semibold text-center">⚠ Some issues detected. You can still submit, but consider fixing them first.</p>
                       </div>`
                }

                <div class="flex gap-3">
                    <button onclick="closeValidationModal()" class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                        Go Back & Edit
                    </button>
                    <button onclick="proceedWithSubmission()" class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg">
                        Submit Anyway
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function performTaskChecks(text, minWords, task) {
    const words = text.split(/\s+/).length;
    const paragraphs = text.split(/\n\s*\n/).filter(p => p.trim().length > 0);
    
    const checks = [
        {
            label: 'Word Count',
            passed: words >= minWords,
            message: words >= minWords 
                ? `✓ ${words} words (minimum: ${minWords})`
                : `✗ Only ${words} words. You need at least ${minWords} words.`
        },
        {
            label: 'Paragraph Structure',
            passed: paragraphs.length >= 3,
            message: paragraphs.length >= 3
                ? `✓ ${paragraphs.length} paragraphs detected (Introduction, Body, Conclusion)`
                : `✗ Only ${paragraphs.length} paragraph(s). Aim for at least 3 (Intro, Body, Conclusion).`
        },
        {
            label: 'Introduction Present',
            passed: paragraphs.length > 0 && paragraphs[0].split(/\s+/).length >= 20,
            message: paragraphs.length > 0 && paragraphs[0].split(/\s+/).length >= 20
                ? '✓ Introduction paragraph detected'
                : '✗ Introduction seems too short or missing'
        },
        {
            label: 'Conclusion Present',
            passed: paragraphs.length > 0 && paragraphs[paragraphs.length - 1].split(/\s+/).length >= 20,
            message: paragraphs.length > 0 && paragraphs[paragraphs.length - 1].split(/\s+/).length >= 20
                ? '✓ Conclusion paragraph detected'
                : '✗ Conclusion seems too short or missing'
        }
    ];

    // Task-specific checks
    if (task === 'task2') {
        const hasPosition = /\b(I (believe|think|agree|disagree)|In my (opinion|view)|personally)\b/i.test(text);
        checks.push({
            label: 'Clear Position (Task 2)',
            passed: hasPosition,
            message: hasPosition
                ? '✓ Position statement detected'
                : '✗ No clear position found. Use phrases like "I believe..." or "In my opinion..."'
        });
    }

    return checks;
}

function closeValidationModal() {
    const modal = document.getElementById('validationModal');
    if (modal) modal.remove();
}

function proceedWithSubmission() {
    validationPassed = true;
    closeValidationModal();
    document.getElementById('writingForm').dispatchEvent(new Event('submit'));
}

async function submitForm() {
    const form = document.getElementById('writingForm');
    const formData = new FormData(form);
    const btn = document.getElementById('submitBtn');
    
    btn.disabled = true;
    btn.innerText = 'Evaluating...';

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();

        if (data.success && data.redirect) {
            localStorage.removeItem('writing_draft_{{ $test->id }}');
            window.location.href = data.redirect;
        } else {
            throw new Error(data.message);
        }

    } catch (err) {
        alert('Submission failed. Try again.');
        btn.disabled = false;
        btn.innerText = 'Submit Answer';
        validationPassed = false;
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= WORD COUNT ================= */
    const editor        = document.getElementById('essayEditor');
    const wordCountEl   = document.getElementById('wordCount');
    const minStatusEl   = document.getElementById('minWordsStatus');
    const submitBtn     = document.getElementById('submitBtn');
    const minWords      = {{ $minWords }};
    const draftKey      = 'writing_draft_{{ $test->id }}';

    function updateWordCount() {
        const text  = editor.value.trim();
        const words = text ? text.split(/\s+/).length : 0;

        wordCountEl.textContent = words;

        if (words >= minWords) {
            minStatusEl.textContent = '✓ Minimum reached';
            minStatusEl.className = 'text-green-600';
            submitBtn.disabled = false;
        } else {
            minStatusEl.textContent = `Minimum: ${minWords}`;
            minStatusEl.className = 'text-red-600';
            submitBtn.disabled = true;
        }

        // Real-time analysis
        updateRealTimeStats(text, words);
    }

    /* ================= REAL-TIME ANALYZER ================= */
    let vocabAnalysisTimeout = null;
    let lastAnalyzedWordCount = 0;

    function updateRealTimeStats(text, wordCount) {
        if (!text || wordCount === 0) {
            // Reset stats
            document.getElementById('uniqueWords').textContent = '0';
            document.getElementById('avgSentence').textContent = '0';
            document.getElementById('complexSentences').textContent = '0';
            return;
        }

        // Calculate unique words
        const wordsArray = text.toLowerCase().match(/\b\w+\b/g) || [];
        const uniqueWords = new Set(wordsArray).size;
        document.getElementById('uniqueWords').textContent = uniqueWords;

        // Calculate average sentence length
        const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
        const avgSentenceLength = sentences.length > 0 
            ? Math.round(wordCount / sentences.length) 
            : 0;
        document.getElementById('avgSentence').textContent = avgSentenceLength + ' words';

        // Calculate complex sentences (containing commas or conjunctions)
        const complexSentences = sentences.filter(s => {
            return s.includes(',') || 
                   /\b(and|but|because|although|however|moreover|furthermore|nevertheless)\b/i.test(s);
        }).length;
        document.getElementById('complexSentences').textContent = complexSentences;

        // Vocabulary analysis (debounced API call)
        if (wordCount >= 50 && Math.abs(wordCount - lastAnalyzedWordCount) >= 30) {
            clearTimeout(vocabAnalysisTimeout);
            vocabAnalysisTimeout = setTimeout(() => {
                analyzeVocabulary(text);
                lastAnalyzedWordCount = wordCount;
            }, 2000); // Wait 2 seconds after user stops typing
        }
    }

    async function analyzeVocabulary(text) {
        const statusEl = document.getElementById('vocabStatus');
        statusEl.textContent = 'Analyzing...';

        try {
            const response = await fetch('{{ route("writing.analyze.vocabulary") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ text: text })
            });

            const data = await response.json();

            // Update progress bars
            document.getElementById('basicPercent').textContent = data.basic + '%';
            document.getElementById('basicBar').style.width = data.basic + '%';
            
            document.getElementById('intermediatePercent').textContent = data.intermediate + '%';
            document.getElementById('intermediateBar').style.width = data.intermediate + '%';
            
            document.getElementById('advancedPercent').textContent = data.advanced + '%';
            document.getElementById('advancedBar').style.width = data.advanced + '%';

            // Update status
            if (data.advanced >= 40) {
                statusEl.textContent = '✓ Excellent!';
                statusEl.className = 'text-xs text-green-600 font-semibold';
            } else if (data.advanced >= 25) {
                statusEl.textContent = '⚠ Good';
                statusEl.className = 'text-xs text-yellow-600 font-semibold';
            } else {
                statusEl.textContent = '⚠ Needs improvement';
                statusEl.className = 'text-xs text-red-600 font-semibold';
            }

        } catch (error) {
            console.error('Vocabulary analysis failed:', error);
            statusEl.textContent = 'Analysis unavailable';
            statusEl.className = 'text-xs text-gray-400';
        }
    }

    editor.addEventListener('input', updateWordCount);

    const savedDraft = localStorage.getItem(draftKey);
    if (savedDraft) {
        editor.value = savedDraft;
        updateWordCount();
    }

    setInterval(() => {
        if (editor.value.trim()) {
            localStorage.setItem(draftKey, editor.value);
        }
    }, 20000);

    /* ================= TIMER ================= */
    let time = {{ $totalTime }};
    const timerEl = document.getElementById('timer');

    function updateTimer() {
        const minutes = String(Math.floor(time / 60)).padStart(2, '0');
        const seconds = String(time % 60).padStart(2, '0');
        timerEl.textContent = `${minutes}:${seconds}`;

        if (time === 600) alert('⏰ 10 minutes remaining');
        if (time === 300) alert('⏰ 5 minutes remaining');

        if (time <= 0) {
            clearInterval(timerInterval);
            return;
        }

        time--;
    }

    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
});
</script>

<script>
document.getElementById('writingForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerText = 'Evaluating...';

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        });

        const data = await res.json();

        if (data.success && data.redirect) {
            localStorage.removeItem('writing_draft_{{ $test->id }}');
            window.location.href = data.redirect;
        } else {
            throw new Error(data.message);
        }

    } catch {
        alert('Submission failed. Try again.');
        btn.disabled = false;
        btn.innerText = 'Submit Answer';
    }
});
</script>

</x-app-layout>
