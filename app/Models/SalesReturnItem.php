<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['sales_return_id', 'invoice_item_id', 'pos_order_item_id', 'product_id', 'item_name', 'quantity', 'unit_price', 'unit_hpp', 'subtotal'];
    protected function casts(): array { return ['quantity' => 'float', 'unit_price' => 'float', 'unit_hpp' => 'float', 'subtotal' => 'float']; }
    public function salesReturn(): BelongsTo { return $this->belongsTo(SalesReturn::class); }
    public function invoiceItem(): BelongsTo { return $this->belongsTo(InvoiceItem::class); }
    public function posOrderItem(): BelongsTo { return $this->belongsTo(PosOrderItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
