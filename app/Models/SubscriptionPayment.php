<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const METHOD_QRIS = 'qris';
    public const METHOD_BCA_VA = 'bca_va';
    public const METHOD_MANDIRI_VA = 'mandiri_va';
    public const METHOD_BRI_VA = 'bri_va';
    public const METHOD_BNI_VA = 'bni_va';
    public const METHOD_PERMATA_VA = 'permata_va';
    public const METHOD_INDOMARET = 'indomaret';
    public const METHOD_ALFAMART = 'alfamart';
    public const METHOD_BCA = 'bca';
    public const METHOD_MANDIRI = 'mandiri';
    public const METHOD_BRI = 'bri';
    public const METHOD_FREE_PROMO = 'free_promo';

    public const PAYMENT_METHODS = [
        self::METHOD_QRIS => [
            'code' => self::METHOD_QRIS,
            'name' => 'QRIS Dinamis (GoPay, OVO, ShopeePay, BCA, Livin, BRImo)',
            'type' => 'qris',
            'bank_name' => 'QRIS Nasional (NMID: ID1020304050)',
            'account_number' => 'Scan QR Code Cooca Pay',
            'account_name' => 'COOCA.ID INDONESIA',
            'icon' => 'qr-code',
            'color' => 'emerald',
            'instructions' => 'Buka aplikasi m-Banking atau e-Wallet apa pun, scan kode QRIS dinamis di layar. Pembayaran terverifikasi otomatis seketika.',
        ],
        self::METHOD_BCA_VA => [
            'code' => self::METHOD_BCA_VA,
            'name' => 'BCA Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Central Asia (BCA)',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'blue',
            'instructions' => 'Salin nomor Virtual Account BCA dan bayar melalui BCA Mobile, KlikBCA, atau ATM BCA. Verifikasi otomatis.',
        ],
        self::METHOD_MANDIRI_VA => [
            'code' => self::METHOD_MANDIRI_VA,
            'name' => 'Mandiri Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Mandiri',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'amber',
            'instructions' => 'Bayar melalui Livin by Mandiri atau ATM Mandiri ke nomor Mandiri Virtual Account. Verifikasi otomatis.',
        ],
        self::METHOD_BRI_VA => [
            'code' => self::METHOD_BRI_VA,
            'name' => 'BRI Virtual Account (BRIVA)',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Rakyat Indonesia (BRI)',
            'account_number' => 'Nomor BRIVA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'cyan',
            'instructions' => 'Bayar melalui BRImo atau ATM BRI ke nomor BRIVA yang tertera. Verifikasi otomatis seketika.',
        ],
        self::METHOD_BNI_VA => [
            'code' => self::METHOD_BNI_VA,
            'name' => 'BNI Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Negara Indonesia (BNI)',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'orange',
            'instructions' => 'Bayar melalui BNI Mobile Banking atau ATM BNI ke nomor BNI Virtual Account. Verifikasi otomatis seketika.',
        ],
        self::METHOD_PERMATA_VA => [
            'code' => self::METHOD_PERMATA_VA,
            'name' => 'Permata Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Permata',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'violet',
            'instructions' => 'Bayar melalui PermataMobile X atau transfer antar-bank ke nomor Permata VA.',
        ],
        self::METHOD_INDOMARET => [
            'code' => self::METHOD_INDOMARET,
            'name' => 'Gerai Indomaret',
            'type' => 'retail',
            'bank_name' => 'Indomaret Payment Point',
            'account_number' => 'Kode Pembayaran Kasir',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'store',
            'color' => 'blue',
            'instructions' => 'Tunjukkan kode bayar kepada kasir Indomaret terdekat dan lakukan pembayaran tunai/non-tunai.',
        ],
        self::METHOD_ALFAMART => [
            'code' => self::METHOD_ALFAMART,
            'name' => 'Gerai Alfamart',
            'type' => 'retail',
            'bank_name' => 'Alfamart / Alfamidi',
            'account_number' => 'Kode Pembayaran Kasir',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'store',
            'color' => 'red',
            'instructions' => 'Tunjukkan kode bayar kepada kasir Alfamart/Alfamidi terdekat dan lakukan pembayaran.',
        ],
        self::METHOD_BCA => [
            'code' => self::METHOD_BCA,
            'name' => 'BCA Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Central Asia (BCA)',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'blue',
            'instructions' => 'Bayar ke nomor Virtual Account BCA resmi Cooca. Verifikasi instan otomatis.',
        ],
        self::METHOD_MANDIRI => [
            'code' => self::METHOD_MANDIRI,
            'name' => 'Mandiri Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Mandiri',
            'account_number' => 'Nomor VA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'amber',
            'instructions' => 'Bayar ke nomor Virtual Account Mandiri resmi Cooca. Verifikasi instan otomatis.',
        ],
        self::METHOD_BRI => [
            'code' => self::METHOD_BRI,
            'name' => 'BRI Virtual Account',
            'type' => 'virtual_account',
            'bank_name' => 'Bank Rakyat Indonesia (BRI)',
            'account_number' => 'Nomor BRIVA Otomatis',
            'account_name' => 'COOCA INDONESIA',
            'icon' => 'credit-card',
            'color' => 'cyan',
            'instructions' => 'Bayar ke nomor BRIVA resmi Cooca. Verifikasi instan otomatis.',
        ],
        self::METHOD_FREE_PROMO => [
            'code' => self::METHOD_FREE_PROMO,
            'name' => 'Promo Bebas Biaya (Trial)',
            'type' => 'promo',
            'bank_name' => 'Promo Spesial Cooca',
            'account_number' => 'Trial Pro',
            'account_name' => 'COOCA MARKETING PROMO',
            'icon' => 'sparkles',
            'color' => 'emerald',
            'instructions' => 'Paket promo trial aktif otomatis seketika tanpa perlu transfer bank maupun konfirmasi admin.',
        ],
    ];

    public const GATEWAY_MANUAL = 'manual';
    public const GATEWAY_TRIPAY = 'tripay';

    protected $fillable = [
        'business_id',
        'user_id',
        'payment_type',
        'billing_package_id',
        'package_name',
        'package_duration_days',
        'order_number',
        'plan_code',
        'cycle',
        'amount',
        'unique_code',
        'total_payable',
        'topup_quantity',
        'topup_storage_bytes',
        'payment_method',
        'payment_gateway',
        'gateway_reference',
        'gateway_pay_code',
        'gateway_pay_url',
        'gateway_qr_url',
        'gateway_qr_string',
        'gateway_fee',
        'gateway_expired_at',
        'status',
        'payment_proof_path',
        'sender_bank',
        'sender_account_name',
        'sender_account_number',
        'proof_uploaded_at',
        'notes',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'unique_code' => 'integer',
            'total_payable' => 'float',
            'topup_quantity' => 'integer',
            'topup_storage_bytes' => 'integer',
            'package_duration_days' => 'integer',
            'gateway_fee' => 'float',
            'gateway_expired_at' => 'datetime',
            'proof_uploaded_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function isTripay(): bool
    {
        return $this->payment_gateway === self::GATEWAY_TRIPAY;
    }

    public function isManual(): bool
    {
        return $this->payment_gateway === self::GATEWAY_MANUAL || empty($this->payment_gateway);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function billingPackage(): BelongsTo
    {
        return $this->belongsTo(BillingPackage::class, 'billing_package_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAwaitingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AWAITING_APPROVAL);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAwaitingApproval(): bool
    {
        return $this->status === self::STATUS_AWAITING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getGatewayAttribute(): ?string
    {
        return $this->payment_gateway;
    }

    public function getTripayChannelCode(): string
    {
        return match ($this->payment_method) {
            self::METHOD_QRIS => 'QRIS',
            self::METHOD_BCA_VA, self::METHOD_BCA => 'BCAVA',
            self::METHOD_MANDIRI_VA, self::METHOD_MANDIRI => 'MANDIRIVA',
            self::METHOD_BRI_VA, self::METHOD_BRI => 'BRIVA',
            self::METHOD_BNI_VA => 'BNIVA',
            self::METHOD_PERMATA_VA => 'PERMATAVA',
            self::METHOD_INDOMARET => 'INDOMARET',
            self::METHOD_ALFAMART => 'ALFAMART',
            default => 'QRIS',
        };
    }

    public function getPaymentMethodDetails(): array
    {
        $normMethod = strtolower(str_replace(['tripay_', 'va'], ['', '_va'], $this->payment_method ?? ''));
        $normRaw = strtolower(str_replace('tripay_', '', $this->payment_method ?? ''));

        if ($this->payment_gateway === self::GATEWAY_TRIPAY || str_starts_with($this->payment_method ?? '', 'tripay_')) {
            $base = self::PAYMENT_METHODS[$normRaw] 
                ?? self::PAYMENT_METHODS[$normMethod] 
                ?? self::PAYMENT_METHODS[$this->payment_method] 
                ?? [
                    'code' => $this->payment_method,
                    'name' => strtoupper($normRaw),
                    'type' => 'gateway',
                    'bank_name' => 'TriPay Gateway',
                    'account_number' => $this->gateway_pay_code ?: '-',
                    'account_name' => 'COOCA INDONESIA',
                    'icon' => 'credit-card',
                    'color' => 'blue',
                    'instructions' => 'Selesaikan pembayaran via TriPay sebelum batas waktu.',
                    'qr_image_url' => $this->gateway_qr_url,
                ];

            if ($this->gateway_pay_code) {
                $base['account_number'] = $this->gateway_pay_code;
            }
            if ($this->gateway_qr_url) {
                $base['qr_image_url'] = $this->gateway_qr_url;
            }
            return $base;
        }

        $account = PaymentAccount::where('bank_code', $this->payment_method)->first();
        if ($account) {
            return [
                'code' => $account->bank_code,
                'name' => $account->bank_name,
                'type' => $account->type,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'icon' => $account->icon,
                'color' => $account->color,
                'instructions' => $account->instructions,
                'qr_image_url' => $account->qr_image_url,
            ];
        }

        return self::PAYMENT_METHODS[$this->payment_method] ?? [
            'code' => $this->payment_method,
            'name' => strtoupper($this->payment_method),
            'type' => 'manual',
            'bank_name' => 'Transfer Bank',
            'account_number' => '-',
            'account_name' => 'PT Cooca Teknologi Indonesia',
            'icon' => 'credit-card',
            'color' => 'slate',
            'instructions' => 'Lakukan pembayaran sesuai nominal tertera.',
            'qr_image_url' => null,
        ];
    }

    public function getProofUrl(): ?string
    {
        if (! $this->payment_proof_path) {
            return null;
        }

        try {
            return route('billing.payment.proof', $this->id);
        } catch (\Throwable) {
            $path = ltrim($this->payment_proof_path, '/');

            if (is_file(public_path($path))) {
                return asset($path);
            }

            return asset('storage/' . $path);
        }
    }

    public function getStatusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [
                'label' => 'Menunggu Pembayaran',
                'class' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                'icon' => 'clock',
            ],
            self::STATUS_AWAITING_APPROVAL => [
                'label' => 'Menunggu Verifikasi Admin',
                'class' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30 animate-pulse',
                'icon' => 'hourglass',
            ],
            self::STATUS_APPROVED => [
                'label' => 'Disetujui & Aktif',
                'class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'icon' => 'check-circle-2',
            ],
            self::STATUS_REJECTED => [
                'label' => 'Pembayaran Ditolak',
                'class' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'icon' => 'x-circle',
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Dibatalkan',
                'class' => 'bg-slate-800 text-slate-400 border-slate-700',
                'icon' => 'slash',
            ],
            default => [
                'label' => $this->status,
                'class' => 'bg-slate-800 text-slate-300 border-slate-700',
                'icon' => 'help-circle',
            ],
        };
    }
}
