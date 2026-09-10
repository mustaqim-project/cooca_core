<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_DRAFT_HELD = 'draft_held';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_SERVED = 'served';

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_VOIDED = 'voided';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_PARTIAL_REFUND = 'partial_refund';

    public const SOURCE_POS = 'pos';

    public const SOURCE_QR_TABLE = 'qr_table';

    protected $fillable = [
        'business_id',
        'location_id',
        'pos_shift_id',
        'user_id',
        'customer_id',
        'order_number',
        'order_date',
        'status',
        'order_type',
        'order_source',
        'pos_table_id',
        'pos_table_session_id',
        'table_or_reference',
        'customer_name_guest',
        'customer_phone_guest',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'voucher_code',
        'voucher_discount_amount',
        'tax_percentage',
        'tax_amount',
        'service_charge_percentage',
        'service_charge_amount',
        'rounding_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'total_hpp_cost',
        'total_gross_profit',
        'points_earned',
        'points_redeemed',
        'points_discount_amount',
        'hold_label',
        'held_at',
        'void_reason',
        'voided_by',
        'voided_at',
        'refund_reason',
        'refunded_by',
        'refunded_at',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'supervisor_approved_by',
        'supervisor_approved_at',
        'notes',
    ];


    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'held_at' => 'datetime',
            'voided_at' => 'datetime',
            'refunded_at' => 'datetime',
            'rejected_at' => 'datetime',
            'supervisor_approved_at' => 'datetime',
            'subtotal' => 'float',
            'discount_value' => 'float',
            'discount_amount' => 'float',
            'voucher_discount_amount' => 'float',
            'tax_percentage' => 'float',
            'tax_amount' => 'float',
            'service_charge_percentage' => 'float',
            'service_charge_amount' => 'float',
            'rounding_amount' => 'float',
            'total_amount' => 'float',
            'paid_amount' => 'float',
            'change_amount' => 'float',
            'total_hpp_cost' => 'float',
            'total_gross_profit' => 'float',
            'points_earned' => 'integer',
            'points_redeemed' => 'integer',
            'points_discount_amount' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<PosShift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(PosShift::class, 'pos_shift_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function refundedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function supervisorApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_approved_by');
    }

    /**
     * @return HasMany<PosOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    /**
     * @return HasMany<PosOrderPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(PosOrderPayment::class);
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    /**
     * @return BelongsTo<PosTable, $this>
     */
    public function posTable(): BelongsTo
    {
        return $this->belongsTo(PosTable::class, 'pos_table_id');
    }

    /**
     * @return BelongsTo<PosTableSession, $this>
     */
    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(PosTableSession::class, 'pos_table_session_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}

