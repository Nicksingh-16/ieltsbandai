<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignedTestStudent extends Model
{
    protected $fillable = [
        'assigned_test_id', 'user_id', 'test_id', 'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssignedTest::class, 'assigned_test_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
