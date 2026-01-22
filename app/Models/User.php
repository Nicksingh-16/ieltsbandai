<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get all payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if user has an active subscription.
     *
     * @return bool
     */
    public function hasActiveSubscription()
    {
        $subscription = $this->subscription;
        
        if (!$subscription) {
            return false;
        }

        return $subscription->status === 'active' 
            && $subscription->ends_at 
            && $subscription->ends_at->isFuture();
    }

    /**
     * Get the active subscription.
     *
     * @return Subscription|null
     */
    public function activeSubscription()
    {
        return $this->subscription()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();
    }

    /**
     * Check if user is on pro plan.
     *
     * @return bool
     */
    public function isPro()
    {
        return $this->hasActiveSubscription();
    }

    /**
     * Check if user is on free plan.
     *
     * @return bool
     */
    public function isFree()
    {
        return !$this->hasActiveSubscription();
    }

    /**
     * Get user's daily test count.
     *
     * @return int
     */
    public function dailyTestCount()
    {
        // Count tests taken today
        return $this->tests()
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Check if user can take more tests today.
     *
     * @return bool
     */
    public function canTakeTest()
    {
        if ($this->hasActiveSubscription()) {
            return true; // Pro users have unlimited tests
        }

        // Free users limited to 1 test per day
        return $this->dailyTestCount() < 1;
    }

    /**
     * Get remaining tests for today.
     *
     * @return int
     */
    public function remainingTests()
    {
        if ($this->hasActiveSubscription()) {
            return PHP_INT_MAX; // Unlimited for pro users
        }

        return max(0, 1 - $this->dailyTestCount());
    }

    /**
     * Get all tests for the user.
     */
    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}