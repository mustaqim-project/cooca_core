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

    // Payment Gateways
    public const GATEWAY_MANUAL = 'manual';
    public const GATEWAY_TRIPAY = 'tripay';

    // Laundry Statuses
    public const LAUNDRY_STATUS_RECEIVED = 'received';
    public const LAUNDRY_STATUS_WASHING = 'washing';
    public const LAUNDRY_STATUS_IRONING = 'ironing';
    public const LAUNDRY_STATUS_READY = 'ready_for_pickup';
    public const LAUNDRY_STATUS_COMPLETED = 'completed';

    protected $appends = [
        'is_paid',
        'net_revenue',
    ];

    protected $fillable = [
        'business_id',
        'location_id',
        'pos_register_id',
        'pos_shift_id',
        'user_id',
        'customer_id',
        'order_number',
        'order_date',
        'status',
        'payment_gateway',
        'payment_channel',
        'gateway_reference',
        'gateway_pay_code',
        'gateway_pay_url',
        'gateway_qr_url',
        'gateway_qr_string',
        'gateway_fee',
        'gateway_expired_at',
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
        // Industry Specific Fields (Bengkel, Laundry, Apotek)
        'vehicle_license_plate',
        'vehicle_model',
        'vehicle_mileage',
        'technician_id',
        'service_notes',
        'laundry_weight_kg',
        'rack_location',
        'estimated_completion_at',
        'laundry_status',
        'print_count',
        'reprint_count',
        'first_printed_at',
        'last_printed_at',
        'last_printed_by',
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
            'gateway_expired_at' => 'datetime',
            'first_printed_at' => 'datetime',
            'last_printed_at' => 'datetime',
            'estimated_completion_at' => 'datetime',
            'print_count' => 'integer',
            'reprint_count' => 'integer',
            'vehicle_mileage' => 'integer',
            'laundry_weight_kg' => 'float',
            'gateway_fee' => 'float',
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

    public function isTripay(): bool
    {
        return $this->payment_gateway === self::GATEWAY_TRIPAY;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PREPARING, self::STATUS_READY, self::STATUS_SERVED, self::STATUS_COMPLETED], true)
            && ((float) $this->paid_amount >= (float) $this->total_amount || $this->payments()->where('status', 'paid')->exists());
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->isPaid();
    }

    public function getNetRevenueAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - (float) ($this->gateway_fee ?? 0.0));
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<PosRegister, $this>
     */
    public function posRegister(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'pos_register_id');
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
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function lastPrintedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_printed_by');
    }

    /**
     * Check if the current order print is a reprint (copy).
     */
    public function isReprint(): bool
    {
        return (int) ($this->print_count ?? 0) > 1;
    }

    /**
     * Record the initial original print of the bill/receipt.
     */
    public function recordPrint(?string $userId = null): self
    {
        if ((int) ($this->print_count ?? 0) === 0) {
            $now = now();
            $this->update([
                'print_count' => 1,
                'reprint_count' => 0,
                'first_printed_at' => $this->first_printed_at ?? $now,
                'last_printed_at' => $now,
                'last_printed_by' => $userId ?? auth()->id() ?? $this->user_id,
            ]);
        }

        return $this;
    }

    /**
     * Record a reprint action, incrementing print count and generating an anti-fraud Audit Log.
     */
    public function recordReprint(?string $userId = null, ?string $reason = null): self
    {
        $oldCount = (int) ($this->print_count ?? 0);
        $newCount = max(1, $oldCount) + 1;
        $newReprintCount = $newCount - 1;
        $now = now();
        $actorId = $userId ?? auth()->id() ?? $this->user_id;

        $this->update([
            'print_count' => $newCount,
            'reprint_count' => $newReprintCount,
            'first_printed_at' => $this->first_printed_at ?? $now,
            'last_printed_at' => $now,
            'last_printed_by' => $actorId,
        ]);

        // Forensic Audit Log for Anti-Fraud
        try {
            $actor = $actorId ? User::find($actorId) : null;
            $actorName = $actor?->name ?? 'Kasir';
            $riskLevel = $newCount > 2 ? 'high' : 'medium';

            AuditLog::create([
                'business_id' => $this->business_id,
                'user_id' => $actorId,
                'auditable_type' => self::class,
                'auditable_id' => $this->id,
                'action' => 'bill_reprinted',
                'risk_level' => $riskLevel,
                'risk_reason' => $newCount > 2 ? 'Cetak ulang bill dilakukan lebih dari 2 kali' : null,
                'notes' => "Cetak Ulang (Re-Print) Bill Order #{$this->order_number} - Salinan ke-{$newReprintCount} (Cetakan ke-{$newCount}) oleh {$actorName}",
                'old_values' => [
                    'print_count' => $oldCount,
                    'reprint_count' => max(0, $oldCount - 1),
                ],
                'new_values' => [
                    'print_count' => $newCount,
                    'reprint_count' => $newReprintCount,
                    'reason' => $reason,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently safeguard if audit log cannot be created
        }

        return $this;
    }
}
