<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SocialMediaComment
 *
 * @property string $id
 * @property string $business_id
 * @property string $social_media_account_id
 * @property string|null $social_media_post_id
 * @property string $platform
 * @property string $platform_comment_id
 * @property string $platform_post_id
 * @property string|null $parent_comment_id
 * @property string|null $from_id
 * @property string|null $from_name
 * @property string $message
 * @property bool $is_from_page
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Business $business
 * @property-read \App\Models\SocialMediaAccount $account
 * @property-read \App\Models\SocialMediaPost|null $post
 */
class SocialMediaComment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'social_media_comments';

    protected $fillable = [
        'business_id',
        'social_media_account_id',
        'social_media_post_id',
        'platform',
        'platform_comment_id',
        'platform_post_id',
        'parent_comment_id',
        'from_id',
        'from_name',
        'message',
        'is_from_page',
        'status',
        'created_time',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_from_page' => 'boolean',
            'created_time' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'social_media_account_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialMediaPost::class, 'social_media_post_id');
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'unread');
    }
}
