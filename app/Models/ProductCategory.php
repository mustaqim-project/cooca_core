<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'marketplace_category_id',
        'marketplace_category_name',
    ];

    /**
     * Resolve route binding by either UUID id or slug safely scoped to tenant.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where(function ($query) use ($value): void {
            $query->where('id', $value)
                ->orWhere('slug', $value);
        })->firstOrFail();
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the registered marketplace category definition.
     *
     * @return array{id: string, name: string, description: string, icon: string, keywords: array<string>}|null
     */
    public function getMarketplaceCategory(): ?array
    {
        if (empty($this->marketplace_category_id)) {
            return null;
        }

        return \App\Domain\Marketplace\MarketplaceCategoryRegistry::find($this->marketplace_category_id);
    }

    /**
     * Check if category has a mapped marketplace category.
     */
    public function hasMarketplaceCategory(): bool
    {
        return ! empty($this->marketplace_category_id);
    }
}
