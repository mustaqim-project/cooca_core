<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPromoUsage extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'promo_id',
        'business_id',
        'user_id',
        'subscription_payment_id',
        'order_number',
        'discount_amount',
        'final_paid_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_amount' => 'float',
            'final_paid_amount' => 'float',
        ];
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPromo::class, 'promo_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    // ── Computed Accessors (used by audit views) ─────────────────────────────

    /**
     * Original subtotal before discount (discount + final paid).
     */
    public function getOriginalAmountAttribute(): float
    {
        return round($this->discount_amount + $this->final_paid_amount, 2);
    }

    /**
     * Alias of final_paid_amount for view consistency.
     */
    public function getFinalAmountAttribute(): float
    {
        return (float) $this->final_paid_amount;
    }
}
