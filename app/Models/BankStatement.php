<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'business_id',
        'cash_account_id',
        'filename',
        'statement_date',
        'opening_balance',
        'closing_balance',
        'status',
        'total_lines',
        'reconciled_lines',
        'imported_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'opening_balance' => 'float',
            'closing_balance' => 'float',
            'total_lines' => 'integer',
            'reconciled_lines' => 'integer',
        ];
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class, 'bank_statement_id');
    }

    public function getProgressPercentage(): int
    {
        if ($this->total_lines <= 0) {
            return 0;
        }

        return (int) round(($this->reconciled_lines / $this->total_lines) * 100);
    }
}
