<?php

namespace App\Services;

/**
 * SyntacticComplexityAnalyzer
 * ---------------------------
 * Measures deterministic markers of grammatical range — the features that
 * IELTS examiners reference in the "Grammatical Range and Accuracy" descriptor:
 * subordinate clauses, relative clauses, modal variety, tense range, passive
 * voice, conditional structures, and sentence-length variation.
 *
 * These signals materially separate Band 6 from Band 7+ in the Cambridge
 * calibrated essays (median composite ~7 for Band 6, ~9–11 for Band 7+).
 * They're surface-level so they can't substitute for examiner judgement on
 * idea development or precision, but they give the LLM concrete evidence to
 * push raw scores up when warranted.
 *
 * Output is injected into the writing prompt as part of the GROUND-TRUTH
 * SIGNALS block alongside LinguisticAnalyzer (TTR/CEFR/cohesion) and
 * LanguageTool (grammar errors).
 */
class SyntacticComplexityAnalyzer
{
    private const SUBORDINATORS = [
        'because', 'although', 'though', 'while', 'whereas', 'since',
        'unless', 'until', 'whenever', 'wherever', 'whether', 'when',
        'before', 'after', 'as soon as', 'even though', 'even if',
        'so that', 'in order that', 'provided that', 'rather than', 'in case',
    ];

    private const RELATIVE_MARKERS = ['who', 'whom', 'whose', 'which', 'where'];

    private const MODALS = [
        'would', 'could', 'should', 'might', 'may', 'must',
        'can', 'will', 'shall', 'ought',
    ];

    private const TENSE_PATTERNS = [
        'present_perfect'    => '/\b(have|has)\s+\w+(ed|en)\b/',
        'past_perfect'       => '/\bhad\s+\w+(ed|en)\b/',
        'future'             => '/\b(will|shall)\s+\w+\b/',
        'past_continuous'    => '/\b(was|were)\s+\w+ing\b/',
        'present_continuous' => '/\b(am|is|are)\s+\w+ing\b/',
        'conditional'        => '/\bwould\s+\w+\b/',
    ];

    public function analyze(string $essay): array
    {
        $essay = trim($essay);
        if ($essay === '') {
            return $this->emptySignals();
        }

        $lower = strtolower($essay);

        // Sentence segmentation — naive split on terminal punctuation, with a
        // minimum word length to skip fragments. Good enough for ratios.
        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $essay) ?: [];
        $sentences = array_values(array_filter($sentences, fn($s) => str_word_count($s) >= 3));
        $sentenceCount = max(1, count($sentences));

        $totalWords = max(1, str_word_count($essay));
        $sentLens   = array_map('str_word_count', $sentences);
        $avgSentLen = $sentenceCount > 0 ? array_sum($sentLens) / $sentenceCount : 0;

        $variance = 0.0;
        foreach ($sentLens as $l) $variance += ($l - $avgSentLen) ** 2;
        $sentStdDev = $sentenceCount > 1 ? sqrt($variance / $sentenceCount) : 0.0;

        $subordinatorCount = 0;
        foreach (self::SUBORDINATORS as $s) {
            $subordinatorCount += preg_match_all('/\b' . preg_quote($s, '/') . '\b/', $lower);
        }

        $relativeCount = 0;
        foreach (self::RELATIVE_MARKERS as $r) {
            $relativeCount += preg_match_all('/\b' . $r . '\b/', $lower);
        }

        $distinctModals = 0;
        foreach (self::MODALS as $m) {
            if (preg_match('/\b' . $m . '\b/', $lower)) $distinctModals++;
        }

        $conditionalCount = preg_match_all(
            '/\bif\b[^.]{1,80}\b(would|will|could|should|might|may)\b/',
            $lower
        );

        $passiveCount = preg_match_all(
            '/\b(am|is|are|was|were|been|being|be)\s+\w+(ed|en)\b/',
            $lower
        );

        $participialCount = 0;
        foreach ($sentences as $s) {
            if (preg_match('/^\w+(ing|ed)\b/i', trim($s))) $participialCount++;
        }

        $distinctTenses = 2; // present simple + past simple are baseline
        foreach (self::TENSE_PATTERNS as $rx) {
            if (preg_match($rx, $lower)) $distinctTenses++;
        }

