<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceGroupOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'commerce_group_order_items';

    protected $fillable = [
        'group_order_id',
        'global_customer_id',
        'member_name',
        'product_id',
        'quantity',
        'unit_price',
        'notes',
        'selected_modifiers',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity'           => 'float',
            'unit_price'         => 'float',
            'selected_modifiers' => 'array',
        ];
    }

    public function groupOrder(): BelongsTo
    {
        return $this->belongsTo(CommerceGroupOrder::class, 'group_order_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getLineTotalAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    public function isOwnedBy(?GlobalCustomer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        return $this->global_customer_id === $customer->id;
    }
}
