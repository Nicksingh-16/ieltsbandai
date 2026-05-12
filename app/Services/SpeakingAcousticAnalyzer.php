<?php

namespace App\Services;

/**
 * SpeakingAcousticAnalyzer
 * -------------------------
 * Computes deterministic acoustic / temporal signals from a transcription
 * provider's word-level output (AssemblyAI / Deepgram). NEVER touches raw
 * audio — it works entirely off the words[] array produced by the STT
 * provider (each word has start/end timestamps in seconds and a confidence
 * score in [0, 1]).
 *
 * The output is injected into the speaking scoring prompt as a
 * "GROUND-TRUTH ACOUSTIC SIGNALS" block, mirroring the Layer-4 pattern
 * already used for writing (LinguisticAnalyzer + LanguageToolClient).
 *
 * All math is defensive against empty / malformed input — division-by-zero
 * is guarded everywhere and missing fields fall back to neutral defaults.
 */
class SpeakingAcousticAnalyzer
{
    /** Pause threshold (sec) above which a gap counts as a pause at all. */
    private const PAUSE_MIN = 0.5;
    /** Long-pause threshold (sec) — IELTS Band 4 descriptor cue. */
    private const PAUSE_LONG = 2.0;
    /** Mid-utterance hesitation threshold (sec). */
    private const MID_UTT_HESITATION = 1.0;
    /** Per-word confidence threshold below which a word is "uncertain". */
    private const LOW_CONFIDENCE = 0.7;
    /** Rolling window size (sec) for speech-rate variance. */
    private const RATE_WINDOW_SEC = 10.0;

    /**
     * Standalone filler tokens (single-word). Matched on the cleaned word
     * lowercased and stripped of punctuation, so they boundary-respect.
     */
    private const FILLERS_SINGLE = ['uh', 'uhh', 'um', 'umm', 'ummm', 'er', 'errr', 'ah', 'ahh', 'erm'];

    /**
     * Multi-word filler phrases. Matched on a normalised lowercase string
     * of the transcript with single-space separators.
     */
    private const FILLERS_PHRASE = ['you know', 'sort of', 'kind of', 'i mean'];

    /**
     * "like" is a filler ONLY when functioning as a discourse marker.
     * Lightweight heuristic: count "like" occurrences NOT preceded by a
     * verb-helper ("would", "do", "did", "to", "feel", "looks", "looked",
     * "feels", "felt", "i", "we", "they"), since those usually indicate
     * the verb sense ("I like ...", "would like ...").
     */
    private const LIKE_VERB_PREDECESSORS = ['would', 'do', 'did', 'to', 'feel', 'feels', 'felt', 'looks', 'looked', 'looking', 'sounds', 'sounded', 'i', 'we', 'they', 'you', 'he', 'she', 'people', 'really'];