        $punctuationComplexity =
            substr_count($essay, ';') +
            substr_count($essay, ':') +
            preg_match_all('/\s—\s|\s-\s/', $essay) +
            substr_count($essay, '(');

        // Composite — weighted to favour structural variety over raw counts.
        // Calibrated against the Cambridge dataset: Band 6 median ≈ 7,
        // Band 7+ median ≈ 9–11.
        $complexityScore = round(
            ($subordinatorCount / $sentenceCount) * 4 +
            ($relativeCount / $sentenceCount) * 2 +
            $distinctModals * 0.5 +
            $distinctTenses * 0.5 +
            ($sentStdDev / 5) +
            $conditionalCount * 0.5 +
            $participialCount * 0.3,
            2
        );

        return [
            'sentences'             => $sentenceCount,
            'avg_sentence_length'   => round($avgSentLen, 1),
            'sentence_length_stddev'=> round($sentStdDev, 1),
            'subordinators_per_sentence' => round($subordinatorCount / $sentenceCount, 2),
            'relative_clauses_per_sentence' => round($relativeCount / $sentenceCount, 2),
            'distinct_modals'       => $distinctModals,
            'conditional_structures'=> $conditionalCount,
            'passive_voice_count'   => $passiveCount,
            'participial_phrases'   => $participialCount,
            'distinct_tenses'       => $distinctTenses,
            'punctuation_complexity'=> $punctuationComplexity,
            'complexity_score'      => $complexityScore,
        ];
    }

    /**
     * Render the prompt block. Drops into the GROUND-TRUTH SIGNALS section
     * alongside LT and LinguisticAnalyzer output.
     */
    public function buildPromptBlock(array $signals): string
    {
        if (empty($signals) || ($signals['sentences'] ?? 0) === 0) {
            return '';
        }

        // Anchor the composite against calibrated band thresholds so the LLM
        // can use it as concrete evidence, not just an opaque number.
        $score = $signals['complexity_score'];
        $bandHint = match (true) {
            $score >= 11.0 => 'matches Band 7-8 profile (wide structural variety)',
            $score >= 8.5  => 'matches Band 7 profile (good range of structures)',
            $score >= 6.5  => 'matches Band 6-6.5 profile (mix of simple and complex)',
            $score >= 4.0  => 'matches Band 5-6 profile (limited structural range)',
            default        => 'matches Band 4-5 profile (basic structures only)',
        };

        return sprintf(
            "Syntactic complexity: composite score %.2f — %s\n"
          . "  Sentences: %d (avg %.1f words, std-dev %.1f — variety indicator)\n"
          . "  Subordinate clauses per sentence: %.2f (Band 7+ typically ≥0.4)\n"
          . "  Relative clauses per sentence: %.2f\n"
          . "  Distinct modal verbs used: %d / 10 (Band 7+ typically ≥3)\n"
          . "  Distinct tenses used: %d (Band 7+ typically ≥4)\n"
          . "  Conditional structures: %d | Passive voice: %d | Participial openers: %d\n"
          . "  Punctuation complexity (;:—()): %d",
            $signals['complexity_score'],
            $bandHint,
            $signals['sentences'],
            $signals['avg_sentence_length'],
            $signals['sentence_length_stddev'],
            $signals['subordinators_per_sentence'],
            $signals['relative_clauses_per_sentence'],
            $signals['distinct_modals'],
            $signals['distinct_tenses'],
            $signals['conditional_structures'],
            $signals['passive_voice_count'],
            $signals['participial_phrases'],
            $signals['punctuation_complexity']
        );
    }

    private function emptySignals(): array
    {
        return [
            'sentences'             => 0,
            'avg_sentence_length'   => 0.0,
            'sentence_length_stddev'=> 0.0,
            'subordinators_per_sentence' => 0.0,
            'relative_clauses_per_sentence' => 0.0,
            'distinct_modals'       => 0,
            'conditional_structures'=> 0,
            'passive_voice_count'   => 0,
            'participial_phrases'   => 0,
            'distinct_tenses'       => 0,
            'punctuation_complexity'=> 0,
            'complexity_score'      => 0.0,
        ];
    }
}
