<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TestTemplate extends Model
{
    protected $fillable = [
        'institute_id', 'created_by', 'name', 'slug', 'description',
        'type', 'duration_minutes', 'is_active', 'is_public', 'metadata',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_public'  => 'boolean',
        'metadata'   => 'array',
    ];

    public static function booted(): void
    {
        static::creating(function (self $t) {
            if (empty($t->slug)) {
                $t->slug = Str::slug($t->name) . '-' . Str::random(5);
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'template_questions')
                    ->withPivot('order', 'config')
                    ->orderByPivot('order');
    }

    public function templateQuestions()
    {
        return $this->hasMany(TemplateQuestion::class)->orderBy('order');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function assignments()
    {
        return $this->hasMany(AssignedTest::class);
    }

    /** True if this is a platform-wide (master/B2C) set, not institute-specific. */
    public function isGlobal(): bool
    {
        return $this->institute_id === null;
    }
}