    /**
     * @param array $words Normalised word array. Each entry:
     *   [
     *     'text'       => string,        // single word, may carry punctuation
     *     'start'      => float seconds, // word start
     *     'end'        => float seconds, // word end
     *     'confidence' => float 0..1,    // per-word confidence (0 if absent)
     *   ]
     */
    public function analyze(array $words): array
    {
        // Filter to valid entries only — gracefully ignore malformed rows.
        $words = array_values(array_filter($words, function ($w) {
            return is_array($w)
                && isset($w['text'])
                && is_string($w['text'])
                && $w['text'] !== ''
                && isset($w['start'], $w['end'])
                && is_numeric($w['start'])
                && is_numeric($w['end']);
        }));

        $total = count($words);

        if ($total === 0) {
            return $this->emptySignals();
        }

        // Duration: from start of first word to end of last word.
        $firstStart = (float) $words[0]['start'];
        $lastEnd    = (float) $words[$total - 1]['end'];
        $durationSec = max(0.0, $lastEnd - $firstStart);
        $durationMin = $durationSec > 0 ? $durationSec / 60.0 : 0.0;

        $wpm = $durationMin > 0 ? $total / $durationMin : 0.0;

        // ── Pause statistics ────────────────────────────────────────────
        $pauseDurations = [];
        $longPauses = 0;
        $midUttHesitations = 0;
        $longestPause = 0.0;

        for ($i = 1; $i < $total; $i++) {
            $gap = (float) $words[$i]['start'] - (float) $words[$i - 1]['end'];
            if ($gap <= self::PAUSE_MIN) {
                continue;
            }

            $pauseDurations[] = $gap;
            if ($gap > $longestPause) {
                $longestPause = $gap;
            }
            if ($gap >= self::PAUSE_LONG) {
                $longPauses++;
            }

            // Mid-utterance hesitation: the preceding word does NOT end
            // with sentence-final punctuation ('.', '!', '?', ','). This
            // approximates the IELTS Band 4 cue "hesitation often
            // associated with mid-utterance".
            if ($gap >= self::MID_UTT_HESITATION) {
                $prevText = (string) $words[$i - 1]['text'];
                $lastChar = substr(rtrim($prevText), -1);
                if ($lastChar !== '' && !in_array($lastChar, ['.', '!', '?', ','], true)) {
                    $midUttHesitations++;
                }
            }
        }

        $pauseCount = count($pauseDurations);
        $avgPause = $pauseCount > 0 ? array_sum($pauseDurations) / $pauseCount : 0.0;

        // ── Filler density ──────────────────────────────────────────────
        $fillerCounts = $this->countFillers($words);
        $totalFillers = array_sum($fillerCounts);
        $fillerPer100 = $total > 0 ? ($totalFillers * 100.0 / $total) : 0.0;

        // ── Confidence (pronunciation proxy) ────────────────────────────
        $confidences = [];
        $lowConfWords = 0;
        foreach ($words as $w) {
            if (isset($w['confidence']) && is_numeric($w['confidence'])) {
                $c = (float) $w['confidence'];
                $confidences[] = $c;
                if ($c < self::LOW_CONFIDENCE) {
                    $lowConfWords++;
                }
            }
        }
        $hasConfidence = count($confidences) > 0;
        $meanConfidence = $hasConfidence ? array_sum($confidences) / count($confidences) : null;
        $lowConfPer100 = ($hasConfidence && $total > 0) ? ($lowConfWords * 100.0 / $total) : null;

        // ── Speech rate variance (std dev of WPM across 10s windows) ────
        $rateVariance = $this->computeRateVariance($words, $firstStart, $lastEnd);

        // ── Disfluency markers (repetitions + self-corrections) ─────────
        [$repetitions, $selfCorrections] = $this->countDisfluencies($words);

        return [
            'total_words'              => $total,
            'duration_seconds'         => round($durationSec, 2),
            'wpm'                      => round($wpm, 1),
            'pause_count'              => $pauseCount,
            'avg_pause_seconds'        => round($avgPause, 2),
            'long_pause_count'         => $longPauses,
            'longest_pause_seconds'    => round($longestPause, 2),
            'mid_utterance_hesitations' => $midUttHesitations,
            'filler_total'             => $totalFillers,
            'filler_per_100_words'     => round($fillerPer100, 2),
            'filler_breakdown'         => array_filter($fillerCounts, fn($c) => $c > 0),
            'mean_confidence'          => $meanConfidence !== null ? round($meanConfidence, 3) : null,
            'low_confidence_words'     => $hasConfidence ? $lowConfWords : null,
            'low_confidence_per_100'   => $lowConfPer100 !== null ? round($lowConfPer100, 2) : null,
            'speech_rate_std_wpm'      => round($rateVariance, 1),
            'repetition_count'         => $repetitions,
            'self_correction_count'    => $selfCorrections,
            'has_confidence_data'      => $hasConfidence,
        ];
    }

