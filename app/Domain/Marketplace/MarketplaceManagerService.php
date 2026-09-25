<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Domain\Marketplace\Contracts\MarketplaceAdapterInterface;
use App\Domain\Marketplace\Drivers\Shopee\ShopeeAdapter;
use App\Domain\Marketplace\Drivers\TikTokShop\TikTokShopAdapter;
use App\Domain\Marketplace\Drivers\Tokopedia\TokopediaAdapter;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceManagerService
{
    /**
     * Resolve adapter instance for the given marketplace channel.
     */
    public function driver(string $channel): MarketplaceAdapterInterface
    {
        $normalized = strtolower(trim($channel));
        if ($normalized === 'tiktok' || $normalized === 'tiktok-tokopedia') {
            $normalized = MarketplaceAccount::CHANNEL_TIKTOK;
        }

        return match ($normalized) {
            MarketplaceAccount::CHANNEL_SHOPEE   => app(ShopeeAdapter::class),
            MarketplaceAccount::CHANNEL_TIKTOK    => app(TikTokShopAdapter::class),
            MarketplaceAccount::CHANNEL_TOKOPEDIA => app(TokopediaAdapter::class),
            default                              => throw new \InvalidArgumentException("Channel marketplace [{$channel}] tidak didukung."),
        };
    }

    /**
     * Generate secure, multi-tenant signed state token for OAuth redirect.
     */
    public function generateOAuthState(Business $business, User $user, string $channel): string
    {
        $payload = [
            'business_id' => $business->id,
            'user_id'     => $user->id,
            'channel'     => $channel,
            'nonce'       => Str::random(24),
            'ts'          => time(),
        ];

        return Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * Decrypt and validate OAuth state token.
     *
     * @return array{business_id: string, user_id: mixed, channel: string, ts: int}|null
     */
    public function validateOAuthState(string $state): ?array
    {
        try {
            $decrypted = Crypt::decryptString($state);
            $data      = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);

            if (empty($data['business_id']) || empty($data['channel'])) {
                return null;
            }

            // State expiration check (30 minutes)
            if (time() - (int) ($data['ts'] ?? 0) > 1800) {
                return null;
            }

            return $data;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Complete OAuth connection and save/update MarketplaceAccount.
     */
    public function connectAccount(Business $business, string $channel, array $params, string $redirectUri): MarketplaceAccount
    {
        $adapter = $this->driver($channel);
        $result  = $adapter->handleAuthCallback($business, $params, $redirectUri);

        return DB::transaction(function () use ($business, $channel, $result) {
            $account = MarketplaceAccount::firstOrNew([
                'business_id' => $business->id,
                'channel'     => $channel,
                'shop_id'     => (string) $result['shop_id'],
            ]);

            $account->shop_name                = $result['shop_name'] ?? ('Toko ' . ucfirst($channel));
            $account->status                   = MarketplaceAccount::STATUS_CONNECTED;
            $account->access_token             = $result['access_token'];
            $account->refresh_token            = $result['refresh_token'] ?? null;
            $account->token_expires_at         = now()->addSeconds((int) ($result['expires_in'] ?? 86400));
            $account->refresh_token_expires_at = now()->addSeconds((int) ($result['refresh_expires_in'] ?? 2592000));
            $account->settings                 = array_merge($account->settings ?? [], $result['extra'] ?? []);
            $account->error_message            = null;
            $account->last_synced_at           = now();
            $account->save();

            MarketplaceSyncLog::log(
                businessId: $business->id,
                channel: $channel,
                entityType: 'auth',
                action: 'connect_account',
                status: 'success',
                entityId: $account->id,
                payload: ['shop_id' => $account->shop_id, 'shop_name' => $account->shop_name],
                marketplaceAccountId: $account->id
            );

            return $account;
        });
    }

    /**
     * Disconnect marketplace account.
     */
    public function disconnectAccount(Business $business, MarketplaceAccount $account): bool
    {
        if ($account->business_id !== $business->id) {
            abort(404);
        }

        return DB::transaction(function () use ($business, $account) {
            $account->status        = MarketplaceAccount::STATUS_DISCONNECTED;
            $account->access_token  = null;
            $account->refresh_token = null;
            $account->save();

            MarketplaceSyncLog::log(
                businessId: $business->id,
                channel: $account->channel,
                entityType: 'auth',
                action: 'disconnect_account',
                status: 'success',
                entityId: $account->id,
                marketplaceAccountId: $account->id
            );

            return true;
        });
    }

    /**
     * Map a COOCA product to marketplace external item with channel pricing.
     */
    public function mapProduct(
        Business $business,
        Product $product,
        MarketplaceAccount $account,
        array $mappingData
    ): MarketplaceProductMapping {
        if ($product->business_id !== $business->id || $account->business_id !== $business->id) {
            abort(404);
        }

        return DB::transaction(function () use ($business, $product, $account, $mappingData) {
            $mapping = MarketplaceProductMapping::firstOrNew([
                'business_id'            => $business->id,
                'product_id'             => $product->id,
                'marketplace_account_id' => $account->id,
            ]);

            $mapping->channel               = $account->channel;
            $mapping->external_product_id   = (string) $mappingData['external_product_id'];
            $mapping->external_sku_id       = $mappingData['external_sku_id'] ?? null;
            $mapping->external_sku_code     = $mappingData['external_sku_code'] ?? null;
            $mapping->external_product_name = $mappingData['external_product_name'] ?? $product->name;
            
            // Channel specific pricing
            if (array_key_exists('channel_price', $mappingData)) {
                $mapping->channel_price = $mappingData['channel_price'] !== null ? (float) $mappingData['channel_price'] : null;
            }
            if (array_key_exists('sync_price_auto', $mappingData)) {
                $mapping->sync_price_auto = (bool) $mappingData['sync_price_auto'];
            }

            // Channel specific stock
            if (array_key_exists('channel_stock', $mappingData)) {
                $mapping->channel_stock = $mappingData['channel_stock'] !== null ? (int) $mappingData['channel_stock'] : null;
            }
            if (array_key_exists('sync_stock_auto', $mappingData)) {
                $mapping->sync_stock_auto = (bool) $mappingData['sync_stock_auto'];
            }

            $mapping->sync_status  = MarketplaceProductMapping::STATUS_SYNCED;
            $mapping->raw_metadata = array_merge($mapping->raw_metadata ?? [], $mappingData['metadata'] ?? []);
            $mapping->save();

            return $mapping;
        });
    }
}
