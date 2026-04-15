<x-app-layout>
<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="mb-8">
            <div class="flex items-center gap-2 text-surface-500 text-sm mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-surface-300 transition-colors">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-surface-300">Reading Test</span>
            </div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-rose-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-surface-50">IELTS Reading Test</h1>
            </div>
            <p class="text-surface-400 mt-2">3 passages · 40 questions · 60 minutes.</p>
        </div>

        {{-- Format info --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">Question types you'll encounter</h2>
            <div class="space-y-3">
                @foreach([
                    ['Multiple Choice', 'Choose A, B, C or D from given options'],
                    ['True / False / Not Given', 'Identify if statements match the passage'],
                    ['Yes / No / Not Given', 'Identify if statements match the writer\'s view'],
                    ['Matching Headings', 'Match headings to paragraphs'],
                    ['Short Answer', 'Answer using words from the passage'],
                    ['Sentence Completion', 'Complete sentences using exact words'],
                ] as [$type, $desc])
                <div class="flex items-start gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-2 shrink-0"></div>
                    <div>
                        <span class="text-sm font-medium text-surface-100">{{ $type }}</span>
                        <span class="text-surface-500 text-sm"> — {{ $desc }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('reading.start') }}" method="POST" class="space-y-5" id="readingStartForm">
            @csrf
            <input type="hidden" name="exam_mode" id="examModeInput" value="0">

            {{-- Mode Selector --}}
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer" onclick="setMode('practice')">
                    <div id="practiceCard" class="card p-4 border-2 border-brand-500 bg-brand-500/10 transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">📝</span>
                            <p class="font-semibold text-surface-100 text-sm">Practice Mode</p>
                        </div>
                        <p class="text-surface-500 text-xs">With AI hints, dark UI, live progress</p>
                    </div>
                </label>
                <label class="cursor-pointer" onclick="setMode('exam')">
                    <div id="examCard" class="card p-4 border-2 border-transparent hover:border-surface-500 transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🎓</span>
                            <p class="font-semibold text-surface-100 text-sm">Exam Simulation</p>
                        </div>
                        <p class="text-surface-500 text-xs">Real IELTS UI — highlights, flagging, strict timer</p>
                    </div>
                </label>
            </div>

            <div class="card p-6">
                <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider mb-4">Test Type</h2>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="test_type" value="academic" class="sr-only peer" required>
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <p class="font-semibold text-surface-100 text-sm">Academic</p>
                            <p class="text-surface-500 text-xs mt-1">3 long passages from books, journals, magazines</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="test_type" value="general" class="sr-only peer">
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <p class="font-semibold text-surface-100 text-sm">General Training</p>
                            <p class="text-surface-500 text-xs mt-1">Shorter texts from ads, notices, letters</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 bg-rose-500/10 border border-rose-500/25 rounded-xl p-4">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-rose-300 font-semibold text-sm mb-1">Tips for Reading</p>
                    <ul class="text-rose-400/80 text-xs space-y-0.5">
                        <li>Skim the passage first, then read questions carefully</li>
                        <li>Answers are always in the text — don't guess</li>
                        <li>Manage time: ~20 minutes per passage</li>
                    </ul>
                </div>
            </div>

            <button type="submit" id="startBtn" class="btn-primary w-full py-4 text-base font-bold shadow-glow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/>
                </svg>
                <span id="startBtnText">Start Reading Test</span>
            </button>
        </form>

        @push('scripts')
        <script>
        function setMode(mode) {
            const isExam = mode === 'exam';
            document.getElementById('examModeInput').value = isExam ? '1' : '0';
            document.getElementById('practiceCard').classList.toggle('border-brand-500', !isExam);
            document.getElementById('practiceCard').classList.toggle('bg-brand-500/10', !isExam);
            document.getElementById('practiceCard').classList.toggle('border-transparent', isExam);
            document.getElementById('examCard').classList.toggle('border-brand-500', isExam);
            document.getElementById('examCard').classList.toggle('bg-brand-500/10', isExam);
            document.getElementById('examCard').classList.toggle('border-transparent', !isExam);
            document.getElementById('startBtnText').textContent = isExam ? 'Start Exam Simulation' : 'Start Reading Test';
        }
        </script>
        @endpush

    </div>
</div>
</x-app-layout>
