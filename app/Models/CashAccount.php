<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAccount extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';
    public const TYPE_EWALLET = 'ewallet';

    protected $fillable = ['business_id', 'chart_of_account_id', 'name', 'type', 'current_balance', 'is_active'];
    protected function casts(): array { return ['current_balance' => 'float', 'is_active' => 'boolean']; }
    public function chartOfAccount(): BelongsTo { return $this->belongsTo(ChartOfAccount::class); }
    public function transactions(): HasMany { return $this->hasMany(CashTransaction::class); }
}
