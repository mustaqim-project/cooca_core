<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'slug',
        'company_name',
        'email',
        'phone',
        'billing_address',
        'shipping_address',
        'tax_identification_number',
        'payment_terms_days',
        'segment',
        'membership_tier',
        'points_balance',
        'total_spent',
        'total_orders_count',
        'birth_date',
        'anniversary_date',
        'credit_limit',
        'current_credit_balance',
        'notes',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
            'points_balance' => 'integer',
            'total_spent' => 'float',
            'total_orders_count' => 'integer',
            'credit_limit' => 'float',
            'current_credit_balance' => 'float',
            'birth_date' => 'date',
            'anniversary_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Resolve route binding by either UUID id or slug.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    /**
     * @return HasMany<PurchaseOrder, $this>
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    /**
     * @return HasMany<CustomerPointHistory, $this>
     */
    public function pointHistories(): HasMany
    {
        return $this->hasMany(CustomerPointHistory::class);
    }

    /**
     * @return HasMany<CustomerCreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }
}
