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

        return Storage::disk('public')->url($this->qr_image_path);
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
        return [
            'bca' => [
                'bank_code' => 'bca',
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_name' => 'PT Cooca Teknologi Indonesia',
                'account_number' => '8735-0812-999',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Transfer tepat hingga 3 digit terakhir ke rekening BCA resmi Cooca, lalu upload bukti struk transfer.',
                'icon' => 'credit-card',
                'color' => 'blue',
                'is_active' => true,
                'sort_order' => 1,
            ],
            'mandiri' => [
                'bank_code' => 'mandiri',
                'bank_name' => 'Bank Mandiri',
                'account_name' => 'PT Cooca Teknologi Indonesia',
                'account_number' => '137-00-1928374-1',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Transfer via ATM/Livin Mandiri sesuai nominal unik ke rekening Mandiri Cooca, lalu upload bukti transfer.',
                'icon' => 'credit-card',
                'color' => 'amber',
                'is_active' => true,
                'sort_order' => 2,
            ],
            'bri' => [
                'bank_code' => 'bri',
                'bank_name' => 'Bank Rakyat Indonesia (BRI)',
                'account_name' => 'PT Cooca Teknologi Indonesia',
                'account_number' => '0341-01-002847-50-3',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Transfer via ATM/BRImo sesuai nominal unik ke rekening BRI Cooca, lalu upload bukti transfer.',
                'icon' => 'credit-card',
                'color' => 'cyan',
                'is_active' => true,
                'sort_order' => 3,
            ],
            'qris' => [
                'bank_code' => 'qris',
                'bank_name' => 'QRIS Cooca Pay (Semua Bank & e-Wallet)',
                'account_name' => 'COOCA.ID INDONESIA',
                'account_number' => 'NMID: ID1020304050',
                'type' => self::TYPE_QRIS,
                'instructions' => 'Buka aplikasi BCA Mobile, GoPay, OVO, ShopeePay, atau Dana, scan kode QRIS Cooca, masukkan nominal sesuai angka unik, dan upload screenshot bukti bayar.',
                'icon' => 'qr-code',
                'color' => 'emerald',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }
}
