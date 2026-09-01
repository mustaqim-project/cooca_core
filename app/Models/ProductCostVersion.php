<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ProductCostVersion extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_IN_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $table = 'product_cost_versions';

    protected $fillable = [
        'business_id',
        'product_id',
        'cost_model_id',
        'version_number',
        'version_label',
        'status',
        'total_hpp',
        'hpp_per_unit',
        'hpp_snapshot',
        'formula_definition_snapshot',
        'status_history',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'total_hpp' => 'float',
            'hpp_per_unit' => 'float',
            'hpp_snapshot' => 'array',
            'formula_definition_snapshot' => 'array',
            'status_history' => 'array',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Immutability Enforcement (§76 Blueprint)
        static::updating(function (ProductCostVersion $version): void {
            $originalStatus = $version->getOriginal('status');
            $lockedStatuses = [self::STATUS_APPROVED, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED];

            if (in_array($originalStatus, $lockedStatuses, true)) {
                $dirty = array_keys($version->getDirty());
                $allowedTransitions = ['status', 'status_history', 'effective_from', 'effective_to', 'updated_at'];
                $disallowedChanges = array_diff($dirty, $allowedTransitions);

                if (! empty($disallowedChanges)) {
                    throw new InvalidArgumentException("Product cost version #{$version->version_number} is {$originalStatus} and cannot be modified.");
                }
            }
        });
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<CostModel, $this>
     */
    public function costModel(): BelongsTo
    {
        return $this->belongsTo(CostModel::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
