<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-4xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">My Assigned Tests</h1>
            <p class="text-surface-400 text-sm mt-1">Tests assigned to you by your institute</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">{{ session('error') }}</div>
    @endif

    @forelse($records as $r)
    @php
        $a       = $r->assignment;
        $tmpl    = $a->template;
        $overdue = $a->isOverdue();
        $done    = $r->status === 'completed';
        $started = $r->status === 'started';
    @endphp
    <div class="card p-5 mb-4 {{ $done ? 'opacity-70' : '' }}">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h3 class="font-semibold text-surface-100">{{ $a->title }}</h3>
                    @if($a->is_mandatory)
                    <span class="text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded">Mandatory</span>
                    @endif
                    @if($done)
                    <span class="text-xs bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded">Completed</span>
                    @elseif($started)
                    <span class="text-xs bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded">In Progress</span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-surface-400">
                    <span class="bg-surface-800 px-2 py-0.5 rounded">{{ str_replace('_',' ', ucfirst($tmpl->type)) }}</span>
                    <span>{{ $tmpl->duration_minutes }}m</span>
                    @if($a->due_date)
                    <span class="{{ $overdue ? 'text-red-400' : '' }}">
                        Due {{ $a->due_date->format('d M Y, g:i A') }}
                        {{ $overdue ? '(Overdue)' : '' }}
                    </span>
                    @endif
                    @if($tmpl->questions()->count())
                    <span>{{ $tmpl->questions()->count() }} questions</span>
                    @endif
                </div>

                @if($a->instructions)
                <p class="text-surface-400 text-xs mt-2">{{ $a->instructions }}</p>
                @endif

                @if($done && $r->test?->overall_band)
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-surface-400 text-xs">Your band:</span>
                    <span class="text-emerald-400 font-bold text-lg">{{ number_format($r->test->overall_band, 1) }}</span>
                    <a href="{{ $r->test->result_route }}" class="text-xs text-indigo-400 hover:text-indigo-300">View Result →</a>
                </div>
                @endif
            </div>

            <div class="shrink-0">
                @if($done && !$a->allows_retake)
                    <span class="text-xs text-surface-500">Submitted {{ $r->completed_at?->diffForHumans() }}</span>
                @elseif($a->status === 'active')
                    <form method="POST" action="{{ route('institute.assigned.start', $a) }}">
                        @csrf
                        <button class="btn-primary text-sm px-6">
                            {{ $started ? 'Continue Test' : ($done ? 'Retake' : 'Start Test') }}
                        </button>
                    </form>
                @else
                    <span class="text-xs text-surface-500">Assignment closed</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center">
        <div class="text-surface-400 mb-2">No tests assigned to you yet.</div>
        <a href="{{ route('dashboard') }}" class="text-indigo-400 text-sm">Go to Dashboard →</a>
    </div>
    @endforelse

</div>
</div>
</x-app-layout>
