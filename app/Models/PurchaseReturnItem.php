<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['purchase_return_id', 'goods_receipt_item_id', 'product_id', 'item_name', 'quantity', 'unit_cost', 'subtotal'];
    protected function casts(): array { return ['quantity' => 'float', 'unit_cost' => 'float', 'subtotal' => 'float']; }
    public function purchaseReturn(): BelongsTo { return $this->belongsTo(PurchaseReturn::class); }
    public function goodsReceiptItem(): BelongsTo { return $this->belongsTo(GoodsReceiptItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
