<?php
/**
 * Throwaway prototype: measure syntactic complexity features across the
 * Cambridge calibrated essays and report whether they discriminate Band 6
 * from Band 8.5. If they do, justifies building a SyntacticComplexityAnalyzer.
 * If they don't, the multi-stage LLM rebuild is the only real path.
 *
 * Run: php scripts/analyze_syntactic_complexity.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function analyzeSyntax(string $essay): array
{
    $essay = (string) $essay;
    $lower = strtolower($essay);

    // Sentence segmentation — naive but good enough for ratios.
    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', trim($essay)) ?: [];
    $sentences = array_filter($sentences, fn($s) => str_word_count($s) >= 3);
    $sentences = array_values($sentences);
    $sentenceCount = max(1, count($sentences));

    $totalWords = max(1, str_word_count($essay));
    $sentLens   = array_map('str_word_count', $sentences);
    $avgSentLen = array_sum($sentLens) / $sentenceCount;
    $variance   = 0;
    foreach ($sentLens as $l) $variance += ($l - $avgSentLen) ** 2;
    $sentStdDev = $sentenceCount > 1 ? sqrt($variance / $sentenceCount) : 0;

    // Subordinators — markers of complex sentence structure
    $subordinators = ['because', 'although', 'though', 'while', 'whereas', 'since',
        'unless', 'until', 'whenever', 'wherever', 'whether', 'if', 'when',
        'before', 'after', 'as soon as', 'even though', 'even if', 'so that',
        'in order that', 'provided that', 'rather than', 'in case'];
    $subordinatorCount = 0;
    foreach ($subordinators as $s) {
        $subordinatorCount += preg_match_all('/\b' . preg_quote($s, '/') . '\b/', $lower);
    }

    // Relative clauses
    $relativeMarkers = ['who', 'whom', 'whose', 'which', 'that', 'where'];
    $relativeCount = 0;
    foreach ($relativeMarkers as $r) {
        $relativeCount += preg_match_all('/\b' . $r . '\b/', $lower);
    }

    // Modal verbs — distinct count is more informative than total
    $modals = ['would', 'could', 'should', 'might', 'may', 'must', 'can', 'will', 'shall', 'ought'];
    $distinctModals = 0;
    foreach ($modals as $m) {
        if (preg_match('/\b' . $m . '\b/', $lower)) $distinctModals++;
    }

    // Conditional structures
    $conditionalIfThen = preg_match_all('/\bif\b[^.]{1,80}\b(would|will|could|should|might|may)\b/', $lower);

    // Passive voice — (be|am|is|are|was|were|been|being) + past participle
    $passiveCount = preg_match_all('/\b(am|is|are|was|were|been|being|be)\s+\w+(ed|en)\b/', $lower);

    // Participial phrase starters (-ing or -ed at sentence start)
    $participialCount = 0;
    foreach ($sentences as $s) {
        if (preg_match('/^\w+(ing|ed)\b/i', trim($s))) $participialCount++;
    }

    // Tense variety — heuristic per-tense detection
    $tenses = [
        'present_perfect' => '/\b(have|has)\s+\w+(ed|en)\b/',
        'past_perfect'    => '/\bhad\s+\w+(ed|en)\b/',
        'future'          => '/\b(will|shall)\s+\w+\b/',
        'past_continuous' => '/\b(was|were)\s+\w+ing\b/',
        'present_continuous' => '/\b(am|is|are)\s+\w+ing\b/',
        'conditional'     => '/\bwould\s+\w+\b/',
    ];
    $distinctTenses = 0;
    foreach ($tenses as $name => $rx) {
        if (preg_match($rx, $lower)) $distinctTenses++;
    }
    // Past simple + present simple are always present in any essay > 5 sentences
    $distinctTenses += 2;

    // Punctuation complexity
    $semicolonCount = substr_count($essay, ';');
    $colonCount     = substr_count($essay, ':');
    $dashCount      = preg_match_all('/\s—\s|\s-\s/', $essay);
    $parenCount     = substr_count($essay, '(');

    return [
        'words'             => $totalWords,
        'sentences'         => $sentenceCount,
        'avg_sent_len'      => round($avgSentLen, 1),
        'sent_len_std'      => round($sentStdDev, 1),
        'subord_per_sent'   => round($subordinatorCount / $sentenceCount, 2),
        'relative_per_sent' => round($relativeCount / $sentenceCount, 2),
        'distinct_modals'   => $distinctModals,
        'conditionals'      => $conditionalIfThen,
        'passive_count'     => $passiveCount,
        'participial'       => $participialCount,
        'distinct_tenses'   => $distinctTenses,
        'punct_complex'     => $semicolonCount + $colonCount + $dashCount + $parenCount,
        // Composite — high values should correlate with Band 7+
        'complexity_score'  => round(
            ($subordinatorCount / $sentenceCount) * 4 +
            ($relativeCount / $sentenceCount) * 2 +
            $distinctModals * 0.5 +
            $distinctTenses * 0.5 +
            ($sentStdDev / 5) +
            $conditionalIfThen * 0.5 +
            $participialCount * 0.3
        , 2),
    ];
}

$essays = \App\Models\CalibratedEssay::whereIn('band_overall', [6.0, 6.5, 7.0, 7.5, 8.5])
    ->where('task_type', 'LIKE', 'writing%')
    ->orderBy('band_overall')
    ->get();

$byBand = [];
echo str_pad('Band', 5) . ' | ' . str_pad('ID', 18) . ' | sent | len  | std  | sub/s | rel/s | mod | tens | comp_score' . PHP_EOL;
echo str_repeat('-', 110) . PHP_EOL;

foreach ($essays as $e) {
    $f = analyzeSyntax($e->essay_text);
    $byBand[(string) $e->band_overall][] = $f['complexity_score'];

    echo sprintf(
        '%-5s | %-18s | %4d | %4.1f | %4.1f | %5.2f | %5.2f | %3d | %4d | %5.2f',
        $e->band_overall,
        substr($e->external_id, 0, 18),
        $f['sentences'],
        $f['avg_sent_len'],
        $f['sent_len_std'],
        $f['subord_per_sent'],
        $f['relative_per_sent'],
        $f['distinct_modals'],
        $f['distinct_tenses'],
        $f['complexity_score']
    ) . PHP_EOL;
}

echo PHP_EOL . 'COMPLEXITY SCORE DISTRIBUTION BY BAND' . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;
ksort($byBand);
foreach ($byBand as $band => $scores) {
    sort($scores);
    $min    = min($scores);
    $max    = max($scores);
    $median = $scores[(int) (count($scores) / 2)];
    $mean   = array_sum($scores) / count($scores);
    echo sprintf(
        'Band %-4s (n=%2d) | min=%5.2f | median=%5.2f | mean=%5.2f | max=%5.2f',
        $band, count($scores), $min, $median, $mean, $max
    ) . PHP_EOL;
}
