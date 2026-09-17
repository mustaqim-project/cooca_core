<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SocialPostTarget
 *
 * Representasi target publikasi per saluran media sosial (Facebook, Instagram,
 * Threads, TikTok) yang mendukung status independen dan partial success retry.
 *
 * @property string $id
 * @property string $social_media_post_id
 * @property string $social_media_account_id
 * @property string $provider
 * @property string $channel
 * @property string $content_type
 * @property string|null $custom_caption
 * @property string $status
 * @property string|null $platform_post_id
 * @property string|null $error_message
 * @property array|null $metrics
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $retry_count
 * @property-read \App\Models\SocialMediaPost $post
 * @property-read \App\Models\SocialMediaAccount $account
 */
class SocialPostTarget extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'social_post_targets';

    protected $fillable = [
        'social_media_post_id',
        'social_media_account_id',
        'provider',
        'channel',
        'content_type',
        'custom_caption',
        'status',
        'platform_post_id',
        'error_message',
        'metrics',
        'published_at',
        'retry_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metrics'      => 'array',
            'published_at' => 'datetime',
            'retry_count'  => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialMediaPost::class, 'social_media_post_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'social_media_account_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed';
    }

    public function getEffectiveCaption(): string
    {
        return ! empty($this->custom_caption)
            ? $this->custom_caption
            : ($this->post?->content ?? '');
    }
}
