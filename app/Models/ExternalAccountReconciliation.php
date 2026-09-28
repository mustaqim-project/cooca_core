<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lembar rekonsiliasi akun eksternal (EDC, E-Wallet, Marketplace)
 * yang di luar ruang lingkup otomatis COOCA.
 *
 * Merchant mengisi saldo awal & saldo akhir secara manual setiap bulan.
 * Sistem menghitung expected_closing dan variance untuk deteksi selisih.
 */
class ExternalAccountReconciliation extends Model
{
    use BelongsToBusiness, HasUuid;

    // ─── Channel Types ──────────────────────────────────────
    public const CHANNEL_EDC    = 'edc';
    public const CHANNEL_EWALLET = 'ewallet';
    public const CHANNEL_MARKETPLACE = 'marketplace';
    public const CHANNEL_CASH   = 'cash_register';

    // ─── Common Providers ───────────────────────────────────
    public const PROVIDER_EDC_BCA     = 'edc_bca';
    public const PROVIDER_EDC_BRI     = 'edc_bri';
    public const PROVIDER_EDC_MANDIRI = 'edc_mandiri';
    public const PROVIDER_GOPAY       = 'gopay';
    public const PROVIDER_OVO         = 'ovo';
    public const PROVIDER_DANA        = 'dana';
    public const PROVIDER_SHOPEEPAY   = 'shopeepay';
    public const PROVIDER_TOKOPEDIA   = 'tokopedia';
    public const PROVIDER_SHOPEE      = 'shopee';
    public const PROVIDER_GRAB        = 'grab';

    // ─── Statuses ───────────────────────────────────────────
    public const STATUS_DRAFT       = 'draft';
    public const STATUS_SUBMITTED   = 'submitted';
    public const STATUS_REVIEWED    = 'reviewed';
    public const STATUS_DISCREPANCY = 'discrepancy';

    protected $fillable = [
        'business_id',
        'location_id',
        'channel_type',
        'channel_label',
        'period',
        'opening_balance',
        'total_inflow',
        'total_disbursement',
        'closing_balance',
        'expected_closing',
        'variance',
        'status',
        'notes',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance'     => 'float',
            'total_inflow'        => 'float',
            'total_disbursement'  => 'float',
            'closing_balance'     => 'float',
            'expected_closing'    => 'float',
            'variance'            => 'float',
            'submitted_at'        => 'datetime',
            'reviewed_at'         => 'datetime',
        ];
    }

    // ─── Relations ──────────────────────────────────────────

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    // ─── Computed Helpers ───────────────────────────────────

    /**
     * Recalculate the expected closing balance and variance.
     * Formula: expected = opening + inflow - disbursement
     * Variance: closing - expected (negative = money missing)
     */
    public function recalculate(): static
    {
        $this->expected_closing = $this->opening_balance + $this->total_inflow - $this->total_disbursement;
        $this->variance = $this->closing_balance - $this->expected_closing;

        return $this;
    }

    /**
     * Whether there is a discrepancy (variance != 0 within ±Rp100 tolerance).
     */
    public function hasDiscrepancy(): bool
    {
        return abs($this->variance) > 100;
    }

    /**
     * Is the record submitted and pending review?
     */
    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw('ABS(variance) > 100');
        });
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    // ─── Static Helpers ─────────────────────────────────────

    /**
     * All known channel types with label.
     */
    public static function channelOptions(): array
    {
        return [
            self::PROVIDER_EDC_BCA     => 'EDC BCA',
            self::PROVIDER_EDC_BRI     => 'EDC BRI',
            self::PROVIDER_EDC_MANDIRI => 'EDC Mandiri',
            self::PROVIDER_GOPAY       => 'GoPay',
            self::PROVIDER_OVO         => 'OVO',
            self::PROVIDER_DANA        => 'DANA',
            self::PROVIDER_SHOPEEPAY   => 'ShopeePay',
            self::PROVIDER_TOKOPEDIA   => 'Tokopedia',
            self::PROVIDER_SHOPEE      => 'Shopee',
            self::PROVIDER_GRAB        => 'Grab',
        ];
    }
}
