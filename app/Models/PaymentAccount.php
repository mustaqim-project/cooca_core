<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentAccount extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_BANK_TRANSFER = 'bank_transfer';
    public const TYPE_QRIS = 'qris';
    public const TYPE_E_WALLET = 'e_wallet';

    protected $fillable = [
        'bank_code',
        'bank_name',
        'account_name',
        'account_number',
        'type',
        'instructions',
        'qr_image_path',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('bank_name', 'asc');
    }

    public function getQrImageUrlAttribute(): ?string
    {
        if (! $this->qr_image_path) {
            return null;
        }

        return \App\Domain\Storage\AdminStorage::publicUrl($this->qr_image_path);
    }

    public function isQris(): bool
    {
        return $this->type === self::TYPE_QRIS;
    }

    /**
     * Get default seeded accounts if database is fresh/empty.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefaultAccounts(): array
    {
        return [];
    }
}
