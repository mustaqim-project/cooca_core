<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

final class AuditLog extends Model
{
    use BelongsToBusiness, HasUuid;

    public const RISK_LOW = 'low';

    public const RISK_MEDIUM = 'medium';

    public const RISK_HIGH = 'high';

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'risk_level',
        'risk_reason',
        'notes',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'alert_sent_at',
        'alert_recipient',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'alert_sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot model events to strictly enforce append-only immutability.
     */
    protected static function booted(): void
    {
        static::updating(function (self $log): bool {
            $immutableAttributes = [
                'business_id',
                'user_id',
                'auditable_type',
                'auditable_id',
                'action',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
                'created_at',
            ];

            if ($log->isDirty($immutableAttributes)) {
                throw new RuntimeException('Audit logs are immutable and historical event data cannot be modified.');
            }

            return true;
        });

        static::deleting(function (self $log): bool {
            throw new RuntimeException('Audit logs are immutable and cannot be deleted.');
        });
    }

    /**
     * User who triggered this action.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning auditable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isHighRisk(): bool
    {
        return $this->risk_level === self::RISK_HIGH;
    }

    public function isMediumRisk(): bool
    {
        return $this->risk_level === self::RISK_MEDIUM;
    }

    public function isLowRisk(): bool
    {
        return $this->risk_level === self::RISK_LOW;
    }

    /**
     * Scope for high risk events.
     *
     * @param  Builder<AuditLog>  $query
     * @return Builder<AuditLog>
     */
    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->where('risk_level', self::RISK_HIGH);
    }

    /**
     * Scope for medium risk events.
     *
     * @param  Builder<AuditLog>  $query
     * @return Builder<AuditLog>
     */
    public function scopeMediumRisk(Builder $query): Builder
    {
        return $query->where('risk_level', self::RISK_MEDIUM);
    }

    /**
     * Scope for low risk events.
     *
     * @param  Builder<AuditLog>  $query
     * @return Builder<AuditLog>
     */
    public function scopeLowRisk(Builder $query): Builder
    {
        return $query->where('risk_level', self::RISK_LOW);
    }

    /**
     * Scope filter for Audit Log Explorer.
     *
     * @param  Builder<AuditLog>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<AuditLog>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['module'])) {
            $query->where(function (Builder $q) use ($filters): void {
                match ($filters['module']) {
                    'pos' => $q->where('auditable_type', 'like', '%PosOrder%'),
                    'supplier' => $q->where('auditable_type', 'like', '%Supplier%'),
                    'finance' => $q->where(function (Builder $sub): void {
                        $sub->where('auditable_type', 'like', '%JournalEntry%')
                            ->orWhere('auditable_type', 'like', '%Expense%')
                            ->orWhere('auditable_type', 'like', '%Invoice%');
                    }),
                    'inventory' => $q->where(function (Builder $sub): void {
                        $sub->where('auditable_type', 'like', '%Product%')
                            ->orWhere('auditable_type', 'like', '%Material%')
                            ->orWhere('auditable_type', 'like', '%Stock%');
                    }),
                    'users' => $q->where(function (Builder $sub): void {
                        $sub->where('auditable_type', 'like', '%User%')
                            ->orWhere('auditable_type', 'like', '%BusinessMembership%');
                    }),
                    default => null,
                };
            });
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (! empty($filters['q'])) {
            $searchTerm = '%' . $filters['q'] . '%';
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('risk_reason', 'like', $searchTerm)
                    ->orWhere('action', 'like', $searchTerm)
                    ->orWhere('auditable_type', 'like', $searchTerm)
                    ->orWhere('notes', 'like', $searchTerm)
                    ->orWhere('ip_address', 'like', $searchTerm)
                    ->orWhereHas('user', function (Builder $userQuery) use ($searchTerm): void {
                        $userQuery->where('name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    });
            });
        }

        return $query;
    }

    /**
     * Human-friendly module label.
     */
    public function getModuleLabel(): string
    {
        $type = class_basename($this->auditable_type);

        return match ($type) {
            'PosOrder' => 'Kasir POS',
            'Supplier' => 'Pemasok (Supplier)',
            'PurchaseOrder' => 'Pesanan Pembelian',
            'JournalEntry' => 'Jurnal Finansial',
            'Expense' => 'Biaya Operasional',
            'Invoice', 'SupplierInvoice' => 'Faktur Pembayaran',
            'Product' => 'Katalog Produk',
            'Material' => 'Bahan Baku',
            'BusinessMembership', 'User' => 'Pengguna & Otoritas',
            'StockAdjustment', 'StockTransfer' => 'Mutasi Stok',
            default => $type,
        };
    }
}

