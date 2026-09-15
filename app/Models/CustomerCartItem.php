<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCartItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'notes',
        'selected_modifiers',
    ];

    protected function casts(): array
    {
        return [
            'quantity'           => 'float',
            'unit_price'         => 'float',
            'selected_modifiers' => 'array',
        ];
    }

    /** @return BelongsTo<CustomerCart, $this> */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CustomerCart::class, 'cart_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute(): float
    {
        return round($this->quantity * $this->unit_price, 2);
    }
}
