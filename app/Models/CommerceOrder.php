<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceOrder extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    // Order Types
    public const TYPE_DIRECT_CHECKOUT = 'direct_checkout';
    public const TYPE_REQUEST_ORDER    = 'request_order';
    public const TYPE_SCHEDULED_ORDER  = 'scheduled_order';
    public const TYPE_CUSTOMER_PO      = 'customer_po';
    public const TYPE_PO_BATCH         = 'po_batch';
    public const TYPE_RESERVATION      = 'reservation';

    // Fulfillment Types
    public const FULFILLMENT_PICKUP            = 'pickup';
    public const FULFILLMENT_MERCHANT_DELIVERY = 'merchant_delivery';
    public const FULFILLMENT_COURIER_MANUAL    = 'courier_manual';
    public const FULFILLMENT_DINE_IN           = 'dine_in';
    public const FULFILLMENT_SERVICE_ON_SITE   = 'service_on_site';

    // Statuses
    public const STATUS_DRAFT            = 'draft';
    public const STATUS_PENDING_REVIEW   = 'pending_review';
    public const STATUS_PENDING_PAYMENT  = 'pending_payment';
    public const STATUS_PROOF_SUBMITTED  = 'proof_submitted';
    public const STATUS_PAYMENT_REJECTED = 'payment_rejected';
    public const STATUS_PAID             = 'paid';
    public const STATUS_PROCESSING       = 'processing';
    public const STATUS_READY            = 'ready';
    public const STATUS_FULFILLED        = 'fulfilled';
    public const STATUS_COMPLETED        = 'completed';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_EXPIRED          = 'expired';

    // Payment Statuses
    public const PAYMENT_UNPAID    = 'unpaid';
    public const PAYMENT_VERIFYING = 'verifying';
    public const PAYMENT_PAID      = 'paid';
    public const PAYMENT_FAILED    = 'failed';
    public const PAYMENT_REFUNDED  = 'refunded';

    // Payment Gateways
    public const GATEWAY_MANUAL = 'manual';
    public const GATEWAY_TRIPAY = 'tripay';

    protected $table = 'commerce_orders';

    protected $fillable = [
        'business_id',
        'location_id',
        'customer_id',
        'global_customer_id',
        'payment_method_id',
        'shipping_rule_id',
        'order_number',
        'tracking_token',
        'order_type',
        'fulfillment_type',
        'status',
        'payment_status',
        'payment_gateway',
        'payment_channel',
        'gateway_reference',
        'gateway_pay_code',
        'gateway_pay_url',
        'gateway_qr_url',
        'gateway_qr_string',
        'gateway_fee',
        'gateway_expired_at',
        'gateway_payload',
        'customer_po_number',
        'company_name',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'destination_postal_code',
        'shipping_notes',
        'scheduled_date',
        'scheduled_time_slot',
        'subtotal',
        'shipping_cost',
        'biteship_service_fee',
        'shipping_courier_code',
        'shipping_courier_service',
        'shipping_courier_name',
        'biteship_order_id',
        'shipping_waybill_id',
        'shipping_tracking_url',
        'shipping_status',
        'shipping_payload',
        'discount_amount',
        'total_amount',
        'reserved_until',
        'paid_at',
        'cancelled_at',
        'rejection_reason',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'subtotal' => 'float',
            'shipping_cost' => 'float',
            'biteship_service_fee' => 'float',
            'discount_amount' => 'float',
            'total_amount' => 'float',
            'gateway_fee' => 'float',
            'gateway_expired_at' => 'datetime',
            'gateway_payload' => 'array',
            'shipping_payload' => 'array',
            'reserved_until' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);

    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function globalCustomer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(CommercePaymentMethod::class, 'payment_method_id');
    }

    public function shippingRule(): BelongsTo
    {
        return $this->belongsTo(CommerceShippingRule::class, 'shipping_rule_id');
    }

    /**
     * Alias accessor for shipping_cost.
     */
    public function getShippingFeeAttribute(): float
    {
        return (float) ($this->attributes['shipping_cost'] ?? 0.0);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommerceOrderItem::class, 'commerce_order_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(CommerceOrderBatch::class, 'commerce_order_id')->orderBy('batch_number');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(CommercePaymentProof::class, 'commerce_order_id')->latest();
    }

    public function latestProof(): HasOne
    {
        return $this->hasOne(CommercePaymentProof::class, 'commerce_order_id')->latestOfMany();
    }

    public function groupOrder(): HasOne
    {
        return $this->hasOne(CommerceGroupOrder::class, 'commerce_order_id');
    }

    public function isGroupOrder(): bool
    {
        return $this->groupOrder !== null;
    }

    public function isCustomerPo(): bool
    {
        return in_array($this->order_type, [self::TYPE_CUSTOMER_PO, self::TYPE_PO_BATCH], true);
    }

    public function isPoBatch(): bool
    {
        return $this->order_type === self::TYPE_PO_BATCH || $this->batches()->exists();
    }

    public function getDeliveredBatchesCountAttribute(): int
    {
        return $this->batches->where('status', CommerceOrderBatch::STATUS_DELIVERED)->count();
    }

    public function getTotalBatchesCountAttribute(): int
    {
        return $this->batches->count();
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_READY,
            self::STATUS_FULFILLED,
            self::STATUS_COMPLETED,
        ], true);
    }

    public function canSubmitProof(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAYMENT_REJECTED,
        ], true);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_REVIEW,
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PROOF_SUBMITTED,
            self::STATUS_PAYMENT_REJECTED,
        ], true);
    }

    public function isTripay(): bool
    {
        return $this->payment_gateway === self::GATEWAY_TRIPAY;
    }

    public function isManualPayment(): bool
    {
        return $this->payment_gateway === self::GATEWAY_MANUAL || empty($this->payment_gateway);
    }

    public function isCod(): bool
    {
        return strtolower((string) ($this->payment_channel ?? '')) === 'cod'
            || strtolower((string) ($this->payment_method ?? '')) === 'cod';
    }

    public function getNetRevenueAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - (float) ($this->gateway_fee ?? 0.0));
    }
}

