<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrder extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'marketplace_account_id',
        'channel',
        'external_order_id',
        'external_order_sn',
        'buyer_name',
        'buyer_phone',
        'total_amount',
        'channel_fee',
        'order_status',
        'items_summary',
        'shipping_provider',
        'tracking_number',
        'synced_order_id',
        'raw_payload',
        'placed_at',
        'synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount'   => 'float',
            'channel_fee'    => 'float',
            'items_summary'  => 'array',
            'raw_payload'    => 'array',
            'placed_at'      => 'datetime',
            'synced_at'      => 'datetime',
        ];
    }

    public function getChannelLabel(): string
    {
        return match ($this->channel) {
            'shopee'       => 'Shopee',
            'tiktok_shop'  => 'TikTok Shop',
            'tokopedia'    => 'Tokopedia',
            default        => ucfirst((string) $this->channel),
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
     * @return BelongsTo<MarketplaceAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }
}
