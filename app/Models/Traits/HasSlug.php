<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the HasSlug trait for the model.
     */
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            $slugColumn = $model->slugColumn();
            $sourceColumn = $model->slugSourceColumn();

            // If slug is explicitly provided or needs generation from source
            $rawSlug = ! empty($model->{$slugColumn})
                ? $model->{$slugColumn}
                : ($model->{$sourceColumn} ?? '');

            if (! empty($rawSlug) && ($model->isDirty($sourceColumn) || $model->isDirty($slugColumn) || empty($model->{$slugColumn}))) {
                $baseSlug = Str::slug((string) $rawSlug);
                $model->{$slugColumn} = $model->generateUniqueSlug($baseSlug);
            }
        });
    }

    /**
     * Get the source column to generate slug from.
     */
    public function slugSourceColumn(): string
    {
        return 'name';
    }

    /**
     * Get the column name for the slug.
     */
    public function slugColumn(): string
    {
        return 'slug';
    }

    /**
     * Scope query to check slug uniqueness (e.g. per business_id).
     */
    public function scopeSlugUniqueness(Builder $query, Model $model): Builder
    {
        if (isset($model->business_id)) {
            $query->where('business_id', $model->business_id);
        }

        return $query;
    }

    /**
     * Generate a unique slug by appending numbers if duplicates exist.
     */
    public function generateUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $count = 1;
        $slugColumn = $this->slugColumn();
        $keyName = $this->getKeyName();

        while ($this->slugExists($slug, $slugColumn, $keyName)) {
            $count++;
            $slug = "{$baseSlug}-{$count}";
        }

        return $slug;
    }

    /**
     * Determine if a slug already exists for another record.
     */
    protected function slugExists(string $slug, string $slugColumn, string $keyName): bool
    {
        $query = static::withoutGlobalScopes()
            ->where($slugColumn, $slug);

        $query = $this->scopeSlugUniqueness($query, $this);

        if ($this->exists && ! empty($this->{$keyName})) {
            $query->where($keyName, '!=', $this->{$keyName});
        }

        return $query->exists();
    }
}
