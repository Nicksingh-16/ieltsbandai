<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit-style coverage for ScoringService post-LLM correction pipeline (L5-v6).
 *
 * These tests intentionally exercise the small pure functions:
 *   - calculateOverallBand()  (public)
 *   - applyBiasCorrection()   (protected — via reflection)
 *   - enforceLengthCaps()     (protected — via reflection)
 *   - enforceQuestionPartCoverage() (protected — via reflection)
 *   - parseConfidenceMax()    (protected — via reflection)
 *
 * The end-to-end LLM call is exercised by AIScoringQualityTest separately
 * (which requires a live provider key and is skipped in CI without one).
 */
class ScoringLogicTest extends TestCase
{
    use RefreshDatabase;

    protected ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = app(ScoringService::class);
    }

    /**
     * Helper to invoke protected methods cleanly. Named with the "invoke"
     * prefix to avoid colliding with TestCase::call() (HTTP test helper).
     */
    private function invokeProtected(string $method, array $args)
    {
        $r = new \ReflectionClass($this->scoringService);
        $m = $r->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($this->scoringService, $args);
    }

    // ── calculateOverallBand ──────────────────────────────────────────────

    public function test_overall_band_is_the_mean_of_four_criteria_rounded_to_half()
    {
        $scores = [
            'task_achievement' => 6.5,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];
        // mean = 6.875 → IELTS rounds to nearest 0.5 → 7.0
        $this->assertSame(7.0, $this->scoringService->calculateOverallBand($scores));
    }

    public function test_quarter_boundary_rounds_up_per_ielts_rule()
    {
        // mean = 6.25; IELTS official rule: .25 rounds up to next half band.
        $scores = [
            'task_achievement' => 6.0,
            'coherence_cohesion' => 6.0,
            'lexical_resource' => 6.5,
            'grammar' => 6.5,
        ];
        $this->assertSame(6.5, $this->scoringService->calculateOverallBand($scores));
    }

    public function test_does_not_apply_legacy_per_criterion_cap()
    {
        // L5-v6 removes the prior "any criterion <6 → cap at 6.0" rule.
        // The headline is purely the mean (rounded), with length/coverage
        // caps and confidence-range caps applied as separate steps upstream.
        $scores = [
            'task_achievement' => 5.0,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];
        // mean = 6.5 — no per-criterion cap any more
        $this->assertSame(6.5, $this->scoringService->calculateOverallBand($scores));
    }

    // ── applyBiasCorrection ───────────────────────────────────────────────

    public function test_bias_correction_does_not_shift_up_anymore()
    {
        // Pre-L5-v6 this would have shifted +1.0 (rawMean 5.5 → overall 6.5).
        // Post-L5-v6 there is no upward shift at all.
        $scoring = [
            'task_achievement' => 5.5,
            'coherence_cohesion' => 5.5,
            'lexical_resource' => 5.5,
            'grammar' => 5.5,
            'overall_band' => 5.5,
            'band_confidence_range' => '5.0 – 6.0',
        ];
        $this->invokeProtected('applyBiasCorrection', [&$scoring, [
            'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
        ]]);
        $this->assertSame(5.5, $scoring['overall_band']);
        $this->assertSame(5.5, $scoring['task_achievement']);
    }

    public function test_bias_correction_caps_overall_at_confidence_range_max()
    {
        // LLM said confidence is 5.0–6.0 but raw mean is 6.5.
        // Headline should not exceed the LLM's own confidence maximum.
        $scoring = [
            'task_achievement' => 7.0,
            'coherence_cohesion' => 6.5,
            'lexical_resource' => 6.5,
            'grammar' => 6.0,
            'overall_band' => 6.5,
            'band_confidence_range' => '5.0 – 6.0',
        ];
        $this->invokeProtected('applyBiasCorrection', [&$scoring, [
            'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
        ]]);
        $this->assertLessThanOrEqual(6.0, $scoring['overall_band']);
    }

    public function test_bias_correction_pulls_down_when_errors_per_100w_are_severe()
    {
        $scoring = [
            'task_achievement' => 6.5,
            'coherence_cohesion' => 6.5,
            'lexical_resource' => 6.5,
            'grammar' => 6.5,
            'overall_band' => 6.5,
            'error_summary' => ['grammar_errors_per_100_words' => 20],
            'band_confidence_range' => '6.0 – 7.0',
        ];
        $this->invokeProtected('applyBiasCorrection', [&$scoring, [
            'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
        ]]);
        // 20 errors/100w with raw mean 6.5 → -1.0 shift expected
        $this->assertLessThanOrEqual(5.5, $scoring['overall_band']);
    }

    public function test_bias_correction_no_op_when_disabled()
    {
        config(['services.calibration.bias_correction_enabled' => false]);
        $scoring = [
            'task_achievement' => 5.0,
            'coherence_cohesion' => 5.0,
            'lexical_resource' => 5.0,
            'grammar' => 5.0,
            'overall_band' => 5.0,
        ];
        $original = $scoring;
        $this->invokeProtected('applyBiasCorrection', [&$scoring, [
            'task_achievement', 'coherence_cohesion', 'lexical_resource', 'grammar',
        ]]);
        $this->assertSame($original, $scoring);
    }

    // ── enforceLengthCaps ─────────────────────────────────────────────────

    public function test_under_length_task2_caps_ta_lr_gra_for_very_short_essay()
    {
        $scoring = [
            'task_achievement' => 7.0,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];
        $this->invokeProtected('enforceLengthCaps', [&$scoring, 80, true]);
        $this->assertSame(4.0, $scoring['task_achievement']);
        $this->assertSame(5.0, $scoring['lexical_resource']);
        $this->assertSame(5.0, $scoring['grammar']);
        // CC not capped — coherence can technically be good even in 80 words
        $this->assertSame(7.0, $scoring['coherence_cohesion']);
    }

    public function test_under_length_task2_caps_tr_only_when_just_under_minimum()
    {
        $scoring = [
            'task_achievement' => 7.0,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];
        $this->invokeProtected('enforceLengthCaps', [&$scoring, 240, true]);
        $this->assertSame(6.0, $scoring['task_achievement']);
        // LR/GRA untouched — there's enough text to demonstrate them
        $this->assertSame(7.0, $scoring['lexical_resource']);
    }

    public function test_length_caps_do_not_engage_when_essay_is_long_enough()
    {
        $scoring = [
            'task_achievement' => 8.0,
            'coherence_cohesion' => 8.0,
            'lexical_resource' => 8.0,
            'grammar' => 8.0,
        ];
        $original = $scoring;
        $this->invokeProtected('enforceLengthCaps', [&$scoring, 320, true]);
        $this->assertSame($original, $scoring);
    }

    public function test_length_caps_never_raise_scores()
    {
        // If the LLM already gave a score below the cap, leave it alone.
        $scoring = [
            'task_achievement' => 3.0,
            'lexical_resource' => 4.0,
            'grammar' => 4.0,
            'coherence_cohesion' => 4.0,
        ];
        $this->invokeProtected('enforceLengthCaps', [&$scoring, 80, true]);
        $this->assertSame(3.0, $scoring['task_achievement']);
    }

    // ── enforceQuestionPartCoverage ───────────────────────────────────────

    public function test_two_part_question_with_one_part_missed_caps_tr_at_5_5()
    {
        $scoring = [
            'task_achievement' => 7.0,
            'question_parts' => [
                ['part' => 'Why is this happening?', 'addressed' => true],
                ['part' => 'Is it positive or negative?', 'addressed' => false],
            ],
        ];
        $this->invokeProtected('enforceQuestionPartCoverage', [&$scoring, (object) []]);
        $this->assertSame(5.5, $scoring['task_achievement']);
    }

    public function test_three_part_question_with_two_parts_missed_caps_tr_at_4_5()
    {
        $scoring = [
            'task_achievement' => 7.0,
            'question_parts' => [
                ['part' => 'View A', 'addressed' => true],
                ['part' => 'View B', 'addressed' => false],
                ['part' => 'Your opinion', 'addressed' => false],
            ],
        ];
        $this->invokeProtected('enforceQuestionPartCoverage', [&$scoring, (object) []]);
        $this->assertSame(4.5, $scoring['task_achievement']);
    }

    public function test_single_part_question_is_not_capped()
    {
        $scoring = [
            'task_achievement' => 7.5,
            'question_parts' => [['part' => 'just one', 'addressed' => false]],
        ];
        $this->invokeProtected('enforceQuestionPartCoverage', [&$scoring, (object) []]);
        $this->assertSame(7.5, $scoring['task_achievement']);
    }

    public function test_all_parts_addressed_does_not_cap()
    {
        $scoring = [
            'task_achievement' => 7.5,
            'question_parts' => [
                ['part' => 'p1', 'addressed' => true],
                ['part' => 'p2', 'addressed' => true],
            ],
        ];
        $this->invokeProtected('enforceQuestionPartCoverage', [&$scoring, (object) []]);
        $this->assertSame(7.5, $scoring['task_achievement']);
    }

    // ── parseConfidenceMax ────────────────────────────────────────────────

    public function test_parses_confidence_range_with_various_dashes()
    {
        $this->assertSame(6.0, $this->invokeProtected('parseConfidenceMax', ['5.0 – 6.0']));   // en dash
        $this->assertSame(6.0, $this->invokeProtected('parseConfidenceMax', ['5.0 — 6.0']));   // em dash
        $this->assertSame(6.0, $this->invokeProtected('parseConfidenceMax', ['5.0 - 6.0']));   // hyphen
        $this->assertSame(8.5, $this->invokeProtected('parseConfidenceMax', ['7.0 – 8.5']));
    }

    public function test_returns_null_for_malformed_range_strings()
    {
        $this->assertNull($this->invokeProtected('parseConfidenceMax', [null]));
        $this->assertNull($this->invokeProtected('parseConfidenceMax', ['']));
        $this->assertNull($this->invokeProtected('parseConfidenceMax', ['unknown']));
        $this->assertNull($this->invokeProtected('parseConfidenceMax', ['5.0']));
    }

    // ── enforceTopicRelevance ────────────────────────────────────────────

    public function test_topic_relevance_off_topic_caps_ta_at_band_3_5()
    {
        $scoring = ['task_achievement' => 7.5, 'topic_relevance' => 10];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(3.5, $scoring['task_achievement']);
    }

    public function test_topic_relevance_tangential_caps_ta_at_5()
    {
        $scoring = ['task_achievement' => 7.0, 'topic_relevance' => 35];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(5.0, $scoring['task_achievement']);
    }

    public function test_topic_relevance_partially_relevant_caps_ta_at_6()
    {
        $scoring = ['task_achievement' => 7.5, 'topic_relevance' => 60];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(6.0, $scoring['task_achievement']);
    }

    public function test_topic_relevance_high_does_not_cap()
    {
        $scoring = ['task_achievement' => 8.0, 'topic_relevance' => 90];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(8.0, $scoring['task_achievement']);
    }

    public function test_topic_relevance_missing_or_invalid_skipped()
    {
        $scoring = ['task_achievement' => 7.5];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(7.5, $scoring['task_achievement']);

        $scoring = ['task_achievement' => 7.5, 'topic_relevance' => 'high'];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(7.5, $scoring['task_achievement']);

        $scoring = ['task_achievement' => 7.5, 'topic_relevance' => 150];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(7.5, $scoring['task_achievement']);
    }

    public function test_topic_relevance_never_raises_ta()
    {
        // If LLM already gave low TA, the cap shouldn't bump it back up
        $scoring = ['task_achievement' => 2.0, 'topic_relevance' => 90];
        $this->invokeProtected('enforceTopicRelevance', [&$scoring]);
        $this->assertSame(2.0, $scoring['task_achievement']);
    }

    // ── error pipeline ───────────────────────────────────────────────────

    public function test_normalise_llm_errors_filters_empty_text_and_lowercases_severity()
    {
        $raw = [
            ['text' => '  peoples  ', 'type' => 'GRAMMAR', 'severity' => 'HIGH', 'correction' => 'people'],
            ['text' => '', 'type' => 'Grammar'], // empty — drop
            ['type' => 'Grammar'],               // no text — drop
            ['text' => 'citys', 'type' => 'vocabulary', 'severity' => 'medium', 'correction' => 'cities'],
        ];
        $out = $this->invokeProtected('normaliseLlmErrors', [$raw]);

        $this->assertCount(2, $out);
        $this->assertSame('peoples', $out[0]['text']);
        $this->assertSame('Grammar', $out[0]['type']);
        $this->assertSame('high', $out[0]['severity']);
        $this->assertSame('llm', $out[0]['source']);
    }

    public function test_merge_error_sources_keeps_llm_first_then_appends_lt_only_entries()
    {
        $llm = [
            ['text' => 'peoples', 'type' => 'Grammar', 'severity' => 'high', 'source' => 'llm', 'correction' => 'people', 'explanation' => 'Plural form is people, not peoples'],
        ];
        $lt = [
            ['text' => 'peoples', 'type' => 'Grammar', 'severity' => 'medium', 'source' => 'languagetool', 'offset' => 12, 'length' => 7, 'correction' => 'people', 'explanation' => 'Possible spelling mistake'],
            ['text' => 'recieve', 'type' => 'Vocabulary', 'severity' => 'medium', 'source' => 'languagetool', 'offset' => 80, 'length' => 7, 'correction' => 'receive', 'explanation' => 'Spelling'],
        ];

        $out = $this->invokeProtected('mergeErrorSources', [$llm, $lt]);

        $this->assertCount(2, $out);
        // Same-span entry: LLM kept, enriched with LT position
        $this->assertSame('peoples', $out[0]['text']);
        $this->assertSame(12, $out[0]['offset']);
        $this->assertSame(7, $out[0]['length']);
        $this->assertSame('llm+languagetool', $out[0]['source']);
        $this->assertSame('Plural form is people, not peoples', $out[0]['explanation']); // LLM explanation preserved
        // LT-only entry appended
        $this->assertSame('recieve', $out[1]['text']);
        $this->assertSame('languagetool', $out[1]['source']);
    }

    public function test_group_repeated_errors_collapses_duplicates_with_count()
    {
        $errors = [
            ['text' => 'peoples', 'type' => 'Grammar', 'severity' => 'medium'],
            ['text' => 'citys', 'type' => 'Vocabulary', 'severity' => 'medium'],
            ['text' => 'Peoples', 'type' => 'Grammar', 'severity' => 'high'], // case-insensitive dup
            ['text' => 'peoples', 'type' => 'Grammar', 'severity' => 'low'],
        ];
        $out = $this->invokeProtected('groupRepeatedErrors', [$errors]);

        $this->assertCount(2, $out);
        $this->assertSame('peoples', $out[0]['text']);
        $this->assertSame(3, $out[0]['repeated_count']);
        // Severity promoted to highest seen ('high' from the second peoples)
        $this->assertSame('high', $out[0]['severity']);
        $this->assertSame('citys', $out[1]['text']);
        $this->assertSame(1, $out[1]['repeated_count']);
    }

    public function test_group_repeated_errors_drops_entries_with_blank_text()
    {
        $errors = [
            ['text' => 'peoples'],
            ['text' => '   '],
            ['text' => ''],
        ];
        $out = $this->invokeProtected('groupRepeatedErrors', [$errors]);

        $this->assertCount(1, $out);
        $this->assertSame('peoples', $out[0]['text']);
    }

    // ── prompt construction ──────────────────────────────────────────────

    public function test_task_1_metadata_is_injected_into_prompt()
    {
        $question = Question::factory()->create([
            'category' => 'writing_academic_task1',
            'metadata' => json_encode([
                'chart_type' => 'line',
                'units' => 'percentage',
            ]),
        ]);

        $prompt = $this->invokeProtected('buildWritingScoringPrompt', ['Sample answer', $question]);

        $this->assertStringContainsString('METADATA:', $prompt);
        $this->assertStringContainsString('chart_type', $prompt);
        $this->assertStringContainsString('line', $prompt);
    }
}
