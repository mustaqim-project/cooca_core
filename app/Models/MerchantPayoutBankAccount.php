<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantPayoutBankAccount extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'location_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'is_primary',
        'is_verified',
        'verified_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * Tampilan display nama bank dan nomor rekening.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->bank_name} - {$this->account_number} (a.n {$this->account_holder_name})";
    }
}
