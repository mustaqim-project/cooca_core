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
        if (! $account || ! $account->isConnected() || ! ($account->is_active ?? true)) {
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

        if (! $account || ! $account->isConnected() || ! ($account->is_active ?? true) || ! $product) {
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
        if (! $account->isConnected() || ! ($account->is_active ?? true)) {
            return [
                'total'   => 0,
                'success' => 0,
                'failed'  => 0,
            ];
        }

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

    /**
     * Upload and publish a new product listing with all its gallery images directly from COOCA to a marketplace channel.
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, channel_name?: string, mapping?: MarketplaceProductMapping, external_product_id?: string, message?: string}
     */
    public function publishProductToChannel(Product $product, string $channel, array $options = []): array
    {
        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $account = MarketplaceAccount::where('business_id', $product->business_id)
            ->where('channel', $normalizedChannel)
            ->where('status', MarketplaceAccount::STATUS_CONNECTED)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return [
                'success' => false,
                'message' => "Akun marketplace {$channel} belum terhubung atau sedang tidak aktif.",
            ];
        }

        // GUARDRAIL 1: Sektor Jasa / Layanan Fisik
        if ($product->isService()) {
            return [
                'success' => false,
                'message' => "Produk berjenis jasa/layanan fisik (\"{$product->name}\") tidak dapat diterbitkan ke saluran ekspedisi marketplace.",
            ];
        }

        // GUARDRAIL 2: Sektor Apotek / Farmasi - BPOM RI Hard-Lock
        if ($product->business?->isPharmacy() && $product->isRestrictedPharmacyProduct()) {
            return [
                'success' => false,
                'message' => "Produk \"{$product->name}\" tergolong obat keras / resep dokter yang dilarang diperjualbelikan di marketplace umum berdasarkan regulasi BPOM RI.",
            ];
        }

        // GUARDRAIL 3: Anti-Margin Bleed Guard
        $syncPriceAuto   = ! empty($options['sync_price_auto'] ?? true);
        $priceMultiplier = (float) ($options['price_multiplier'] ?? 1.00);
        $channelPrice    = ! empty($options['channel_price']) ? (float) $options['channel_price'] : null;

        $effectivePrice = $syncPriceAuto 
            ? round((float) $product->selling_price * $priceMultiplier)
            : (float) ($channelPrice ?? $product->selling_price);

        $baseCost = (float) ($product->base_cost ?? 0.0);

        if ($baseCost > 0 && $effectivePrice < $baseCost && empty($options['allow_below_cost'])) {
            $diffFormatted = number_format($baseCost - $effectivePrice, 0, ',', '.');
            $costFormatted = number_format($baseCost, 0, ',', '.');
            return [
                'success' => false,
                'message' => "Harga jual saluran (Rp " . number_format($effectivePrice, 0, ',', '.') . ") berada di bawah modal dasar HPP (Rp {$costFormatted}). Potensi kerugian Rp {$diffFormatted}/unit.",
            ];
        }

        $effectiveStock = isset($options['custom_stock']) && $options['custom_stock'] !== null && $options['custom_stock'] !== ''
            ? (int) $options['custom_stock']
            : $this->resolveEffectiveStock($product);

        // Resolve category from hierarchy: 1. Explicit options -> 2. Category parent -> 3. Smart suggestion
        $resolvedCategoryId   = $options['category_id'] ?? $options['marketplace_category_id'] ?? $product->category?->marketplace_category_id;
        $resolvedCategoryName = $options['category_name'] ?? $options['marketplace_category_name'] ?? $product->category?->marketplace_category_name;

        if (empty($resolvedCategoryId)) {
            $suggested = MarketplaceCategoryRegistry::suggestCategory($product);
            $resolvedCategoryId   = $suggested['id'];
            $resolvedCategoryName = $suggested['name'];
        }

        $adapter = $this->manager->driver($normalizedChannel);

        try {
            $publishResult = $adapter->publishProduct($account, $product, array_merge($options, [
                'price'         => $effectivePrice,
                'stock'         => $effectiveStock,
                'sku'           => $product->code ?: ('SKU-' . $product->id),
                'category_id'   => $resolvedCategoryId,
                'category_name' => $resolvedCategoryName,
            ]));

            if (empty($publishResult['success']) || empty($publishResult['external_product_id'])) {
                return [
                    'success' => false,
                    'message' => $publishResult['message'] ?? 'Gagal memproses penerbitan produk ke marketplace.',
                ];
            }

            $externalId  = (string) $publishResult['external_product_id'];
            $externalSku = $publishResult['external_sku_code'] ?? $product->code;

            $mapping = MarketplaceProductMapping::updateOrCreate(
                [
                    'business_id' => $product->business_id,
                    'product_id'  => $product->id,
                    'channel'     => $normalizedChannel,
                ],
                [
                    'marketplace_account_id' => $account->id,
                    'external_product_id'   => $externalId,
                    'external_sku_code'     => $externalSku,
                    'external_product_name' => $product->name,
                    'channel_price'         => $channelPrice,
                    'sync_price_auto'       => $syncPriceAuto,
                    'price_multiplier'      => $priceMultiplier,
                    'custom_stock'          => isset($options['custom_stock']) && $options['custom_stock'] !== '' ? (int) $options['custom_stock'] : null,
                    'sync_stock_auto'       => ! empty($options['sync_stock_auto'] ?? true),
                    'stock_buffer'          => (int) ($options['stock_buffer'] ?? 0),
                    'sync_status'           => MarketplaceProductMapping::STATUS_SYNCED,
                    'last_price_synced_at'  => now(),
                    'last_stock_synced_at'  => now(),
                    'last_sync_error'       => null,
                    'is_active'             => true,
                    'raw_metadata'          => array_merge(
                        $publishResult['raw_response'] ?? [],
                        [
                            'category_id'   => $resolvedCategoryId,
                            'category_name' => $resolvedCategoryName,
                        ]
                    ),
                ]
            );


            MarketplaceSyncLog::log(
                businessId: $product->business_id,
                channel: $normalizedChannel,
                entityType: 'product',
                action: 'publish_product',
                status: 'success',
                entityId: $mapping->id,
                payload: [
                    'product_id'          => $product->id,
                    'external_product_id' => $externalId,
                    'price'               => $effectivePrice,
                    'stock'               => $effectiveStock,
                    'images_count'        => $product->images->count() + ($product->image ? 1 : 0),
                ],
                marketplaceAccountId: $account->id
            );

            return [
                'success'             => true,
                'channel_name'        => $adapter->getName(),
                'mapping'             => $mapping,
                'external_product_id' => $externalId,
                'message'             => $publishResult['message'] ?? "Produk \"{$product->name}\" berhasil diterbitkan ke {$adapter->getName()}.",
            ];
        } catch (\Throwable $e) {
            MarketplaceSyncLog::log(
                businessId: $product->business_id,
                channel: $normalizedChannel,
                entityType: 'product',
                action: 'publish_product',
                status: 'failed',
                entityId: $product->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return [
                'success' => false,
                'message' => "Gagal menerbitkan produk ke {$adapter->getName()}: " . $e->getMessage(),
            ];
        }
    }
}

