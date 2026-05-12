<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Feedback Inbox</h1>
            <p class="text-surface-400 text-sm mt-1">
                {{ $counts['new'] }} new · {{ $counts['reviewing'] }} reviewing · {{ $counts['resolved'] }} resolved · {{ $counts['total'] }} total
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="status" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All statuses</option>
            @foreach(['new','reviewing','resolved','dismissed'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="category" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All categories</option>
            @foreach(['bug','feature','scoring','general'] as $c)
                <option value="{{ $c }}" @selected(request('category')===$c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm px-4">Filter</button>
    </form>

    <div class="space-y-3">
        @forelse($feedbacks as $fb)
            @php
                $catColors = ['bug'=>'rose','feature'=>'sky','scoring'=>'amber','general'=>'surface'];
                $cc = $catColors[$fb->category] ?? 'surface';
                $statusColors = ['new'=>'emerald','reviewing'=>'amber','resolved'=>'sky','dismissed'=>'surface'];
                $sc = $statusColors[$fb->status] ?? 'surface';
            @endphp
            <div class="card p-4 md:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $cc }}-500/15 text-{{ $cc }}-400 capitalize font-semibold">{{ $fb->category }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-500/15 text-{{ $sc }}-400 capitalize">{{ $fb->status }}</span>
                        @if($fb->rating)
                            <span class="text-xs text-amber-400">{{ str_repeat('★', $fb->rating) }}{{ str_repeat('☆', 5-$fb->rating) }}</span>
                        @endif
                        <span class="text-xs text-surface-500">{{ $fb->created_at->diffForHumans() }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.feedback.status', $fb) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="bg-surface-900 border border-surface-700 rounded px-2 py-1 text-xs text-surface-300">
                            @foreach(['new','reviewing','resolved','dismissed'] as $s)
                                <option value="{{ $s }}" @selected($fb->status===$s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="text-surface-200 whitespace-pre-wrap text-sm leading-relaxed">{{ $fb->message }}</div>
                <div class="mt-3 pt-3 border-t border-surface-800 flex flex-wrap gap-x-6 gap-y-1 text-xs text-surface-500">
                    <span>From: {{ $fb->user?->name ?? 'Anonymous' }}{{ $fb->email ? ' · '.$fb->email : '' }}</span>
                    @if($fb->page_url)<span class="truncate max-w-md">Page: <a href="{{ $fb->page_url }}" class="text-brand-400 hover:underline" target="_blank">{{ $fb->page_url }}</a></span>@endif
                    @if($fb->ip)<span class="font-mono">IP: {{ $fb->ip }}</span>@endif
                </div>
            </div>
        @empty
            <div class="card p-12 text-center text-surface-500">No feedback yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $feedbacks->links() }}</div>

</div>
</div>
</x-app-layout>
