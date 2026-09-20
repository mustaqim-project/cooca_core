<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_DEBIT = 'debit';

    public const TYPE_CREDIT = 'credit';

    public const STATUS_UNMATCHED = 'unmatched';

    public const STATUS_MATCHED = 'matched';

    public const STATUS_RECONCILED = 'reconciled';

    protected $fillable = [
        'bank_statement_id',
        'business_id',
        'transaction_date',
        'description',
        'reference_number',
        'type',
        'amount',
        'balance',
        'status',
        'matched_transaction_type',
        'matched_transaction_id',
        'reconciled_at',
        'reconciled_by',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'float',
            'balance' => 'float',
            'reconciled_at' => 'datetime',
        ];
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function isReconciled(): bool
    {
        return $this->status === self::STATUS_RECONCILED;
    }
}
