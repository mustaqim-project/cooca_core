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

class PosTableSession extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'business_id',
        'pos_table_id',
        'session_number',
        'customer_name',
        'customer_phone',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PosTable, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class, 'pos_table_id');
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'pos_table_session_id');
    }

    /**
     * Check if all orders in this session are paid and closed.
     */
    public function canBeClosed(): bool
    {
        // Must not have orders that are pending, confirmed, preparing, ready, served, or waiting_payment without completed payment
        return ! $this->orders()
            ->whereNotIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED])
            ->exists();
    }

    /**
     * Get total session order amount.
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->orders()
            ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED])
            ->sum('total_amount');
    }
}
