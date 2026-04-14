<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Institute extends Model
{
    protected $fillable = [
        'name', 'slug', 'owner_id', 'plan', 'seat_limit', 'seats_used',
        'contact_email', 'phone', 'city', 'gst_number', 'logo', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function booted(): void
    {
        static::creating(function ($institute) {
            $institute->slug = Str::slug($institute->name) . '-' . Str::random(5);
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function questionSets()
    {
        return $this->hasMany(TestTemplate::class);
    }

    public function hasSeatsAvailable(): bool
    {
        return $this->seats_used < $this->seat_limit;
    }

    public function seatsRemaining(): int
    {
        return max(0, $this->seat_limit - $this->seats_used);
    }
}
