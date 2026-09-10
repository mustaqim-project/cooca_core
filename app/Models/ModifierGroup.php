<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const SELECTION_SINGLE = 'single';
    public const SELECTION_MULTIPLE = 'multiple';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'selection_type',
        'min_selection',
        'max_selection',
        'is_required',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_selection' => 'integer',
            'max_selection' => 'integer',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ModifierOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class, 'modifier_group_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<ModifierOption, $this>
     */
    public function activeOptions(): HasMany
    {
        return $this->hasMany(ModifierOption::class, 'modifier_group_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups', 'modifier_group_id', 'product_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
