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
        return [
            'qris' => [
                'bank_code' => 'qris',
                'bank_name' => 'QRIS Dinamis (Otomatis Semua Bank & e-Wallet)',
                'account_name' => 'COOCA.ID TRIPAY',
                'account_number' => 'Scan QR Code Cooca Pay',
                'type' => self::TYPE_QRIS,
                'instructions' => 'Scan kode QRIS melalui GoPay, OVO, ShopeePay, Dana, BCA Mobile, Livin, atau BRImo. Pembayaran terverifikasi otomatis dalam detik.',
                'icon' => 'qr-code',
                'color' => 'emerald',
                'is_active' => true,
                'sort_order' => 1,
            ],
            'bca_va' => [
                'bank_code' => 'bca_va',
                'bank_name' => 'BCA Virtual Account (Otomatis)',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Nomor VA dibuat otomatis saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Transfer via BCA Mobile / myBCA / ATM BCA ke nomor Virtual Account yang diterbitkan. Pembayaran terverifikasi instan tanpa upload struk.',
                'icon' => 'credit-card',
                'color' => 'blue',
                'is_active' => true,
                'sort_order' => 2,
            ],
            'mandiri_va' => [
                'bank_code' => 'mandiri_va',
                'bank_name' => 'Mandiri Virtual Account (Otomatis)',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Nomor VA dibuat otomatis saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Bayar melalui Livin by Mandiri atau ATM Mandiri dengan nomor Virtual Account. Terverifikasi instan.',
                'icon' => 'credit-card',
                'color' => 'amber',
                'is_active' => true,
                'sort_order' => 3,
            ],
            'briva' => [
                'bank_code' => 'briva',
                'bank_name' => 'BRI BRIVA (Otomatis)',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Nomor BRIVA dibuat otomatis saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Bayar melalui BRImo atau ATM BRI dengan nomor BRIVA. Terverifikasi instan.',
                'icon' => 'credit-card',
                'color' => 'cyan',
                'is_active' => true,
                'sort_order' => 4,
            ],
            'bni_va' => [
                'bank_code' => 'bni_va',
                'bank_name' => 'BNI Virtual Account (Otomatis)',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Nomor VA dibuat otomatis saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Bayar melalui BNI Mobile Banking atau ATM BNI dengan nomor Virtual Account. Terverifikasi instan.',
                'icon' => 'credit-card',
                'color' => 'orange',
                'is_active' => true,
                'sort_order' => 5,
            ],
            'permata_va' => [
                'bank_code' => 'permata_va',
                'bank_name' => 'Permata Virtual Account (Otomatis)',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Nomor VA dibuat otomatis saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Bayar melalui PermataMobile X atau ATM Permata dengan nomor Virtual Account. Terverifikasi instan.',
                'icon' => 'credit-card',
                'color' => 'violet',
                'is_active' => true,
                'sort_order' => 6,
            ],
            'alfamart' => [
                'bank_code' => 'alfamart',
                'bank_name' => 'Gerai Alfamart / Alfamidi',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Kode bayar kasir dibuat saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Tunjukkan kode pembayaran ke kasir Alfamart atau Alfamidi terdekat.',
                'icon' => 'store',
                'color' => 'red',
                'is_active' => true,
                'sort_order' => 7,
            ],
            'indomaret' => [
                'bank_code' => 'indomaret',
                'bank_name' => 'Gerai Indomaret',
                'account_name' => 'COOCA - TRIPAY',
                'account_number' => 'Kode bayar kasir dibuat saat checkout',
                'type' => self::TYPE_BANK_TRANSFER,
                'instructions' => 'Tunjukkan kode pembayaran ke kasir Indomaret terdekat.',
                'icon' => 'store',
                'color' => 'blue',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];
    }
}
