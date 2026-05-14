<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'duration_seconds', 'overall_band', 'feedback', 'status',
        'category', 'test_type', 'question_id', 'started_at', 'completed_at',
        'answer', 'score', 'result', 'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'credit_charged_at' => 'datetime',
        'credit_refunded_at' => 'datetime',
        'result' => 'array',
        'metadata' => 'array',
        'overall_band' => 'float',
        'duration_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        // Track test_started on creation.
        static::created(function (Test $test) {
            if ($test->user_id) {
                app(\App\Services\EventTracker::class)->track('test_started', [
                    'test_id' => $test->id,
                    'type' => $test->type,
                    'category' => $test->category,
                ], $test->user);
            }
        });

        // Fire referrer reward + analytics when a test transitions to 'completed'.
        // Idempotent inside ReferralController::creditReferrer — safe to
        // hit on every completion across writing/listening/reading/speaking.
        static::updated(function (Test $test) {
            if ($test->wasChanged('status') && $test->status === 'completed' && $test->user) {
                \App\Http\Controllers\Web\ReferralController::creditReferrer($test->user);
                app(\App\Services\EventTracker::class)->track('test_completed', [
                    'test_id' => $test->id,
                    'type' => $test->type,
                    'overall_band' => $test->overall_band,
                ], $test->user);
            }
            if ($test->wasChanged('status') && $test->status === 'failed') {
                app(\App\Services\EventTracker::class)->track('test_failed', [
                    'test_id' => $test->id,
                    'type' => $test->type,
                ], $test->user);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // NOTE: Eloquent relation methods MUST stay camelCase. Pint will try to
    // snake_case them (php_unit_method_casing or similar) — see .pint.json
    // exclude-rule, and never let it through. Renaming breaks every callsite
    // that uses $test->testScores / $test->testQuestions across views,
    // controllers, services, jobs, and the test_scores DB table is unrelated.
    public function testScores() // @phpstan-ignore-line
    {
        return $this->hasMany(TestScore::class);
    }

    public function testQuestions() // @phpstan-ignore-line
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'test_questions')->withPivot('part');
    }

    /** Universal band getter */
    public function getBandAttribute()
    {
        return $this->overall_band ?? $this->score ?? null;
    }

    /** Universal result page route */
    public function getResultRouteAttribute(): string
    {
        if ($this->status !== 'completed') {
            return route('dashboard');
        }

        return match ($this->type) {
            'writing' => route('writing.result', $this->id),
            'listening' => route('listening.result', $this->id),
            'reading' => route('reading.result', $this->id),
            default => route('test.result', $this->id), // speaking uses TestResultController
        };
    }
}
