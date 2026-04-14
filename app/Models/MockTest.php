<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockTest extends Model
{
    protected $fillable = [
        'user_id', 'test_type', 'status', 'current_module',
        'listening_test_id', 'reading_test_id', 'writing_test_id', 'speaking_test_id',
        'listening_band', 'reading_band', 'writing_band', 'speaking_band',
        'overall_band', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Module order for sequential flow
    const MODULES = ['listening', 'reading', 'writing', 'speaking'];

    public function user()       { return $this->belongsTo(User::class); }
    public function listening()  { return $this->belongsTo(Test::class, 'listening_test_id'); }
    public function reading()    { return $this->belongsTo(Test::class, 'reading_test_id'); }
    public function writing()    { return $this->belongsTo(Test::class, 'writing_test_id'); }
    public function speaking()   { return $this->belongsTo(Test::class, 'speaking_test_id'); }

    public function nextModule(): ?string
    {
        $idx = array_search($this->current_module, self::MODULES);
        return self::MODULES[$idx + 1] ?? null;
    }

    public function isComplete(): bool
    {
        return $this->speaking_test_id !== null
            && $this->status === 'completed';
    }

    public function calculateOverall(): float
    {
        $bands = array_filter([
            $this->listening_band,
            $this->reading_band,
            $this->writing_band,
            $this->speaking_band,
        ]);

        if (empty($bands)) return 0;

        return round(array_sum($bands) / count($bands) * 2) / 2;
    }

    public function moduleTestId(string $module): ?int
    {
        return $this->{$module . '_test_id'};
    }

    public function routeForModule(string $module): string
    {
        $testId = $this->moduleTestId($module);
        if (!$testId) return '#';

        return match($module) {
            'listening' => route('listening.result', $testId),   // after submit
            'reading'   => route('reading.result', $testId),
            'writing'   => route('writing.result', $testId),
            'speaking'  => route('test.result', $testId),
            default     => '#',
        };
    }
}
