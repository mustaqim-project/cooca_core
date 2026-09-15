<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceReservation extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_CONFIRMED            = 'confirmed';
    public const STATUS_SEATED               = 'seated';
    public const STATUS_COMPLETED            = 'completed';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_NO_SHOW              = 'no_show';

    protected $table = 'commerce_reservations';

    protected $fillable = [
        'business_id',
        'commerce_order_id',
        'pos_table_id',
        'product_id',
        'reservation_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'time_slot',
        'guest_count',
        'status',
        'notes',
        'cancellation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'guest_count' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function posTable(): BelongsTo
    {
        return $this->belongsTo(PosTable::class, 'pos_table_id');
    }

    public function table(): BelongsTo
    {
        return $this->posTable();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_SEATED, self::STATUS_COMPLETED], true);
    }

    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    public function markAsSeated(): void
    {
        $this->update(['status' => self::STATUS_SEATED]);

        if ($this->pos_table_id && $this->posTable) {
            $this->posTable->update(['status' => PosTable::STATUS_OCCUPIED]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);

        if ($this->pos_table_id && $this->posTable) {
            $this->posTable->update(['status' => PosTable::STATUS_AVAILABLE]);
        }
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        if ($this->pos_table_id && $this->posTable && $this->posTable->status === PosTable::STATUS_OCCUPIED) {
            $this->posTable->update(['status' => PosTable::STATUS_AVAILABLE]);
        }
    }
}
