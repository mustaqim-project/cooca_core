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

class ApprovalRule extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const DOC_PURCHASE_ORDER = 'purchase_order';
    public const DOC_EXPENSE = 'expense';
    public const DOC_SUPPLIER_INVOICE = 'supplier_invoice';
    public const DOC_STOCK_ADJUSTMENT = 'stock_adjustment';

    protected $table = 'approval_rules';

    protected $fillable = [
        'business_id',
        'document_type',
        'name',
        'min_amount',
        'max_amount',
        'required_levels',
        'approver_role_level_1',
        'approver_role_level_2',
        'approver_role_level_3',
        'level_1_role',
        'level_2_role',
        'level_3_role',
        'is_active',
    ];

    public function getLevel1RoleAttribute(): ?string
    {
        return $this->approver_role_level_1;
    }

    public function setLevel1RoleAttribute(?string $value): void
    {
        $this->attributes['approver_role_level_1'] = $value;
    }

    public function getLevel2RoleAttribute(): ?string
    {
        return $this->approver_role_level_2;
    }

    public function setLevel2RoleAttribute(?string $value): void
    {
        $this->attributes['approver_role_level_2'] = $value;
    }

    public function getLevel3RoleAttribute(): ?string
    {
        return $this->approver_role_level_3;
    }

    public function setLevel3RoleAttribute(?string $value): void
    {
        $this->attributes['approver_role_level_3'] = $value;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_amount' => 'float',
            'max_amount' => 'float',
            'required_levels' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'rule_id');
    }

    /**
     * Scope active rules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope rules matching a specific document type and amount.
     */
    public function scopeForDocument(Builder $query, string $docType, float $amount): Builder
    {
        return $query->where('document_type', $docType)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function (Builder $q) use ($amount): void {
                $q->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            });
    }

    /**
     * Get the required role slug for a specific level.
     */
    public function getRoleForLevel(int $level): ?string
    {
        return match ($level) {
            1 => $this->approver_role_level_1,
            2 => $this->approver_role_level_2,
            3 => $this->approver_role_level_3,
            default => null,
        };
    }
}
