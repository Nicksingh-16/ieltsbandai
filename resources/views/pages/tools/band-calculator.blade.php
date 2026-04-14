<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Band Score Calculator — Free Online Tool | IELTS Band AI</title>
    <meta name="description" content="Calculate your overall IELTS band score instantly. Enter your Listening, Reading, Writing and Speaking scores to get your combined band score.">
    <meta name="keywords" content="IELTS band calculator, IELTS overall score calculator, IELTS score calculator online, IELTS band score">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-100 min-h-screen">

{{-- Minimal nav --}}
<nav class="bg-surface-900 border-b border-surface-700 px-4 py-3 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/></svg>
        </div>
        <span class="font-bold text-surface-50">IELTS Band AI</span>
    </a>
    <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Free Sign Up</a>
</nav>

<main class="max-w-2xl mx-auto px-4 py-12">

    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-surface-50 mb-3">IELTS Band Score Calculator</h1>
        <p class="text-surface-400">Enter your module scores to calculate your overall IELTS band score instantly.</p>
    </div>

    <div x-data="bandCalc()" class="card p-8">

        <div class="space-y-6 mb-8">
            @foreach([
                ['key'=>'listening','label'=>'Listening','color'=>'text-amber-400','max'=>40,'note'=>'Raw score 0–40'],
                ['key'=>'reading',  'label'=>'Reading',  'color'=>'text-rose-400', 'max'=>40,'note'=>'Raw score 0–40'],
                ['key'=>'writing',  'label'=>'Writing',  'color'=>'text-purple-400','max'=>9, 'note'=>'Band score 1–9'],
                ['key'=>'speaking', 'label'=>'Speaking', 'color'=>'text-brand-400', 'max'=>9, 'note'=>'Band score 1–9'],
            ] as $m)
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-semibold {{ $m['color'] }}">{{ $m['label'] }}</label>
                    <span class="text-xs text-surface-500">{{ $m['note'] }}</span>
                </div>
                @if(in_array($m['key'], ['listening','reading']))
                {{-- Raw score input with band conversion --}}
                <div class="flex items-center gap-4">
                    <input type="number" x-model="raw.{{ $m['key'] }}" @input="calc()"
                        min="0" max="{{ $m['max'] }}" placeholder="e.g. 28"
                        class="flex-1 bg-surface-900 border border-surface-700 rounded-lg px-4 py-3 text-surface-100 text-lg font-semibold focus:outline-none focus:border-brand-500">
                    <div class="text-right w-24">
                        <span class="text-2xl font-bold {{ $m['color'] }}" x-text="bands.{{ $m['key'] }} ?? '—'"></span>
                        <p class="text-xs text-surface-500">band</p>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-4">
                    <input type="number" x-model="bands.{{ $m['key'] }}" @input="calc()"
                        min="0" max="9" step="0.5" placeholder="e.g. 6.5"
                        class="flex-1 bg-surface-900 border border-surface-700 rounded-lg px-4 py-3 text-surface-100 text-lg font-semibold focus:outline-none focus:border-brand-500">
                    <div class="text-right w-24">
                        <span class="text-2xl font-bold {{ $m['color'] }}" x-text="bands.{{ $m['key'] }} ?? '—'"></span>
                        <p class="text-xs text-surface-500">band</p>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Overall result --}}
        <div class="border-t border-surface-700 pt-6 text-center">
            <p class="text-surface-400 text-sm mb-2">Overall IELTS Band Score</p>
            <div class="text-7xl font-bold text-brand-400 mb-3" x-text="overall ?? '—'"></div>
            <p class="text-surface-500 text-sm" x-text="levelDesc"></p>
        </div>

    </div>

    {{-- Raw score conversion table --}}
    <div class="mt-10 card p-6">
        <h2 class="font-semibold text-surface-200 mb-4">Listening & Reading Raw Score → Band Conversion</h2>
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <p class="text-surface-400 text-xs uppercase tracking-wider mb-3">Academic Reading</p>
                <table class="w-full">
                    <tbody class="divide-y divide-surface-800">
                        @foreach([[39,40,9],[37,38,8.5],[35,36,8],[33,34,7.5],[30,32,7],[27,29,6.5],[23,26,6],[19,22,5.5],[15,18,5],[13,14,4.5],[10,12,4]] as [$min,$max,$band])
                        <tr class="text-surface-400 text-xs">
                            <td class="py-1">{{ $min }}–{{ $max }}</td>
                            <td class="py-1 text-right text-surface-200 font-semibold">{{ $band }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                <p class="text-surface-400 text-xs uppercase tracking-wider mb-3">Listening</p>
                <table class="w-full">
                    <tbody class="divide-y divide-surface-800">
                        @foreach([[39,40,9],[37,38,8.5],[35,36,8],[32,34,7.5],[30,31,7],[26,29,6.5],[23,25,6],[18,22,5.5],[16,17,5],[13,15,4.5],[10,12,4]] as [$min,$max,$band])
                        <tr class="text-surface-400 text-xs">
                            <td class="py-1">{{ $min }}–{{ $max }}</td>
                            <td class="py-1 text-right text-surface-200 font-semibold">{{ $band }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SEO content --}}
    <div class="mt-12 space-y-4 text-surface-400 text-sm leading-relaxed">
        <h2 class="text-xl font-semibold text-surface-200">How is the IELTS overall band score calculated?</h2>
        <p>Your overall IELTS band score is the average of your four module scores (Listening, Reading, Writing, Speaking), rounded to the nearest whole or half band. For example: 7 + 6.5 + 6 + 6.5 = 26 ÷ 4 = 6.5 overall.</p>
        <p>Listening and Reading are scored from raw marks (0–40 correct answers), which are converted to band scores 1–9. Writing and Speaking are directly assessed by examiners (or AI) on band descriptors and assigned a band score.</p>
        <h2 class="text-xl font-semibold text-surface-200 mt-6">What band score do I need?</h2>
        <p>Most UK universities require Band 6.5–7.0 overall. Australian PR typically requires Band 6.0–7.0. Canadian Express Entry uses a points system but Band 7.0+ on IELTS adds the most points. Healthcare registration bodies (NMC UK, AHPRA) typically require Band 7.0+ in each module.</p>
    </div>

