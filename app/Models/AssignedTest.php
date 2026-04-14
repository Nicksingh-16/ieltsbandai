<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignedTest extends Model
{
    protected $fillable = [
        'test_template_id', 'institute_id', 'batch_id', 'assigned_by',
        'title', 'instructions', 'due_date', 'is_mandatory', 'allows_retake', 'status',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'is_mandatory' => 'boolean',
        'allows_retake'=> 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function template()
    {
        return $this->belongsTo(TestTemplate::class, 'test_template_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function studentRecords()
    {
        return $this->hasMany(AssignedTestStudent::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'active';
    }

    public function completionRate(): float
    {
        $total = $this->studentRecords()->count();
        if ($total === 0) return 0;
        $done = $this->studentRecords()->where('status', 'completed')->count();
        return round(($done / $total) * 100);
    }

    /** Enroll all students in a batch into this assignment. */
    public function enrollBatch(Batch $batch): void
    {
        $batch->students->each(function (User $student) {
            AssignedTestStudent::firstOrCreate([
                'assigned_test_id' => $this->id,
                'user_id'          => $student->id,
            ], ['status' => 'pending']);
        });
    }
}
