<?php

namespace App\Services;

/**
 * Computes deterministic linguistic signals for an essay so the LLM doesn't
 * have to count or guess them. These signals get injected into the writing
 * prompt as a GROUND-TRUTH block to constrain weaker free-tier models.
 *
 * What this measures:
 *   - Type-Token Ratio (lexical diversity proxy)
 *   - CEFR vocabulary distribution (A1-C2 percentages)
 *   - Cohesion-marker count + list (IELTS-recognized linking devices)
 *   - Word count
 *   - Average word length
 *
 * What this does NOT measure: grammar/spelling errors. Those come from
 * LanguageToolClient, which calls a self-hosted Java service.
 */
class LinguisticAnalyzer
{
    /** @var array<string, string>|null */
    private static ?array $cefrIndex = null;

    /** Common IELTS cohesive devices (no exhaustive list — covers high-signal items) */
    private const COHESION_MARKERS = [
        // Addition
        'furthermore', 'moreover', 'additionally', 'in addition', 'also', 'besides',
        'as well as', 'not only', 'but also',
        // Contrast
        'however', 'nevertheless', 'nonetheless', 'on the other hand',
        'in contrast', 'on the contrary', 'whereas', 'although', 'though', 'despite',
        'in spite of', 'yet', 'while', 'conversely',
        // Cause / consequence
        'therefore', 'thus', 'consequently', 'as a result', 'hence', 'accordingly',
        'because', 'because of', 'due to', 'owing to', 'since', 'as',
        // Examples
        'for example', 'for instance', 'such as', 'including', 'namely', 'particularly',
        'in particular', 'specifically',
        // Sequence
        'firstly', 'secondly', 'thirdly', 'finally', 'lastly', 'first of all', 'to begin with',
        'next', 'then', 'subsequently', 'meanwhile',
        // Conclusion / summary
        'in conclusion', 'to conclude', 'in summary', 'to sum up', 'overall',
        'in short', 'all in all',
        // Emphasis
        'indeed', 'in fact', 'clearly', 'obviously', 'undoubtedly', 'certainly',
        // Comparison
        'similarly', 'likewise', 'in the same way', 'equally',
        // Concession
        'admittedly', 'of course', 'naturally',
    ];

    /**
     * Run all analyses and return a flat result array.
     *
     * @return array{
     *   word_count: int,
     *   unique_word_count: int,
     *   ttr: float,
     *   avg_word_length: float,
     *   cefr_distribution: array<string,float>,
     *   cohesion_markers: array{count:int, found:array<int,string>}
     * }
     */
    public function analyze(string $essay): array
    {
        $tokens = $this->tokenize($essay);
        $totalTokens = count($tokens);
        $unique = array_unique($tokens);
        $uniqueCount = count($unique);

        $ttr = $totalTokens > 0 ? round($uniqueCount / $totalTokens, 3) : 0.0;
        $avgLen = $totalTokens > 0 ? round(array_sum(array_map('strlen', $tokens)) / $totalTokens, 2) : 0.0;

        return [
            'word_count' => $totalTokens,
            'unique_word_count' => $uniqueCount,
            'ttr' => $ttr,
            'avg_word_length' => $avgLen,
            'cefr_distribution' => $this->computeCEFRDistribution($tokens),
            'cohesion_markers' => $this->countCohesionMarkers($essay),
        ];
    }

    /**
     * Type-Token Ratio = unique tokens / total tokens.
     * Band 7+ writing typically lands above 0.5; below 0.4 indicates lexical poverty.
     */
    public function computeTTR(string $essay): float
    {
        $tokens = $this->tokenize($essay);
        $total = count($tokens);
        if ($total === 0) {
            return 0.0;
        }
        return round(count(array_unique($tokens)) / $total, 3);
    }

    /**
     * Returns percentage distribution across A1, A2, B1, B2, C1, C2.
     * Tokens not in any wordlist are bucketed as C2 (rare/sophisticated/unknown).
     *
     * @param array<int,string>|string $input
     * @return array<string,float> Percentages summing to ~100.0
     */
    public function computeCEFRDistribution($input): array
    {
        $tokens = is_array($input) ? $input : $this->tokenize($input);
        $index = $this->loadCefrIndex();
        $buckets = ['A1' => 0, 'A2' => 0, 'B1' => 0, 'B2' => 0, 'C1' => 0, 'C2' => 0];
        $total = 0;

        foreach ($tokens as $tok) {
            $level = $index[$tok] ?? 'C2'; // unknown words bucket as C2 (advanced)
            $buckets[$level]++;
            $total++;
        }

        if ($total === 0) {
            return array_map(fn () => 0.0, $buckets);
        }

        return array_map(fn ($n) => round(($n / $total) * 100, 1), $buckets);
    }

    /**
     * Counts IELTS cohesion markers detected in the essay (case-insensitive,
     * boundary-respecting).
     *
     * @return array{count:int, found:array<int,string>}
     */
    public function countCohesionMarkers(string $essay): array
    {
        $lower = ' ' . strtolower($essay) . ' ';
        $found = [];
        foreach (self::COHESION_MARKERS as $marker) {
            // Bound by spaces / punctuation to avoid matching substrings (e.g.
            // "as" inside "always"). Allow comma/period/space at boundaries.
            $pattern = '/(^|\W)' . preg_quote($marker, '/') . '(\W|$)/i';
            $count = preg_match_all($pattern, $essay, $_);
            if ($count > 0) {
                $found[] = $marker;
            }
        }
        return [
            'count' => count($found),
            'found' => $found,
        ];
    }

    /**
     * Lowercase, strip punctuation, split on whitespace. Drops empty tokens.
     * @return array<int,string>
     */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        // Keep apostrophes inside words (don't, it's), strip everything else.
        $text = preg_replace("/[^a-z'\\s]/", ' ', $text) ?? '';
        $tokens = preg_split('/\s+/', trim($text)) ?: [];
        return array_values(array_filter($tokens, fn ($t) => $t !== '' && strlen($t) >= 1));
    }

    /**
     * Load all CEFR wordlists into a single token => level map. Cached
     * per-process so subsequent calls are O(1).
     *
     * @return array<string,string>
     */
    private function loadCefrIndex(): array
    {
        if (self::$cefrIndex !== null) {
            return self::$cefrIndex;
        }

        $dir = base_path('resources/wordlists');
        $files = ['A1' => 'cefr_a1.txt', 'A2' => 'cefr_a2.txt', 'B1' => 'cefr_b1.txt', 'B2' => 'cefr_b2.txt', 'C1' => 'cefr_c1.txt'];
        $index = [];

        foreach ($files as $level => $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($path)) {
                continue;
            }
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $word) {
                $word = strtolower(trim($word));
                if ($word === '' || isset($index[$word])) {
                    continue; // first list wins (A1 over A2 etc.)
                }
                $index[$word] = $level;
            }
        }

        self::$cefrIndex = $index;
        return $index;
    }
}
