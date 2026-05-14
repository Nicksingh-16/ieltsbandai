<?php

namespace Tests\Feature;

use App\Models\CalibratedEssay;
use Tests\TestCase;

/**
 * Cambridge IELTS content is licensed for internal LLM calibration only.
 * The model's $hidden array MUST NOT regress — if a future dev removes the
 * hidden fields, this test fails loudly so the leak is caught before the
 * essay body or examiner notes can land in any user-facing API response.
 *
 * Uses an unsaved model instance so the test runs against any DB connection
 * (project-wide migrations are not all sqlite-compatible). The protection
 * being tested is a model-layer concern (toArray/toJson honour $hidden),
 * not a DB concern.
 */
class CalibratedEssayPrivacyTest extends TestCase
{
    private function makeEssay(): CalibratedEssay
    {
        $essay = new CalibratedEssay;
        $essay->forceFill([
            'id' => 1,
            'external_id' => 'test_privacy_w1',
            'source_book' => 'Cambridge IELTS Test',
            'test_number' => 1,
            'task_type' => 'writing_task_2',
            'task_description' => 'Test prompt',
            'essay_text' => 'SECRET ESSAY BODY — must not leak.',
            'topic_keywords' => ['privacy', 'test'],
            'band_overall' => 7.0,
            'band_ta' => 7.0,
            'band_cc' => 7.0,
            'band_lr' => 7.0,
            'band_gra' => 7.0,
            'examiner_notes' => 'SECRET EXAMINER NOTES — must not leak.',
            'topic_embedding' => [0.1, 0.2, 0.3],
            'word_count' => 250,
            'is_holdout' => false,
        ]);

        return $essay;
    }

    public function test_essay_text_is_hidden_from_serialization(): void
    {
        $essay = $this->makeEssay();

        $array = $essay->toArray();
        $json = $essay->toJson();

        $this->assertArrayNotHasKey('essay_text', $array, 'essay_text leaked to toArray()');
        $this->assertArrayNotHasKey('examiner_notes', $array, 'examiner_notes leaked to toArray()');
        $this->assertArrayNotHasKey('topic_embedding', $array, 'topic_embedding leaked to toArray()');

        $this->assertStringNotContainsString('SECRET ESSAY BODY', $json, 'essay body leaked to toJson()');
        $this->assertStringNotContainsString('SECRET EXAMINER NOTES', $json, 'examiner notes leaked to toJson()');
        $this->assertStringNotContainsString('"essay_text"', $json);
        $this->assertStringNotContainsString('"examiner_notes"', $json);
        $this->assertStringNotContainsString('"topic_embedding"', $json);
    }

    public function test_collection_serialization_does_not_leak_essay_text(): void
    {
        $essays = collect([$this->makeEssay(), $this->makeEssay(), $this->makeEssay()]);
        $json = $essays->toJson();

        $this->assertStringNotContainsString('SECRET ESSAY BODY', $json);
        $this->assertStringNotContainsString('SECRET EXAMINER NOTES', $json);
        $this->assertStringNotContainsString('"essay_text"', $json);
        $this->assertStringNotContainsString('"examiner_notes"', $json);
    }
}
