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

    public const PAYOUT_MODE_MANUAL = 'manual';

    public const PAYOUT_MODE_AUTO_H1 = 'auto_h1';

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
        'payout_bank_account_id',
        'payout_mode',
        'scheduled_payout_at',
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
            'scheduled_payout_at' => 'datetime',
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

    public function payoutBankAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantPayoutBankAccount::class, 'payout_bank_account_id');
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

    public function isAutoPayoutMode(): bool
    {
        return $this->payout_mode === self::PAYOUT_MODE_AUTO_H1;
    }

    /**
     * Scope: pending auto-payout settlements due for processing.
     */
    public function scopeAutoPayoutQueue(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payout_mode', self::PAYOUT_MODE_AUTO_H1)
            ->where('status', self::STATUS_PENDING)
            ->where('scheduled_payout_at', '<=', now());
    }
}
