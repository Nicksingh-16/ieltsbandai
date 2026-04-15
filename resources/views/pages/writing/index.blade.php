<x-app-layout>
<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 text-surface-500 text-sm mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-surface-300 transition-colors">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-surface-300">Writing Test</span>
            </div>
            <h1 class="text-3xl font-bold text-surface-50">IELTS Writing Test</h1>
            <p class="text-surface-400 mt-2">AI-powered evaluation with instant band score and criterion-level feedback.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('writing.start') }}" method="POST" class="space-y-6">
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
                        <p class="text-surface-500 text-xs">Live word analysis, AI hints, dark UI</p>
                    </div>
                </label>
                <label class="cursor-pointer" onclick="setMode('exam')">
                    <div id="examCard" class="card p-4 border-2 border-transparent hover:border-surface-500 transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🎓</span>
                            <p class="font-semibold text-surface-100 text-sm">Exam Simulation</p>
                        </div>
                        <p class="text-surface-500 text-xs">Real IELTS UI — strict timer, clean interface, notes</p>
                    </div>
                </label>
            </div>

            {{-- Step 1: Test Type --}}
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider mb-4">Step 1 — Test Type</h2>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="test_type" value="academic" class="sr-only peer" required>
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center mb-3">
                                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <p class="font-semibold text-surface-100 text-sm">Academic</p>
                            <p class="text-surface-500 text-xs mt-1">University & professional registration</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="test_type" value="general" class="sr-only peer">
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center mb-3">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                            </div>
                            <p class="font-semibold text-surface-100 text-sm">General Training</p>
                            <p class="text-surface-500 text-xs mt-1">Migration, work & everyday English</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Step 2: Task --}}
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider mb-4">Step 2 — Writing Task</h2>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="task" value="task1" class="sr-only peer" required>
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-surface-50 font-bold">Task 1</span>
                                <span class="tag bg-surface-700 text-surface-400 border-surface-600 text-[10px]">20 min</span>
                            </div>
                            <p class="text-surface-500 text-xs">150+ words</p>
                            <p class="text-surface-400 text-xs mt-2">Graph / chart / diagram<br>or Letter (formal/informal)</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="task" value="task2" class="sr-only peer">
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-surface-50 font-bold">Task 2</span>
                                <span class="tag bg-surface-700 text-surface-400 border-surface-600 text-[10px]">40 min</span>
                            </div>
                            <p class="text-surface-500 text-xs">250+ words</p>
                            <p class="text-surface-400 text-xs mt-2">Essay — opinion / discussion / problem &amp; solution</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Exam conditions notice --}}
            <div class="flex gap-3 bg-amber-500/10 border border-amber-500/25 rounded-xl p-4">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-amber-300 font-semibold text-sm mb-1">Exam Conditions Apply</p>
                    <ul class="text-amber-400/80 text-xs space-y-0.5">
                        <li>Timer starts immediately after you begin</li>
                        <li>No pause or restart — auto-save only</li>
                        <li>Spelling & grammar evaluated strictly</li>
                    </ul>
                </div>
            </div>

            {{-- Start button --}}
            <button type="submit" class="btn-primary w-full py-4 text-base font-bold shadow-glow">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                <span id="startBtnText">Start Writing Test</span>
            </button>

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
                document.getElementById('startBtnText').textContent = isExam ? 'Start Exam Simulation' : 'Start Writing Test';
            }
            </script>
            @endpush
        </form>
    </div>
</div>
</x-app-layout>
