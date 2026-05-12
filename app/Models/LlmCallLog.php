<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmCallLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'test_id', 'provider', 'model', 'purpose', 'prompt_version',
        'input_tokens', 'output_tokens', 'cost_usd',
        'http_status', 'latency_ms', 'ok', 'created_at',
    ];

    protected $casts = [
        'input_tokens'  => 'integer',
        'output_tokens' => 'integer',
        'cost_usd'      => 'float',
        'http_status'   => 'integer',
        'latency_ms'    => 'integer',
        'ok'            => 'boolean',
        'created_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
