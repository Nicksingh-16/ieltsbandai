<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id', 'type', 'category', 'title', 'content',
        'media_url', 'time_limit', 'min_words', 'active', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'active'   => 'boolean',
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function templates()
    {
        return $this->belongsToMany(TestTemplate::class, 'template_questions')
                    ->withPivot('order', 'config');
    }

    /** True if this question belongs to the global/master bank. */
    public function isGlobal(): bool
    {
        return $this->institute_id === null;
    }
}
