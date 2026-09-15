<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceOrderBatch extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PREPARATION = 'in_preparation';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'commerce_order_batches';

    protected $fillable = [
        'commerce_order_id',
        'batch_number',
        'batch_code',
        'scheduled_date',
        'scheduled_time_slot',
        'quantity',
        'status',
        'shipping_address',
        'tracking_number',
        'notes',
        'delivered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'batch_number' => 'integer',
            'scheduled_date' => 'date',
            'quantity' => 'float',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => Carbon::now(),
        ]);
    }

    public function markAsShipped(?string $trackingNumber = null): void
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'tracking_number' => $trackingNumber ?: $this->tracking_number,
        ]);
    }
}
