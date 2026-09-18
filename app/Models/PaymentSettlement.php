<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSettlement extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'business_id',
        'settlement_number',
        'settlement_date',
        'payment_channel',
        'gross_amount',
        'fee_amount',
        'net_amount',
        'destination_bank',
        'status',
        'notes',
        'reconciled_by',
        'proof_image_path',
        'admin_id',
        'transferred_at',
        'admin_notes',
        'rejection_reason',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'proof_image_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settlement_date' => 'date',
            'transferred_at' => 'datetime',
            'gross_amount' => 'float',
            'fee_amount' => 'float',
            'net_amount' => 'float',
        ];
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentSettlementAllocation::class, 'payment_settlement_id');
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (! $this->proof_image_path) {
            return null;
        }

        return route('settlements.proof', $this->id);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
