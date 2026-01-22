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

    public function user()   { return $this->belongsTo(User::class); }
    public function testScores() { return $this->hasMany(TestScore::class); }
    public function testQuestions() { return $this->hasMany(TestQuestion::class); }
    public function audioFiles() { return $this->hasMany(AudioFile::class); }

    /** Universal band getter */
    public function getBandAttribute()
    {
        return $this->overall_band ?? $this->score ?? null;
    }

    /** Universal result page route */
   public function getResultRouteAttribute()
{
    // Only allow result pages for completed tests
    if ($this->status !== 'completed') {
        return route('dashboard');
    }

    if ($this->type === 'writing') {
        return route('writing.result', $this->id);
    }

    if ($this->type === 'speaking') {
        return route('speaking.result', $this->id);
    }

    // fallback
    return route('dashboard');
}

}

