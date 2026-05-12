@php($pageTitle = 'Share feedback')
<x-app-layout>
<div class="min-h-[80vh] pt-24 pb-12 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8 scroll-mt-24">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                Beta feedback
            </span>
            <h1 class="text-3xl md:text-4xl font-bold text-white">Help us shape IELTS Band AI</h1>
            <p class="mt-3 text-surface-400">Found a bug, want a feature, or disagree with a band score? Tell us — we read every message.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('feedback.store') }}" class="card p-6 md:p-8 space-y-6">
            @csrf

            <input type="hidden" name="page_url" value="{{ $page_url }}">

            <div>
                <label class="block text-sm font-semibold text-surface-200 mb-2">What is this about?</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3" x-data="{ cat: '{{ old('category', 'general') }}' }">
                    @foreach([
                        'bug'     => ['Bug',         '🐛'],
                        'feature' => ['Feature',     '💡'],
                        'scoring' => ['Scoring',     '📊'],
                        'general' => ['General',     '💬'],
                    ] as $val => $meta)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $val }}" x-model="cat" class="sr-only" {{ old('category','general')===$val ? 'checked':'' }}>
                            <div class="text-center px-3 py-4 rounded-xl border-2 transition"
                                 :class="cat === '{{ $val }}' ? 'bg-brand-500/15 border-brand-400 text-white shadow-lg shadow-brand-500/10' : 'bg-surface-900/60 border-surface-700 text-surface-200 hover:border-surface-500 hover:bg-surface-800/60'">
                                <div class="text-2xl leading-none">{{ $meta[1] }}</div>
                                <div class="text-xs font-semibold mt-2">{{ $meta[0] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('category')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-surface-200 mb-2">How would you rate the experience? <span class="text-surface-500 font-normal">(optional)</span></label>
                <div class="flex gap-2" x-data="{ r: {{ (int) old('rating', 0) }} }">
                    @for($i=1;$i<=5;$i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" x-model.number="r" class="sr-only">
                            <div class="w-11 h-11 rounded-full grid place-items-center text-base font-bold border transition"
                                 :class="r >= {{ $i }} ? 'bg-amber-400 border-amber-300 text-surface-950 shadow-md shadow-amber-500/20' : 'bg-surface-800 border-surface-700 text-surface-200 hover:bg-surface-700 hover:border-surface-500'">
                                {{ $i }}
                            </div>
                        </label>
                    @endfor
                </div>
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold text-surface-200 mb-2">Your feedback</label>
                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="4000"
                          class="w-full rounded-lg bg-surface-900 border border-surface-700 focus:border-brand-400 focus:ring-brand-400 text-surface-100 placeholder-surface-500"
                          placeholder="What happened? What did you expect? Paste a test ID if it relates to a specific scoring.">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            @guest
            <div>
                <label for="email" class="block text-sm font-semibold text-surface-200 mb-2">Email <span class="text-surface-500 font-normal">(if you'd like a reply)</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-lg bg-surface-900 border border-surface-700 focus:border-brand-400 focus:ring-brand-400 text-surface-100 placeholder-surface-500"
                       placeholder="you@example.com">
            </div>
            @endguest

            <div class="flex items-center justify-between gap-3 pt-2">
                <a href="{{ url()->previous() }}" class="text-sm font-medium text-surface-300 hover:text-white transition">Cancel</a>
                <button type="submit" class="btn-primary">Send feedback</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
