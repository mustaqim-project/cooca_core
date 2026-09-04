<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'purchase_order_id',
        'item_type',
        'product_id',
        'material_id',
        'item_name',
        'sku',
        'quantity',
        'invoiced_quantity',
        'unit_id',
        'unit_price',
        'purchase_price_snapshot',
        'supplier_name_snapshot',
        'cost_price_snapshot',
        'subtotal',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'invoiced_quantity' => 'float',
            'unit_price' => 'float',
            'purchase_price_snapshot' => 'float',
            'cost_price_snapshot' => 'float',
            'subtotal' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item): void {
            $item->subtotal = $item->quantity * $item->unit_price;

            // Snapshot harga beli diisi SEKALI saja ketika item pertama kali dibuat.
            // Setelah tersimpan, snapshot tidak akan pernah diperbarui walau unit_price diubah.
            if (! $item->exists && $item->purchase_price_snapshot === null) {
                $item->purchase_price_snapshot = $item->unit_price;
            }

            // Snapshot nama supplier diisi pada saat pembuatan agar histori tidak berubah
            // ketika nama supplier pada master data diperbarui.
            if (! $item->exists && empty($item->supplier_name_snapshot) && $item->purchase_order_id) {
                $supplierName = $item->purchaseOrder?->supplier?->name;
                if ($supplierName) {
                    $item->supplier_name_snapshot = $supplierName;
                }
            }
        });

        static::saved(function (PurchaseOrderItem $item): void {
            $item->purchaseOrder?->recalculateTotals();
        });

        static::deleted(function (PurchaseOrderItem $item): void {
            $item->purchaseOrder?->recalculateTotals();
        });
    }

    /**
     * @return BelongsTo<PurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
