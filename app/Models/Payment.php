<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',        // human-readable ref, e.g. IBAI-A7K9P2
        'payment_id',      // gateway transaction id (Razorpay payment_id) — unused for manual UPI
        'amount',
        'currency',
        'status',          // pending | pending_verification | completed | failed | refunded
        'plan',            // plan key from config/plans.php
        'method',          // razorpay | manual
        'proof_id',        // user-submitted UTR for manual UPI
        'admin_note',
        'granted_at',
        'verified_at',
        'verified_by',
        'credits_granted',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'granted_at'      => 'datetime',
        'verified_at'     => 'datetime',
        'credits_granted' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /** Resolve plan config — single source of truth for prices/features. */
    public function planConfig(): ?array
    {
        return config("plans.one_time.{$this->plan}")
            ?? config("plans.subscription.{$this->plan}");
    }

    public function isSubscriptionPlan(): bool
    {
        return (bool) config("plans.subscription.{$this->plan}");
    }
}