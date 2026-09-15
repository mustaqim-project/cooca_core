<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'commerce_order_items';

    protected $fillable = [
        'commerce_order_id',
        'product_id',
        'product_name',
        'product_type',
        'unit_price',
        'quantity',
        'subtotal',
        'notes',
        'modifiers_snapshot',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'quantity' => 'float',
            'subtotal' => 'float',
            'modifiers_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
