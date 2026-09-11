<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_TRANSFER = 'transfer';

    protected $fillable = ['business_id', 'cash_account_id', 'type', 'amount', 'balance_after', 'reference_type', 'reference_id', 'description', 'transaction_date', 'created_by'];
    protected function casts(): array { return ['amount' => 'float', 'balance_after' => 'float', 'transaction_date' => 'date']; }
    public function cashAccount(): BelongsTo { return $this->belongsTo(CashAccount::class); }

    public function isOutflow(): bool
    {
        if ($this->type === self::TYPE_OUT) {
            return true;
        }

        if ($this->type === self::TYPE_TRANSFER) {
            return $this->reference_type === 'cash_transfer_out'
                || str_contains(strtolower($this->description ?? ''), 'transfer ke')
                || str_contains(strtolower($this->description ?? ''), 'transfer keluar');
        }

        return false;
    }

    public function isInflow(): bool
    {
        if ($this->type === self::TYPE_IN) {
            return true;
        }

        if ($this->type === self::TYPE_TRANSFER) {
            return $this->reference_type === 'cash_transfer_in'
                || str_contains(strtolower($this->description ?? ''), 'transfer dari')
                || str_contains(strtolower($this->description ?? ''), 'transfer masuk');
        }

        return false;
    }

    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER
            || str_starts_with($this->reference_type ?? '', 'cash_transfer');
    }
}

