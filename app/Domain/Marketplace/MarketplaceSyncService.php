<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class MarketplaceSyncService
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Push price for a specific product mapping.
     */
    public function syncProductPrice(MarketplaceProductMapping $mapping): bool
    {
        $account = $mapping->account;
        if (! $account || ! $account->isConnected()) {
            return false;
        }

        $adapter = $this->manager->driver($mapping->channel);
        $price   = $mapping->getEffectivePrice();

        try {
            $ok = $adapter->pushPrice($account, $mapping, $price);

            if ($ok) {
                $mapping->last_price_synced_at = now();
                $mapping->last_sync_error      = null;
                $mapping->sync_status          = MarketplaceProductMapping::STATUS_SYNCED;
                $mapping->save();

                MarketplaceSyncLog::log(
                    businessId: $mapping->business_id,
                    channel: $mapping->channel,
                    entityType: 'price',
                    action: 'push_price',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['price' => $price, 'external_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );
            } else {
                $mapping->last_sync_error = 'Gagal sinkronisasi harga ke ' . $mapping->channel;
                $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
                $mapping->save();
            }

            return $ok;
        } catch (\Throwable $e) {
            $mapping->last_sync_error = $e->getMessage();
            $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
            $mapping->save();

            MarketplaceSyncLog::log(
                businessId: $mapping->business_id,
                channel: $mapping->channel,
                entityType: 'price',
                action: 'push_price',
                status: 'failed',
                entityId: $mapping->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return false;
        }
    }

    /**
     * Resolve effective available stock considering inventory stock, safety buffer, and manual override.
     */
    public function resolveEffectiveStock(Product $product, ?MarketplaceProductMapping $mapping = null): int
    {
        if ($mapping && ! $mapping->sync_stock_auto && $mapping->custom_stock !== null) {
            return max(0, (int) $mapping->custom_stock);
        }

        if ($mapping && ! $mapping->sync_stock_auto && $mapping->channel_stock !== null) {
            return max(0, (int) $mapping->channel_stock);
        }

        $rawStock = (float) ($product->calculateEffectiveAvailableStock() ?? 0);

        if ($rawStock <= 0) {
            $rawStock = (float) InventoryStock::where('business_id', $product->business_id)
                ->where('product_id', $product->id)
                ->sum('quantity');
        }

        if ($rawStock <= 0 && isset($product->attributes['stock'])) {
            $rawStock = (float) $product->attributes['stock'];
        } elseif ($rawStock <= 0 && isset($product->stock)) {
            $rawStock = (float) $product->stock;
        }

        $buffer = (int) ($mapping?->stock_buffer ?? $mapping?->account?->stock_buffer ?? 0);

        return max(0, (int) floor($rawStock - $buffer));
    }

    /**
     * Push stock for a specific product mapping.
     */
    public function syncProductStock(MarketplaceProductMapping $mapping): bool
    {
        $account = $mapping->account;
        $product = $mapping->product;

        if (! $account || ! $account->isConnected() || ! $product) {
            return false;
        }

        $adapter = $this->manager->driver($mapping->channel);
        $stockToPush = $this->resolveEffectiveStock($product, $mapping);

        try {
            $ok = $adapter->pushStock($account, $mapping, $stockToPush);

            if ($ok) {
                $mapping->last_stock_synced_at = now();
                $mapping->last_sync_error      = null;
                $mapping->sync_status          = MarketplaceProductMapping::STATUS_SYNCED;
                $mapping->save();

                MarketplaceSyncLog::log(
                    businessId: $mapping->business_id,
                    channel: $mapping->channel,
                    entityType: 'stock',
                    action: 'push_stock',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['stock' => $stockToPush, 'external_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );
            } else {
                $mapping->last_sync_error = 'Gagal sinkronisasi stok ke ' . $mapping->channel;
                $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
                $mapping->save();
            }

            return $ok;
        } catch (\Throwable $e) {
            $mapping->last_sync_error = $e->getMessage();
            $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
            $mapping->save();

            MarketplaceSyncLog::log(
                businessId: $mapping->business_id,
                channel: $mapping->channel,
                entityType: 'stock',
                action: 'push_stock',
                status: 'failed',
                entityId: $mapping->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return false;
        }
    }

    /**
     * Sync price & stock for all channels mapped to a given product.
     */
    public function syncProductToAllChannels(Product $product): array
    {
        $mappings = MarketplaceProductMapping::where('product_id', $product->id)->get();
        $results  = [];

        foreach ($mappings as $mapping) {
            $priceOk = $mapping->sync_price_auto ? $this->syncProductPrice($mapping) : true;
            $stockOk = $mapping->sync_stock_auto ? $this->syncProductStock($mapping) : true;

            $results[$mapping->channel] = [
                'price' => $priceOk,
                'stock' => $stockOk,
            ];
        }

        return $results;
    }

    /**
     * Trigger batch sync for all mapped products of a business/account.
     */
    public function syncAllForAccount(MarketplaceAccount $account): array
    {
        $mappings = MarketplaceProductMapping::where('marketplace_account_id', $account->id)->get();
        $successCount = 0;
        $failCount    = 0;

        foreach ($mappings as $mapping) {
            $okPrice = $this->syncProductPrice($mapping);
            $okStock = $this->syncProductStock($mapping);

            if ($okPrice && $okStock) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $account->last_synced_at = now();
        $account->save();

        return [
            'total'   => $mappings->count(),
            'success' => $successCount,
            'failed'  => $failCount,
        ];
    }
}
