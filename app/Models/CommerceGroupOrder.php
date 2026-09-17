<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceGroupOrder extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_OPEN        = 'open';
    public const STATUS_LOCKED      = 'locked';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_CANCELLED   = 'cancelled';

    protected $table = 'commerce_group_orders';

    protected $fillable = [
        'business_id',
        'host_customer_id',
        'title',
        'share_token',
        'scheduled_date',
        'scheduled_time_slot',
        'status',
        'commerce_order_id',
        'delivery_address',
        'delivery_notes',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'expires_at'     => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'host_customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommerceGroupOrderItem::class, 'group_order_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isCheckedOut(): bool
    {
        return $this->status === self::STATUS_CHECKED_OUT;
    }

    public function isHost(?GlobalCustomer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        return $this->host_customer_id === $customer->id;
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) $i->line_total);
    }

    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->items->sum('quantity');
    }

    public function getMembersCountAttribute(): int
    {
        return (int) $this->items->pluck('global_customer_id')->unique()->count();
    }

    /**
     * Build transparent split bill data grouped per colleague / member.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSplitBillSummary(): array
    {
        $this->loadMissing(['items.product', 'host']);

        $grouped = $this->items->groupBy('global_customer_id');
        $summary = [];

        foreach ($grouped as $customerId => $memberItems) {
            $firstItem = $memberItems->first();
            $memberName = $firstItem?->member_name ?? 'Anggota';
            $isHost = $customerId === $this->host_customer_id;

            $memberSubtotal = 0.0;
            $itemsList = [];

            foreach ($memberItems as $item) {
                $lineTotal = (float) $item->line_total;
                $memberSubtotal += $lineTotal;

                $itemsList[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Produk',
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => $lineTotal,
                    'notes' => $item->notes,
                ];
            }

            $summary[] = [
                'customer_id' => $customerId,
                'name' => $memberName,
                'is_host' => $isHost,
                'item_count' => (int) $memberItems->sum('quantity'),
                'subtotal' => $memberSubtotal,
                'items' => $itemsList,
            ];
        }

        // Sort so Host comes first, then other members by name
        usort($summary, function ($a, $b) {
            if ($a['is_host'] !== $b['is_host']) {
                return $a['is_host'] ? -1 : 1;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $summary;
    }
}
