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

class Invoice extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'business_id',
        'customer_id',
        'location_id',
        'purchase_order_id',
        'sales_order_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'balance_due',
        'total_hpp_cost',
        'total_gross_profit',
        'payment_terms',
        'bank_details_snapshot',
        'notes',
        'terms_conditions',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'float',
            'discount_value' => 'float',
            'discount_amount' => 'float',
            'tax_percentage' => 'float',
            'tax_amount' => 'float',
            'shipping_cost' => 'float',
            'total_amount' => 'float',
            'paid_amount' => 'float',
            'balance_due' => 'float',
            'total_hpp_cost' => 'float',
            'total_gross_profit' => 'float',
            'bank_details_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<PurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<InvoicePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    /**
     * Recalculate totals, HPP snapshot sums, and profit margin.
     */
    public function recalculateTotals(): void
    {
        if ($this->items()->exists()) {
            $subtotal = (float) $this->items()->sum('subtotal');
            $totalHppCost = (float) $this->items()->sum('total_hpp');

            $discountAmount = 0.0;
            if ($this->discount_type === 'percentage') {
                $discountAmount = ($this->discount_value / 100.0) * $subtotal;
            } else {
                $discountAmount = min($this->discount_value, $subtotal);
            }

            $taxableAmount = max(0.0, $subtotal - $discountAmount);
            $taxAmount = ($this->tax_percentage / 100.0) * $taxableAmount;
            $totalAmount = $taxableAmount + $taxAmount + (float) $this->shipping_cost;
        } else {
            $subtotal = (float) $this->subtotal;
            $totalHppCost = (float) $this->total_hpp_cost;
            $discountAmount = (float) $this->discount_amount;
            $taxAmount = (float) $this->tax_amount;
            $totalAmount = (float) ($this->total_amount ?: $subtotal);
        }

        $paidAmount = (float) $this->payments()->sum('amount');
        $balanceDue = max(0.0, $totalAmount - $paidAmount);
        $totalGrossProfit = $subtotal - $totalHppCost;

        $status = $this->status;
        if ($status !== self::STATUS_VOID && $status !== self::STATUS_DRAFT) {
            if ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $status = self::STATUS_PAID;
            } elseif ($paidAmount > 0 && $balanceDue > 0) {
                $status = self::STATUS_PARTIALLY_PAID;
            } elseif ($balanceDue > 0 && $this->due_date && $this->due_date->isPast()) {
                $status = self::STATUS_OVERDUE;
            } elseif ($paidAmount == 0) {
                $status = self::STATUS_UNPAID;
            }
        }

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'total_hpp_cost' => $totalHppCost,
            'total_gross_profit' => $totalGrossProfit,
            'status' => $status,
        ]);
    }
}
