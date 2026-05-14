<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'criteria',
        'band_score',
        'comments',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