    /**
     * Render the GROUND-TRUTH ACOUSTIC SIGNALS block for prompt injection.
     * Mirrors ScoringService::buildGroundTruthSignalsBlock layout exactly so
     * the LLM treats both blocks the same way (factual evidence, do not
     * re-count).
     */
    public function buildPromptBlock(array $signals): string
    {
        if (empty($signals) || ($signals['total_words'] ?? 0) === 0) {
            return '';
        }

        $wpm = $signals['wpm'];
        $wpmNote = $wpm < 100
            ? 'sustained <100 wpm — Band 5 fluency cue'
            : ($wpm < 130 ? 'moderate pace' : 'fluent pace');

        $fillerLine = sprintf(
            '%d total | %.2f per 100 words',
            $signals['filler_total'],
            $signals['filler_per_100_words']
        );
        if (!empty($signals['filler_breakdown'])) {
            $parts = [];
            foreach ($signals['filler_breakdown'] as $k => $v) {
                $parts[] = "{$k}:{$v}";
            }
            $fillerLine .= ' (' . implode(', ', $parts) . ')';
        }
        if ($signals['filler_per_100_words'] > 15) {
            $fillerLine .= ' — Rule-5 cue: FC <= 5.5';
        }

        $pauseLine = sprintf(
            '%d pauses >0.5s | avg %.2fs | %d long pauses (>2s) | longest %.2fs',
            $signals['pause_count'],
            $signals['avg_pause_seconds'],
            $signals['long_pause_count'],
            $signals['longest_pause_seconds']
        );

        $midUttLine = sprintf(
            '%d mid-utterance hesitations (>=1s pause mid-sentence) — Band 4 descriptor cue if frequent',
            $signals['mid_utterance_hesitations']
        );

        if ($signals['has_confidence_data']) {
            $confLine = sprintf(
                'mean %.3f | %d low-confidence words (<0.70) | %.2f per 100 words',
                $signals['mean_confidence'],
                $signals['low_confidence_words'],
                $signals['low_confidence_per_100']
            );
        } else {
            $confLine = 'n/a (provider did not return per-word confidence — fall back to descriptor-based pronunciation judgement)';
        }

        $rateLine = sprintf(
            '%.1f wpm std-dev across 10s windows (high variance = irregular pacing)',
            $signals['speech_rate_std_wpm']
        );

        $disfluencyLine = sprintf(
            '%d adjacent-word repetitions | %d explicit self-corrections',
            $signals['repetition_count'],
            $signals['self_correction_count']
        );

        $duration = $signals['duration_seconds'];

        return <<<SIG

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
GROUND-TRUTH ACOUSTIC SIGNALS (computed from STT timestamps, not estimated)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Use these factual acoustic measurements as evidence — do NOT re-count these features.

Speaking duration: {$duration}s across {$signals['total_words']} words
Words per minute: {$wpm} ({$wpmNote})
Speech rate variance: {$rateLine}
Pause statistics: {$pauseLine}
Mid-utterance hesitation: {$midUttLine}
Fillers ("uh", "um", "er", "ah", "like", "you know", "sort of", "kind of"): {$fillerLine}
Disfluency markers: {$disfluencyLine}
Per-word confidence (pronunciation proxy): {$confLine}

NOTE: Confidence is an STT-model proxy, not phoneme-level pronunciation analysis.
High low-confidence rates often correlate with mispronunciation or unclear articulation,
but rare slips or accented-but-clear speech can also depress confidence — interpret as
a SIGNAL, not a verdict.

SIG;
    }

    // ── Internals ───────────────────────────────────────────────────────

    private function emptySignals(): array
    {
        return [
            'total_words'              => 0,
            'duration_seconds'         => 0.0,
            'wpm'                      => 0.0,
            'pause_count'              => 0,
            'avg_pause_seconds'        => 0.0,
            'long_pause_count'         => 0,
            'longest_pause_seconds'    => 0.0,
            'mid_utterance_hesitations' => 0,
            'filler_total'             => 0,
            'filler_per_100_words'     => 0.0,
            'filler_breakdown'         => [],
            'mean_confidence'          => null,
            'low_confidence_words'     => null,
            'low_confidence_per_100'   => null,
            'speech_rate_std_wpm'      => 0.0,
            'repetition_count'         => 0,
            'self_correction_count'    => 0,
            'has_confidence_data'      => false,
        ];
    }

    /**
     * Returns the cleaned lowercase form of a word — punctuation stripped,
     * apostrophes preserved (so "don't" stays "don't").
     */
    private function cleanWord(string $text): string
    {
        $t = strtolower(trim($text));
        // Strip leading/trailing punctuation but keep internal apostrophes.
        return trim($t, " \t\n\r\0\x0B.,!?;:\"()[]{}");
    }

