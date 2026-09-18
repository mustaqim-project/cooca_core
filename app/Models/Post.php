<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'cluster_id',
        'category_id',
        'cluster', // 'tutorial' (Cluster K) or 'edukasi' (Cluster O)
        'category',
        'excerpt',
        'content',
        'cover_image',
        'meta_title',
        'meta_description',
        'author_name',
        'views_count',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'cluster_id' => 'integer',
        'category_id' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    /**
     * Relationship to PostCategory.
     */
    public function postCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * Relationship to PostCluster.
     */
    public function postCluster(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PostCluster::class, 'cluster_id');
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for Cluster K (Tutorial cara).
     */
    public function scopeTutorial(Builder $query): Builder
    {
        return $query->where('cluster', 'tutorial');
    }

    /**
     * Scope for Cluster O (Edukasi topikal).
     */
    public function scopeEdukasi(Builder $query): Builder
    {
        return $query->where('cluster', 'edukasi');
    }

    /**
     * Approximate read time in minutes.
     */
    public function getReadTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Ensure slug is generated if empty.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }
}
