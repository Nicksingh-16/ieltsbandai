<x-app-layout>
<div class="min-h-screen bg-surface-950">

    {{-- Hero --}}
    <div class="relative overflow-hidden border-b border-surface-800">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 via-transparent to-purple-500/5 pointer-events-none"></div>
        <div class="max-w-5xl mx-auto px-4 py-14 relative">
            <div class="flex flex-col lg:flex-row lg:items-center gap-10">

                {{-- Left: copy --}}
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 bg-brand-500/10 border border-brand-500/20 rounded-full px-3 py-1 text-brand-400 text-xs font-semibold uppercase tracking-wider mb-5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Full Simulation
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-bold text-surface-50 leading-tight mb-4">
                        IELTS Full<br><span class="text-gradient">Mock Test</span>
                    </h1>
                    <p class="text-surface-400 text-lg leading-relaxed mb-6 max-w-lg">
                        All 4 modules in sequence with real time limits. Get a combined overall band score — exactly like the real exam.
                    </p>
                    <div class="flex flex-wrap gap-4 text-sm text-surface-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Real time limits enforced
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            AI evaluation for Writing & Speaking
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Combined overall band score
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Take breaks between modules
                        </div>
                    </div>
                </div>

                {{-- Right: module timeline card --}}
                <div class="lg:w-80 shrink-0">
                    <div class="card p-5">
                        <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-4">Test Structure</p>
                        <div class="space-y-1">
                            @foreach([
                                ['num'=>1,'label'=>'Listening','emoji'=>'🎧','time'=>'40 min','color'=>'bg-amber-500/15 text-amber-400','border'=>'border-amber-500/20'],
                                ['num'=>2,'label'=>'Reading',  'emoji'=>'📖','time'=>'60 min','color'=>'bg-rose-500/15 text-rose-400',  'border'=>'border-rose-500/20'],
                                ['num'=>3,'label'=>'Writing',  'emoji'=>'✍️','time'=>'60 min','color'=>'bg-purple-500/15 text-purple-400','border'=>'border-purple-500/20'],
                                ['num'=>4,'label'=>'Speaking', 'emoji'=>'🎤','time'=>'14 min','color'=>'bg-brand-500/15 text-brand-400',  'border'=>'border-brand-500/20'],
                            ] as $m)
                            <div class="flex items-center gap-3 p-3 rounded-xl {{ $m['color'] }} border {{ $m['border'] }}">
                                <span class="text-lg">{{ $m['emoji'] }}</span>
                                <div class="flex-1">
                                    <span class="font-semibold text-sm text-surface-100">{{ $m['label'] }}</span>
                                </div>
                                <span class="text-xs text-surface-400 font-mono">{{ $m['time'] }}</span>
                            </div>
                            @if($m['num'] < 4)
                            <div class="flex justify-center py-0.5">
                                <div class="w-px h-3 bg-surface-700"></div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-surface-700 flex justify-between text-sm">
                            <span class="text-surface-500">Total</span>
                            <span class="font-semibold text-surface-200">~3 hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-10">

        {{-- Resume in-progress mock test --}}
        @if($active)
        <div class="card p-5 border border-amber-500/30 bg-amber-500/5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-400">Mock Test In Progress</p>
                        <p class="text-surface-400 text-sm mt-0.5">
                            {{ ucfirst($active->test_type) }} · Started {{ $active->started_at->diffForHumans() }} ·
                            Next: <span class="text-surface-200 font-medium capitalize">{{ $active->current_module }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('mock-test.module', ['mock' => $active->id, 'module' => $active->current_module]) }}"
                        class="btn-primary text-sm px-5">Resume →</a>
                    <form method="POST" action="{{ route('mock-test.abandon', $active) }}"
                        onsubmit="return confirm('Abandon this mock test? This cannot be undone.')">
                        @csrf
                        <button class="btn-secondary text-sm text-red-400 hover:text-red-300 border-red-500/30">Abandon</button>
                    </form>
                </div>
            </div>

            {{-- Module progress dots --}}
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-amber-500/20">
                @foreach(['listening','reading','writing','speaking'] as $mod)
                @php $done = $active->moduleTestId($mod) !== null; $current = $active->current_module === $mod; @endphp
                <div class="flex items-center gap-2 flex-1">
                    <div class="flex-1 flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                            {{ $done ? 'bg-emerald-500 text-white' : ($current ? 'bg-brand-500 text-white' : 'bg-surface-800 text-surface-500') }}">
                            {{ $done ? '✓' : strtoupper(substr($mod, 0, 1)) }}
                        </div>
                        <span class="text-xs {{ $done ? 'text-emerald-400' : ($current ? 'text-brand-400' : 'text-surface-600') }} capitalize hidden sm:block">{{ $mod }}</span>
                    </div>
                    @if(!$loop->last)<div class="h-px bg-surface-700 flex-1"></div>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            {{-- Start form --}}
            <div class="lg:col-span-3">
                @if(!$active)
                <div class="card p-7">
                    <h2 class="text-lg font-bold text-surface-100 mb-1">Start a New Mock Test</h2>
                    <p class="text-surface-500 text-sm mb-6">Choose your test type — Academic for university admission, General Training for migration and work visas.</p>

                    <form method="POST" action="{{ route('mock-test.start') }}" x-data="{ selected: '' }">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <label class="cursor-pointer">
                                <input type="radio" name="test_type" value="academic" class="sr-only peer" required
                                    x-on:change="selected = 'academic'">
                                <div class="card p-5 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all h-full">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-500/15 flex items-center justify-center mb-3">
                                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    </div>
                                    <p class="font-bold text-surface-100 mb-1">Academic</p>
                                    <p class="text-surface-500 text-xs leading-relaxed">University admission & professional registration</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="test_type" value="general" class="sr-only peer"
                                    x-on:change="selected = 'general'">
                                <div class="card p-5 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all h-full">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center mb-3">
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                    </div>
                                    <p class="font-bold text-surface-100 mb-1">General Training</p>
                                    <p class="text-surface-500 text-xs leading-relaxed">Migration, work experience & everyday English</p>
                                </div>
                            </label>
                        </div>

                        <button type="submit"
                            class="btn-primary w-full py-3.5 text-base font-bold justify-center shadow-glow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Begin Mock Test
                        </button>
                        <p class="text-xs text-surface-600 text-center mt-3">Uses 1 credit per module · 4 credits total</p>
                    </form>
                </div>
                @else
                <div class="card p-7 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/15 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="font-bold text-surface-100 mb-2">You have an ongoing mock test</h2>
                    <p class="text-surface-500 text-sm mb-5">Complete or abandon your current mock test before starting a new one.</p>
                    <a href="{{ route('mock-test.module', ['mock' => $active->id, 'module' => $active->current_module]) }}"
                        class="btn-primary justify-center py-3 w-full">Resume Current Test →</a>
                </div>
                @endif
            </div>

            {{-- Right panel: info + past results --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Credit info --}}
                <div class="card p-5">
                    <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-3">Credits Required</p>
                    <div class="space-y-2 text-sm">
                        @foreach(['Listening','Reading','Writing','Speaking'] as $mod)
                        <div class="flex justify-between items-center">
                            <span class="text-surface-400">{{ $mod }}</span>
                            <span class="text-surface-300 font-semibold">1 credit</span>
                        </div>
                        @endforeach
                        <div class="pt-2 border-t border-surface-700 flex justify-between items-center font-semibold">
                            <span class="text-surface-300">Total</span>
                            <span class="text-brand-400">4 credits</span>
                        </div>
                    </div>
                    <p class="text-xs text-surface-600 mt-3">Credits are deducted as you start each module, not all at once.</p>
                </div>

                {{-- Tips --}}
                <div class="card p-5">
                    <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-3">Before You Start</p>
                    <ul class="space-y-2.5">
                        @foreach([
                            'Find a quiet space with no interruptions',
                            'Have headphones ready for listening',
                            'Check your microphone works for speaking',
                            'Modules must be completed in order — no skipping',
                            'You can take breaks between modules',
                        ] as $tip)
                        <li class="flex gap-2 text-sm text-surface-400">
                            <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            {{ $tip }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Past mock test results --}}
        @php
        $pastTests = \App\Models\MockTest::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();
        @endphp
        @if($pastTests->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-lg font-bold text-surface-100 mb-4">Past Mock Tests</h2>
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm">
                    <thead>
                        <tr class="border-b border-surface-800">
                            <th class="text-left px-5 py-3 text-surface-400 font-medium">Date</th>
                            <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                            <th class="text-center px-3 py-3 text-surface-400 font-medium">L</th>
                            <th class="text-center px-3 py-3 text-surface-400 font-medium">R</th>
                            <th class="text-center px-3 py-3 text-surface-400 font-medium">W</th>
                            <th class="text-center px-3 py-3 text-surface-400 font-medium">S</th>
                            <th class="text-center px-3 py-3 text-surface-400 font-medium">Overall</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-800">
                        @foreach($pastTests as $mt)
                        <tr class="hover:bg-surface-900 transition-colors">
                            <td class="px-5 py-3 text-surface-400 text-xs">{{ $mt->completed_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3"><span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded capitalize">{{ $mt->test_type }}</span></td>
                            @foreach(['listening_band','reading_band','writing_band','speaking_band'] as $b)
                            <td class="px-3 py-3 text-center">
                                @if($mt->$b)
                                <span class="{{ $mt->$b >= 7 ? 'text-emerald-400' : ($mt->$b >= 6 ? 'text-amber-400' : 'text-red-400') }} font-semibold">{{ $mt->$b }}</span>
                                @else<span class="text-surface-600">—</span>@endif
                            </td>
                            @endforeach
                            <td class="px-3 py-3 text-center font-bold text-2xl text-brand-400">{{ $mt->overall_band ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('mock-test.result', $mt) }}" class="text-xs text-indigo-400 hover:text-indigo-300 whitespace-nowrap">View →</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
</x-app-layout>