</main>

<script>
function bandCalc() {
    // Official IELTS listening raw→band table
    const listeningTable = [[39,40,9],[37,38,8.5],[35,36,8],[32,34,7.5],[30,31,7],[26,29,6.5],[23,25,6],[18,22,5.5],[16,17,5],[13,15,4.5],[10,12,4],[8,9,3.5],[6,7,3],[4,5,2.5]];
    const readingTable   = [[39,40,9],[37,38,8.5],[35,36,8],[33,34,7.5],[30,32,7],[27,29,6.5],[23,26,6],[19,22,5.5],[15,18,5],[13,14,4.5],[10,12,4],[8,9,3.5],[6,7,3],[4,5,2.5]];

    function rawToBand(raw, table) {
        const r = parseInt(raw);
        if (isNaN(r)) return null;
        for (const [min, max, band] of table) {
            if (r >= min && r <= max) return band;
        }
        return r >= 1 ? 1 : null;
    }

    return {
        raw:   { listening: '', reading: '' },
        bands: { listening: null, reading: null, writing: '', speaking: '' },
        overall: null, levelDesc: '',
        calc() {
            this.bands.listening = rawToBand(this.raw.listening, listeningTable);
            this.bands.reading   = rawToBand(this.raw.reading, readingTable);

            const all = [this.bands.listening, this.bands.reading,
                         parseFloat(this.bands.writing) || null,
                         parseFloat(this.bands.speaking) || null].filter(v => v !== null);

            if (all.length === 0) { this.overall = null; this.levelDesc = ''; return; }

            const avg = all.reduce((a,b) => a+b, 0) / all.length;
            this.overall = Math.round(avg * 2) / 2;

            const levels = [[8,'Expert User — full operational command of English'],
                            [7,'Good User — operational command with occasional inaccuracies'],
                            [6,'Competent User — effective command in most situations'],
                            [5,'Modest User — partial command, handles familiar situations'],
                            [0,'Limited User — can communicate in familiar situations only']];
            this.levelDesc = levels.find(([min]) => this.overall >= min)?.[1] ?? '';
        }
    };
}
</script>
</body>
</html>
