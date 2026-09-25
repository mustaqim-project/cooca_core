<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosShift extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'business_id',
        'pos_register_id',
        'location_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'opening_denominations',
        'closing_cash_actual',
        'closing_cash_expected',
        'closing_denominations',
        'cash_difference',
        'total_cash_sales',
        'total_non_cash_sales',
        'total_cash_in',
        'total_cash_out',
        'status',
        'notes',
        'cashier_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'float',
            'opening_denominations' => 'array',
            'closing_cash_actual' => 'float',
            'closing_cash_expected' => 'float',
            'closing_denominations' => 'array',
            'cash_difference' => 'float',
            'total_cash_sales' => 'float',
            'total_non_cash_sales' => 'float',
            'total_cash_in' => 'float',
            'total_cash_out' => 'float',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isBalanced(): bool
    {
        return abs((float) ($this->cash_difference ?? 0.0)) < 0.01;
    }

    public function isShort(): bool
    {
        return (float) ($this->cash_difference ?? 0.0) < -0.01;
    }

    public function isOver(): bool
    {
        return (float) ($this->cash_difference ?? 0.0) > 0.01;
    }

    public function getVarianceLabel(): string
    {
        $diff = (float) ($this->cash_difference ?? 0.0);
        if (abs($diff) < 0.01) {
            return 'Pas (Balanced)';
        }
        if ($diff > 0) {
            return '+Rp ' . number_format($diff, 0, ',', '.') . ' (Lebih / Over)';
        }
        return '-Rp ' . number_format(abs($diff), 0, ',', '.') . ' (Kurang / Short)';
    }

    /**
     * @return BelongsTo<PosRegister, $this>
     */
    public function register(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'pos_register_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PosCashMovement, $this>
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(PosCashMovement::class);
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }
}
