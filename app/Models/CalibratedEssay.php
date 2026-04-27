<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibratedEssay extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'source_book',
        'test_number',
        'task_type',
        'task_description',
        'essay_text',
        'topic_keywords',
        'band_overall',
        'band_ta',
        'band_cc',
        'band_lr',
        'band_gra',
        'examiner_notes',
        'topic_embedding',
        'word_count',
        'is_holdout',
    ];

    protected $casts = [
        'topic_keywords' => 'array',
        'topic_embedding' => 'array',
        'band_overall' => 'float',
        'band_ta' => 'float',
        'band_cc' => 'float',
        'band_lr' => 'float',
        'band_gra' => 'float',
        'is_holdout' => 'boolean',
    ];

    /**
     * Internal use only — never serialize the essay body to user-facing API
     * responses. Cambridge content is licensed for in-context AI calibration only.
     */
    protected $hidden = [
        'essay_text',
        'examiner_notes',
        'topic_embedding',
    ];

    public function scopeForFewShot($query)
    {
        return $query->where('is_holdout', false);
    }

    public function scopeHoldout($query)
    {
        return $query->where('is_holdout', true);
    }

    public function scopeByTaskType($query, string $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    public function scopeNearBand($query, float $targetBand, float $tolerance = 1.0)
    {
        return $query
            ->whereBetween('band_overall', [$targetBand - $tolerance, $targetBand + $tolerance]);
    }

    /**
     * Format this essay as a few-shot example block for LLM prompts.
     *
     * Band 8.5+ essays in the Cambridge dataset are examiner-prepared model
     * answers, not student responses. Tagging them explicitly stops the model
     * from discounting them as "too perfect to be a student" and reduces the
     * systematic under-scoring observed in L3-v1 baseline.
     */
    public function toFewShotBlock(): string
    {
        $bandStr = number_format($this->band_overall, 1);
        $essay = trim($this->essay_text);
        $notes = trim($this->examiner_notes ?? '');
        $tag = $this->band_overall >= 8.5
            ? '(EXAMINER-PREPARED MODEL — target ceiling reference)'
            : '(STUDENT RESPONSE — Cambridge-scored)';

        return <<<EOT
---
EXAMPLE (Band {$bandStr}) {$tag}:

ESSAY:
{$essay}

EXAMINER COMMENTARY:
{$notes}
---
EOT;
    }
}
