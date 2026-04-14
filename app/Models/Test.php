<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'result' => 'array',
        'metadata' => 'array',
        'overall_band' => 'float',
        'duration_seconds' => 'integer',
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function testScores()   { return $this->hasMany(TestScore::class); }
    public function testQuestions(){ return $this->hasMany(TestQuestion::class); }
    public function audioFiles()   { return $this->hasMany(AudioFile::class); }
    public function questions()    { return $this->belongsToMany(Question::class, 'test_questions')->withPivot('part'); }

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

        return match($this->type) {
            'writing'   => route('writing.result', $this->id),
            'listening' => route('listening.result', $this->id),
            'reading'   => route('reading.result', $this->id),
            default     => route('test.result', $this->id), // speaking uses TestResultController
        };
    }

}

