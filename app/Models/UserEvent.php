<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'properties', 'session_id', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
