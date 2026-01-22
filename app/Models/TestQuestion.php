<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question_id',
        'part',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
