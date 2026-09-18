<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasFactory;

    protected $table = 'legal_pages';

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'meta_title',
        'meta_description',
        'content_general',
        'content_owner',
        'content_customer',
        'version',
        'effective_date',
        'is_published',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_published'   => 'boolean',
    ];

    /**
     * Scope to only published legal pages.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Helper to find by slug with fallback.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
