<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'item_name',
        'sku',
        'description',
        'quantity',
        'unit_id',
        'unit_price',
        'unit_hpp',
        'subtotal',
        'total_hpp',
        'gross_profit',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'unit_price' => 'float',
            'unit_hpp' => 'float',
            'subtotal' => 'float',
            'total_hpp' => 'float',
            'gross_profit' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item): void {
            $item->subtotal = $item->quantity * $item->unit_price;
            $item->total_hpp = $item->quantity * $item->unit_hpp;
            $item->gross_profit = $item->subtotal - $item->total_hpp;
        });

        static::saved(function (InvoiceItem $item): void {
            $item->invoice?->recalculateTotals();
        });

        static::deleted(function (InvoiceItem $item): void {
            $item->invoice?->recalculateTotals();
        });
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
