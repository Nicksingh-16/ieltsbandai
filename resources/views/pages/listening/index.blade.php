<x-app-layout>
<div class="min-h-screen bg-surface-950 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="mb-8">
            <div class="flex items-center gap-2 text-surface-500 text-sm mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-surface-300 transition-colors">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-surface-300">Listening Test</span>
            </div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M8.464 8.464a5 5 0 000 7.072"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-surface-50">IELTS Listening Test</h1>
            </div>
            <p class="text-surface-400 mt-2">4 sections · 40 questions · 30 minutes + 10 minutes transfer time.</p>
        </div>

        {{-- What to expect --}}
        <div class="card p-6 mb-6">
            <h2 class="section-title mb-4">What to expect</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['Section 1', 'Everyday social context — conversation between 2 people', 'bg-brand-500/15 text-brand-400'],
                    ['Section 2', 'Everyday social context — monologue', 'bg-emerald-500/15 text-emerald-400'],
                    ['Section 3', 'Educational setting — discussion', 'bg-amber-500/15 text-amber-400'],
                    ['Section 4', 'Academic lecture — monologue', 'bg-purple-500/15 text-purple-400'],
                ] as [$title, $desc, $color])
                <div class="card p-4">
                    <div class="w-7 h-7 rounded-lg {{ $color }} flex items-center justify-center text-xs font-bold mb-2">
                        {{ substr($title, -1) }}
                    </div>
                    <p class="text-sm font-semibold text-surface-100">{{ $title }}</p>
                    <p class="text-xs text-surface-500 mt-1">{{ $desc }}</p>
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

        <form action="{{ route('listening.start') }}" method="POST" class="space-y-5">
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
                        <p class="text-surface-500 text-xs">Live progress tracking, dark UI, section navigation</p>
                    </div>
                </label>
                <label class="cursor-pointer" onclick="setMode('exam')">
                    <div id="examCard" class="card p-4 border-2 border-transparent hover:border-surface-500 transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🎓</span>
                            <p class="font-semibold text-surface-100 text-sm">Exam Simulation</p>
                        </div>
                        <p class="text-surface-500 text-xs">Real IELTS UI — strict timer, question flagging, notes</p>
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
                            <p class="text-surface-500 text-xs mt-1">University & professional</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="test_type" value="general" class="sr-only peer">
                        <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                            <p class="font-semibold text-surface-100 text-sm">General Training</p>
                            <p class="text-surface-500 text-xs mt-1">Migration & work</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 bg-amber-500/10 border border-amber-500/25 rounded-xl p-4">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-amber-300 font-semibold text-sm mb-1">Before you begin</p>
                    <ul class="text-amber-400/80 text-xs space-y-0.5">
                        <li>Use headphones for best experience</li>
                        <li>Audio plays once — take notes as you listen</li>
                        <li>You have 10 minutes at the end to transfer answers</li>
                    </ul>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-4 text-base font-bold shadow-glow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="startBtnText">Start Listening Test</span>
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
            document.getElementById('startBtnText').textContent = isExam ? 'Start Exam Simulation' : 'Start Listening Test';
        }
        </script>
        @endpush

    </div>
</div>
</x-app-layout>
