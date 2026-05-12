{{-- Beta banner. Renders only when config('beta.enabled') is true.
     Auto-disappears at go-live by flipping BETA_MODE=false in .env. --}}
@if(config('beta.enabled'))
    @php
        $betaText = config('beta.banner_text');
        // Always point at the internal feedback form. The legacy
        // BETA_FEEDBACK_URL is only used as a fallback if the route is
        // somehow unavailable during early bootstrap.
        $betaUrl  = \Illuminate\Support\Facades\Route::has('feedback.create')
            ? route('feedback.create')
            : config('beta.feedback_url');
    @endphp
    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white text-sm relative z-40">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-center gap-3 text-center">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                Beta
            </span>
            <span class="font-medium">{{ $betaText }}</span>
            @if(!empty($betaUrl))
                <a href="{{ $betaUrl }}"
                   class="inline-flex items-center gap-1 font-bold underline underline-offset-2 hover:no-underline shrink-0">
                    Share feedback
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
@endif
