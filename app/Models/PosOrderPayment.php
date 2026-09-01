<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderPayment extends Model
{
    use HasFactory, HasUuid;

    public const METHOD_CASH = 'cash';

    public const METHOD_QRIS = 'qris';

    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_EDC_DEBIT = 'edc_debit';

    public const METHOD_EDC_CREDIT = 'edc_credit';

    public const METHOD_CUSTOMER_CREDIT = 'customer_credit';

    public const METHOD_LOYALTY_POINTS = 'loyalty_points';

    protected $fillable = [
        'pos_order_id',
        'payment_method',
        'amount',
        'reference_number',
        'fee_amount',
        'net_amount',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'fee_amount' => 'float',
            'net_amount' => 'float',
        ];
    }

    /**
     * @return BelongsTo<PosOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }
}
