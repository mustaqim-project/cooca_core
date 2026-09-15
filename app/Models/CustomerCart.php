<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCart extends Model
{
    use HasUuids;

    protected $fillable = [
        'global_customer_id',
        'business_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<GlobalCustomer, $this> */
    public function globalCustomer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return HasMany<CustomerCartItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CustomerCartItem::class, 'cart_id');
    }

    /** Total harga semua item di cart. */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->quantity * $i->unit_price);
    }

    /** Jumlah item di cart. */
    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
