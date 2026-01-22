<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'title',
        'content',
        'media_url',
        'time_limit',
        'min_words',
        'active',
        'metadata',
    ];

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class);
    }
}
