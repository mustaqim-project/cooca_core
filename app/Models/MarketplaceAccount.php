<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceAccount extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const CHANNEL_SHOPEE = 'shopee';
    public const CHANNEL_TIKTOK = 'tiktok_shop';
    public const CHANNEL_TOKOPEDIA = 'tokopedia';

    public const STATUS_CONNECTED = 'connected';
    public const STATUS_DISCONNECTED = 'disconnected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'business_id',
        'channel',
        'shop_id',
        'shop_name',
        'status',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'refresh_token_expires_at',
        'auto_sync_stock',
        'auto_sync_price',
        'stock_buffer',
        'price_multiplier',
        'settings',
        'last_synced_at',
        'error_message',
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
            'auto_sync_stock'          => 'boolean',
            'auto_sync_price'          => 'boolean',
            'stock_buffer'             => 'integer',
            'price_multiplier'         => 'float',
            'settings'                 => 'array',
            'last_synced_at'           => 'datetime',
        ];
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    public function isExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function getChannelLabel(): string
    {
        return match ($this->channel) {
            self::CHANNEL_SHOPEE   => 'Shopee',
            self::CHANNEL_TIKTOK   => 'TikTok Shop',
            self::CHANNEL_TOKOPEDIA => 'Tokopedia',
            default                => ucfirst((string) $this->channel),
        };
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return HasMany<MarketplaceProductMapping, $this>
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(MarketplaceProductMapping::class, 'marketplace_account_id');
    }

    /**
     * @return HasMany<MarketplaceOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(MarketplaceOrder::class, 'marketplace_account_id');
    }

    /**
     * @return HasMany<MarketplaceSyncLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MarketplaceSyncLog::class, 'marketplace_account_id');
    }
}
