<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SubscriptionPayment extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const METHOD_BCA = 'bca';
    public const METHOD_MANDIRI = 'mandiri';
    public const METHOD_BRI = 'bri';
    public const METHOD_QRIS = 'qris';

    public const PAYMENT_METHODS = [
        self::METHOD_BCA => [
            'code' => self::METHOD_BCA,
            'name' => 'Bank BCA Transfer',
            'type' => 'bank_transfer',
            'bank_name' => 'Bank Central Asia (BCA)',
            'account_number' => '8735-0812-999',
            'account_name' => 'PT Cooca Teknologi Indonesia',
            'icon' => 'credit-card',
            'color' => 'blue',
            'instructions' => 'Transfer tepat hingga 3 digit terakhir ke rekening BCA resmi Cooca, lalu upload bukti struk transfer.',
        ],
        self::METHOD_MANDIRI => [
            'code' => self::METHOD_MANDIRI,
            'name' => 'Bank Mandiri Transfer',
            'type' => 'bank_transfer',
            'bank_name' => 'Bank Mandiri',
            'account_number' => '137-00-1928374-1',
            'account_name' => 'PT Cooca Teknologi Indonesia',
            'icon' => 'credit-card',
            'color' => 'amber',
            'instructions' => 'Transfer via ATM/Livin Mandiri sesuai nominal unik ke rekening Mandiri Cooca, lalu upload bukti transfer.',
        ],
        self::METHOD_BRI => [
            'code' => self::METHOD_BRI,
            'name' => 'Bank BRI Transfer',
            'type' => 'bank_transfer',
            'bank_name' => 'Bank Rakyat Indonesia (BRI)',
            'account_number' => '0341-01-002847-50-3',
            'account_name' => 'PT Cooca Teknologi Indonesia',
            'icon' => 'credit-card',
            'color' => 'cyan',
            'instructions' => 'Transfer via ATM/BRImo sesuai nominal unik ke rekening BRI Cooca, lalu upload bukti transfer.',
        ],
        self::METHOD_QRIS => [
            'code' => self::METHOD_QRIS,
            'name' => 'QRIS Instant (Semua Bank & e-Wallet)',
            'type' => 'qris',
            'bank_name' => 'QRIS Nasional (NMID: ID1020304050)',
            'account_number' => 'Scan QR Code Cooca Pay',
            'account_name' => 'COOCA.ID INDONESIA',
            'icon' => 'qr-code',
            'color' => 'emerald',
            'instructions' => 'Buka aplikasi BCA Mobile, GoPay, OVO, ShopeePay, atau Dana, scan kode QRIS Cooca, masukkan nominal sesuai angka unik, dan upload screenshot bukti bayar.',
        ],
    ];

    protected $fillable = [
        'business_id',
        'user_id',
        'order_number',
        'plan_code',
        'cycle',
        'amount',
        'unique_code',
        'total_payable',
        'payment_method',
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
            'proof_uploaded_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
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

    public function getPaymentMethodDetails(): array
    {
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
        if (!$this->payment_proof_path) {
            return null;
        }

        return Storage::disk('public')->url($this->payment_proof_path);
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
