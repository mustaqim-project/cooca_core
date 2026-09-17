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
 * Class SocialMediaAccount
 *
 * Repositori kredensial terisolasi untuk akun media sosial (Facebook Page,
 * Instagram Business, Threads) per tenant (business_id).
 *
 * @property string $id
 * @property string $business_id
 * @property string $platform
 * @property string $account_id
 * @property string $account_name
 * @property string|null $username
 * @property string|null $profile_picture_url
 * @property string $access_token
 * @property string $token_type
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property string $status
 * @property array|null $settings
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Business $business
 */
class SocialMediaAccount extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'social_media_accounts';

    protected $fillable = [
        'business_id',
        'provider',
        'platform',
        'account_id',
        'account_name',
        'username',
        'profile_picture_url',
        'access_token',
        'refresh_token',
        'token_type',
        'token_expires_at',
        'refresh_token_expires_at',
        'status',
        'settings',
        'metadata',
        'scopes',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token'             => 'encrypted',
            'refresh_token'            => 'encrypted',
            'token_expires_at'         => 'datetime',
            'refresh_token_expires_at' => 'datetime',
            'settings'                 => 'array',
            'metadata'                 => 'array',
            'scopes'                   => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class, 'social_media_account_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SocialPostTarget::class, 'social_media_account_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialMediaComment::class, 'social_media_account_id');
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isMeta(): bool
    {
        return strtolower($this->provider ?: 'meta') === 'meta';
    }

    public function isTikTok(): bool
    {
        return strtolower($this->provider ?? '') === 'tiktok' || strtolower($this->platform ?? '') === 'tiktok';
    }

    public function isConnected(): bool
    {
        $status = strtolower($this->status ?? '');

        if (! in_array($status, ['active', 'connected'], true)) {
            return false;
        }

        if ($this->token_expires_at !== null && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function needsTokenRefresh(int $bufferMinutes = 15): bool
    {
        if ($this->token_expires_at === null) {
            return false;
        }

        return $this->token_expires_at->lessThanOrEqualTo(now()->addMinutes($bufferMinutes));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
