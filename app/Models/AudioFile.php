<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AudioFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'file_url',
        'duration_seconds',
        'size_kb',
        'transcript',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
