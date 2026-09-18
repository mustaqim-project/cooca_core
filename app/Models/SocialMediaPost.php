<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SocialMediaPost
 *
 * @property string $id
 * @property string $business_id
 * @property string $social_media_account_id
 * @property string $platform
 * @property string|null $platform_post_id
 * @property string $content
 * @property string $media_type
 * @property array|null $media_urls
 * @property string $status
 * @property string|null $error_message
 * @property array|null $metrics
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Business $business
 * @property-read \App\Models\SocialMediaAccount $account
 */
class SocialMediaPost extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'social_media_posts';

    protected $fillable = [
        'business_id',
        'admin_id',
        'is_platform',
        'social_media_account_id',
        'platform',
        'platform_post_id',
        'content',
        'media_type',
        'media_urls',
        'local_media_paths',
        'status',
        'error_message',
        'metrics',
        'scheduled_at',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_platform'       => 'boolean',
            'media_urls'        => 'array',
            'local_media_paths' => 'array',
            'metrics'           => 'array',
            'scheduled_at'      => 'datetime',
            'published_at'      => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'social_media_account_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SocialPostTarget::class, 'social_media_post_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(SocialPostMedia::class, 'social_media_post_id')->orderBy('sort_order', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialMediaComment::class, 'social_media_post_id');
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->where('is_platform', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' || $this->status === 'completed';
    }

    public function isPartiallyFailed(): bool
    {
        if ($this->status === 'partially_failed') {
            return true;
        }

        if ($this->relationLoaded('targets') && $this->targets->isNotEmpty()) {
            $hasSuccess = $this->targets->contains(fn ($t) => $t->status === 'published');
            $hasFailed = $this->targets->contains(fn ($t) => $t->status === 'failed');

            return $hasSuccess && $hasFailed;
        }

        return false;
    }

    public function canRetry(): bool
    {
        return in_array($this->status, ['failed', 'partially_failed'], true)
            || ($this->relationLoaded('targets') && $this->targets->contains(fn ($t) => $t->status === 'failed'));
    }

    public function getMetric(string $key, int $default = 0): int
    {
        return (int) data_get($this->metrics, $key, $default);
    }

    /**
     * Recalculate and synchronize parent post status based on target statuses.
     */
    public function syncStatusFromTargets(): void
    {
        $targets = $this->targets()->get();
        if ($targets->isEmpty()) {
            return;
        }

        $total = $targets->count();
        $publishedCount = $targets->where('status', 'published')->count();
        $failedCount = $targets->where('status', 'failed')->count();
        $scheduledCount = $targets->where('status', 'scheduled')->count();
        $processingCount = $targets->whereIn('status', ['pending', 'processing'])->count();

        if ($publishedCount === $total) {
            $this->update([
                'status'       => 'published',
                'published_at' => $this->published_at ?? now(),
            ]);
        } elseif ($failedCount === $total) {
            $this->update([
                'status' => 'failed',
            ]);
        } elseif ($publishedCount > 0 && $failedCount > 0 && ($publishedCount + $failedCount) === $total) {
            $this->update([
                'status' => 'partially_failed',
            ]);
        } elseif ($publishedCount > 0 && $scheduledCount > 0) {
            $this->update([
                'status' => 'partially_published',
            ]);
        } elseif ($scheduledCount > 0 && $processingCount === 0 && $publishedCount === 0) {
            $this->update([
                'status' => 'scheduled',
            ]);
        } elseif ($processingCount > 0) {
            $this->update([
                'status' => 'publishing',
            ]);
        }
    }
}
