<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCategory extends Model
{
    use HasFactory, HasUuid;

    public const CODE_DIRECT_MATERIAL = 'direct_material';

    public const CODE_DIRECT_LABOR = 'direct_labor';

    public const CODE_VARIABLE_OVERHEAD = 'variable_overhead';

    public const CODE_FIXED_OVERHEAD = 'fixed_overhead';

    public const CODE_OTHER = 'other';

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'description',
    ];

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return HasMany<CostComponent, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(CostComponent::class);
    }
}
