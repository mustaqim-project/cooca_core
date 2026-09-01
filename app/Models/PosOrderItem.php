<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'pos_order_id',
        'product_id',
        'product_name',
        'product_code',
        'unit_price',
        'unit_cost_hpp',
        'quantity',
        'subtotal',
        'discount_amount',
        'total_price',
        'total_hpp',
        'batch_number',
        'serial_number',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'unit_cost_hpp' => 'float',
            'quantity' => 'float',
            'subtotal' => 'float',
            'discount_amount' => 'float',
            'total_price' => 'float',
            'total_hpp' => 'float',
        ];
    }

    /**
     * @return BelongsTo<PosOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
