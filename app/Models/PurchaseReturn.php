<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VOID = 'void';

    protected $fillable = ['business_id', 'goods_receipt_id', 'supplier_id', 'supplier_invoice_id', 'location_id', 'return_number', 'return_date', 'reason', 'status', 'total_amount', 'debit_note_number', 'created_by', 'approved_by', 'approved_at', 'completed_by', 'completed_at'];
    protected function casts(): array { return ['return_date' => 'date', 'total_amount' => 'float', 'approved_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function supplierInvoice(): BelongsTo { return $this->belongsTo(SupplierInvoice::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseReturnItem::class); }
}
