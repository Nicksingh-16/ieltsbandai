<x-app-layout>
<div class="min-h-screen bg-surface-950 py-10 px-4">
<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('dashboard') }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
        <span class="tag-cyan">Referral Program</span>
    </div>

    {{-- Hero --}}
    <div class="card p-8 mb-6 text-center border-brand-500/20 bg-gradient-to-br from-surface-800 to-surface-900">
        <div class="w-16 h-16 rounded-2xl bg-brand-500/15 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-surface-50 mb-2">Earn Free Test Credits</h1>
        <p class="text-surface-400 mb-6">Share your referral link. For every friend who joins, you get <span class="text-brand-400 font-bold">+2 credits</span>. They get <span class="text-emerald-400 font-bold">+1 bonus credit</span> on signup.</p>

        <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto">
            <div class="card p-3 text-center">
                <div class="text-2xl font-bold text-brand-400">{{ $user->referral_credits_earned ?? 0 }}</div>
                <div class="text-xs text-surface-500 mt-0.5">Credits Earned</div>
            </div>
            <div class="card p-3 text-center">
                <div class="text-2xl font-bold text-emerald-400">{{ $referrals->count() }}</div>
                <div class="text-xs text-surface-500 mt-0.5">Friends Joined</div>
            </div>
            <div class="card p-3 text-center">
                <div class="text-2xl font-bold text-amber-400">{{ $user->test_credits ?? 0 }}</div>
                <div class="text-xs text-surface-500 mt-0.5">Total Credits</div>
            </div>
        </div>
    </div>

    {{-- Referral link --}}
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-surface-100 mb-4">Your Referral Link</h2>
        <div class="flex gap-3" x-data="{ copied: false }">
            <input type="text" value="{{ $referralUrl }}" readonly
                class="flex-1 bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-surface-300 text-sm font-mono focus:outline-none">
            <button @click="navigator.clipboard.writeText('{{ $referralUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2500) })"
                class="btn-primary px-5 shrink-0">
                <span x-show="!copied">Copy</span>
                <span x-show="copied" class="text-emerald-300">Copied!</span>
            </button>
        </div>
        <div class="flex gap-3 mt-4">
            <a href="https://wa.me/?text={{ urlencode('I\'ve been using IELTS Band AI to practice for IELTS — it gives instant AI scores for writing and speaking. Get a bonus test credit when you sign up: ' . $referralUrl) }}"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 bg-[#25D366]/10 border border-[#25D366]/30 text-[#25D366] rounded-xl py-2.5 text-sm font-semibold hover:bg-[#25D366]/20 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Share on WhatsApp
            </a>
            <a href="https://t.me/share/url?url={{ urlencode($referralUrl) }}&text={{ urlencode('Get +1 free IELTS test credit when you join IELTS Band AI!') }}"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 bg-[#0088cc]/10 border border-[#0088cc]/30 text-[#0088cc] rounded-xl py-2.5 text-sm font-semibold hover:bg-[#0088cc]/20 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 11.944 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Share on Telegram
            </a>
        </div>
    </div>

    {{-- How it works --}}
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-surface-100 mb-4">How It Works</h2>
        <div class="space-y-3">
            @foreach([
                ['1','Share your link with a friend who is preparing for IELTS', 'text-brand-400'],
                ['2','They sign up using your link — they instantly get +1 bonus test credit', 'text-emerald-400'],
                ['3','You receive +2 credits automatically — no limits on referrals!', 'text-amber-400'],
            ] as [$n, $text, $color])
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-surface-700 flex items-center justify-center text-xs font-bold {{ $color }} shrink-0">{{ $n }}</div>
                <p class="text-sm text-surface-300 pt-0.5">{{ $text }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Referrals list --}}
    @if($referrals->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-surface-700">
            <h2 class="font-semibold text-surface-100">Friends Who Joined</h2>
        </div>
        <div class="divide-y divide-surface-700">
            @foreach($referrals as $ref)
            <div class="px-6 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                    {{ strtoupper(substr($ref->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-surface-200 truncate">{{ $ref->name }}</p>
                    <p class="text-xs text-surface-500">Joined {{ $ref->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-xs text-emerald-400 font-semibold">+2 credits</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
</div>
</x-app-layout>
