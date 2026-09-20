<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_ASSET = 'asset';

    public const TYPE_LIABILITY = 'liability';

    public const TYPE_EQUITY = 'equity';

    public const TYPE_REVENUE = 'revenue';

    public const TYPE_COGS = 'cogs';

    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'business_id',
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'is_system',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function isRoot(): bool
    {
        return empty($this->parent_id);
    }

    public function canBeDeleted(): bool
    {
        if ($this->is_system) {
            return false;
        }

        return $this->children()->count() === 0 && $this->lines()->count() === 0;
    }

    /**
     * Dapatkan seluruh ID turunan (sub-akun dan cucu-akun) secara rekursif.
     *
     * @return array<int, string>
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_ASSET => 'Aset / Aktiva',
            self::TYPE_LIABILITY => 'Kewajiban / Hutang',
            self::TYPE_EQUITY => 'Ekuitas / Modal',
            self::TYPE_REVENUE => 'Pendapatan',
            self::TYPE_COGS => 'Beban Pokok Penjualan (HPP)',
            self::TYPE_EXPENSE => 'Beban Operasional',
            default => ucfirst((string) $this->type),
        };
    }
}

