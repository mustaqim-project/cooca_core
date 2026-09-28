<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreEdcTerminal extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'location_id',
        'bank_name',
        'terminal_name',
        'terminal_id_tid',
        'merchant_id_mid',
        'mdr_debit_percent',
        'mdr_credit_percent',
        'settlement_account_info',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mdr_debit_percent' => 'float',
            'mdr_credit_percent' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosOrderPayment::class, 'store_edc_terminal_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocation(Builder $query, ?string $locationId): Builder
    {
        if (empty($locationId)) {
            return $query;
        }

        return $query->where(function ($q) use ($locationId) {
            $q->where('location_id', $locationId)
                ->orWhereNull('location_id');
        });
    }

    public function getDisplayNameAttribute(): string
    {
        $locName = $this->location ? " ({$this->location->name})" : '';
        return "{$this->bank_name} - {$this->terminal_name} [TID: {$this->terminal_id_tid}]{$locName}";
    }
}
