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

class ApprovalRequest extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'approval_requests';

    protected $fillable = [
        'business_id',
        'document_type',
        'document_id',
        'requester_id',
        'rule_id',
        'amount',
        'current_level',
        'total_levels',
        'status',
        'rejection_reason',
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
            'current_level' => 'integer',
            'total_levels' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ApprovalRule::class, 'rule_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'approval_request_id')->orderBy('created_at', 'asc');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForCurrentBusiness(Builder $query): Builder
    {
        $bizId = \App\Support\Context::businessId();
        return $bizId ? $query->where('business_id', $bizId) : $query;
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getRequiredLevelsAttribute(): int
    {
        return $this->total_levels;
    }

    /**
     * Resolve underlying Eloquent model of the document.
     */
    public function getDocumentModel(): ?Model
    {
        return match ($this->document_type) {
            ApprovalRule::DOC_PURCHASE_ORDER => PurchaseOrder::find($this->document_id),
            ApprovalRule::DOC_EXPENSE => Expense::find($this->document_id),
            ApprovalRule::DOC_SUPPLIER_INVOICE => SupplierInvoice::find($this->document_id),
            ApprovalRule::DOC_STOCK_ADJUSTMENT => StockAdjustment::find($this->document_id),
            default => null,
        };
    }

    /**
     * Document type human-readable label.
     */
    public function getDocumentTypeLabel(): string
    {
        return match ($this->document_type) {
            ApprovalRule::DOC_PURCHASE_ORDER => 'Pesanan Pembelian (PO)',
            ApprovalRule::DOC_EXPENSE => 'Pengeluaran Kas / Biaya',
            ApprovalRule::DOC_SUPPLIER_INVOICE => 'Faktur Tagihan Pemasok',
            ApprovalRule::DOC_STOCK_ADJUSTMENT => 'Penyesuaian Stok Gudang',
            default => ucfirst(str_replace('_', ' ', $this->document_type)),
        };
    }
}
