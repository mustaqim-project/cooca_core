<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VOID = 'void';

    public const REFUND_CREDIT_NOTE = 'credit_note';
    public const REFUND_CASH = 'cash_refund';
    public const REFUND_STORE_CREDIT = 'store_credit';

    protected $fillable = ['business_id', 'invoice_id', 'pos_order_id', 'customer_id', 'location_id', 'return_number', 'return_date', 'reason', 'status', 'total_amount', 'refund_method', 'created_by', 'approved_by', 'approved_at', 'completed_by', 'completed_at'];

    protected function casts(): array
    {
        return ['return_date' => 'date', 'total_amount' => 'float', 'approved_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function posOrder(): BelongsTo { return $this->belongsTo(PosOrder::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function items(): HasMany { return $this->hasMany(SalesReturnItem::class); }
}
