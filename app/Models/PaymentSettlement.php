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

    public const STATUS_COMPLETED = 'completed';

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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settlement_date' => 'date',
            'gross_amount' => 'float',
            'fee_amount' => 'float',
            'net_amount' => 'float',
        ];
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentSettlementAllocation::class, 'payment_settlement_id');
    }
}
