<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'institute_id', 'name', 'description', 'test_type',
        'target_band', 'exam_date', 'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'exam_date'   => 'date',
        'target_band' => 'decimal:1',
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'batch_user')->withTimestamps();
    }

    public function studentCount(): int
    {
        return $this->students()->count();
    }

    public function averageBand(): ?float
    {
        // Pull latest test per student and average overall_band
        $scores = $this->students()
            ->with(['tests' => fn($q) => $q->whereNotNull('overall_band')->latest()->limit(1)])
            ->get()
            ->map(fn($u) => optional($u->tests->first())->overall_band)
            ->filter();

        return $scores->isEmpty() ? null : round($scores->avg() * 2) / 2;
    }
}