    /**
     * Count fillers. Returns an associative array keyed by filler name.
     */
    private function countFillers(array $words): array
    {
        $counts = [
            'uh' => 0, 'um' => 0, 'er' => 0, 'ah' => 0,
            'like' => 0, 'you know' => 0, 'sort of' => 0,
            'kind of' => 0, 'i mean' => 0,
        ];

        $cleaned = array_map(fn($w) => $this->cleanWord((string) $w['text']), $words);

        // Single-word fillers
        foreach ($cleaned as $i => $token) {
            if ($token === '') {
                continue;
            }

            // Canonicalise "uhh", "ummm" etc.
            $canon = $this->canonicaliseFiller($token);
            if ($canon !== null) {
                // Special-case "like": only count when functioning as
                // discourse marker (preceded by something other than a
                // verb-helper). Otherwise it's the verb sense.
                if ($canon === 'like') {
                    $prev = $i > 0 ? $cleaned[$i - 1] : '';
                    if ($prev !== '' && in_array($prev, self::LIKE_VERB_PREDECESSORS, true)) {
                        continue; // verb sense — skip
                    }
                }
                $counts[$canon] = ($counts[$canon] ?? 0) + 1;
            }
        }

        // Multi-word filler phrases — scan adjacent pairs.
        $n = count($cleaned);
        for ($i = 0; $i < $n - 1; $i++) {
            $bigram = $cleaned[$i] . ' ' . $cleaned[$i + 1];
            if (in_array($bigram, self::FILLERS_PHRASE, true)) {
                $counts[$bigram] = ($counts[$bigram] ?? 0) + 1;
            }
        }

        return $counts;
    }

    private function canonicaliseFiller(string $token): ?string
    {
        if ($token === '') {
            return null;
        }
        if (in_array($token, self::FILLERS_SINGLE, true)) {
            // Normalise extended forms.
            if (str_starts_with($token, 'um')) return 'um';
            if (str_starts_with($token, 'uh')) return 'uh';
            if (str_starts_with($token, 'er')) return 'er';
            if (str_starts_with($token, 'ah')) return 'ah';
            if ($token === 'erm') return 'um';
        }
        if ($token === 'like') {
            return 'like';
        }
        return null;
    }

    /**
     * Standard deviation of WPM across rolling self::RATE_WINDOW_SEC windows.
     */
    private function computeRateVariance(array $words, float $firstStart, float $lastEnd): float
    {
        $duration = $lastEnd - $firstStart;
        if ($duration < self::RATE_WINDOW_SEC * 1.5) {
            // Not enough data for a meaningful window-based variance.
            return 0.0;
        }

        $windowWpm = [];
        $windowStart = $firstStart;
        while ($windowStart + self::RATE_WINDOW_SEC <= $lastEnd + 0.001) {
            $windowEnd = $windowStart + self::RATE_WINDOW_SEC;
            $count = 0;
            foreach ($words as $w) {
                $ws = (float) $w['start'];
                if ($ws >= $windowStart && $ws < $windowEnd) {
                    $count++;
                }
            }
            // words in 10s -> wpm
            $windowWpm[] = $count * (60.0 / self::RATE_WINDOW_SEC);
            $windowStart += self::RATE_WINDOW_SEC;
        }

        $n = count($windowWpm);
        if ($n < 2) {
            return 0.0;
        }

        $mean = array_sum($windowWpm) / $n;
        $sumSq = 0.0;
        foreach ($windowWpm as $v) {
            $sumSq += ($v - $mean) ** 2;
        }
        return sqrt($sumSq / $n);
    }

    /**
     * Count adjacent repetitions ("the the", "I I") and explicit
     * self-corrections ("I mean", "sorry", "no wait").
     *
     * @return array{0:int,1:int} [repetitions, self_corrections]
     */
    private function countDisfluencies(array $words): array
    {
        $cleaned = array_map(fn($w) => $this->cleanWord((string) $w['text']), $words);

        $repetitions = 0;
        $n = count($cleaned);
        for ($i = 1; $i < $n; $i++) {
            if ($cleaned[$i] !== '' && $cleaned[$i] === $cleaned[$i - 1]) {
                $repetitions++;
            }
        }

        // Self-correction phrases.
        $selfCorrections = 0;
        $markers = ['i mean', 'sorry', 'no wait', 'i meant', 'or rather'];
        for ($i = 0; $i < $n; $i++) {
            // Single-token markers
            if ($cleaned[$i] === 'sorry') {
                $selfCorrections++;
                continue;
            }
            if ($i < $n - 1) {
                $bigram = $cleaned[$i] . ' ' . $cleaned[$i + 1];
                if (in_array($bigram, $markers, true)) {
                    $selfCorrections++;
                    $i++; // skip the second token of the bigram
                }
            }
        }

        return [$repetitions, $selfCorrections];
    }
}
