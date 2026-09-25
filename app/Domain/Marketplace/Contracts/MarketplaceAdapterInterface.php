<?php

declare(strict_types=1);

namespace App\Domain\Marketplace\Contracts;

use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use Illuminate\Http\Request;

interface MarketplaceAdapterInterface
{
    /**
     * Get channel identifier (shopee, tiktok_shop, tokopedia).
     */
    public function getChannel(): string;

    /**
     * Get user-friendly name for this marketplace channel.
     */
    public function getName(): string;

    /**
     * Generate OAuth 2.0 authorization redirect URL.
     */
    public function getAuthUrl(Business $business, string $redirectUri, string $state): string;

    /**
     * Handle OAuth callback: exchange code for access_token & refresh_token.
     *
     * @param array<string, mixed> $params
     * @return array{shop_id: string, shop_name: string, access_token: string, refresh_token: string, expires_in: int, refresh_expires_in?: int, extra?: array}
     */
    public function handleAuthCallback(Business $business, array $params, string $redirectUri): array;

    /**
     * Refresh expired OAuth access token.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function refreshToken(MarketplaceAccount $account): array;

    /**
     * Push stock update to marketplace.
     */
    public function pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool;

    /**
     * Push price update to marketplace.
     */
    public function pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool;

    /**
     * Fetch products list from marketplace for mapping.
     *
     * @param array<string, mixed> $params
     * @return array{products: array<int, array{id: string, name: string, sku: string, price: float, stock: int, image_url: ?string, variations: array}>, has_next: bool}
     */
    public function getProducts(MarketplaceAccount $account, array $params = []): array;

    /**
     * Pull recent orders from marketplace.
     *
     * @param array<string, mixed> $params
     * @return array<int, array{order_id: string, order_sn?: string, buyer_name: string, total_amount: float, status: string, items: array, raw: array}>
     */
    public function pullOrders(MarketplaceAccount $account, array $params = []): array;

    /**
     * Verify and parse incoming webhook request from marketplace.
     *
     * @param array<string, mixed> $headers
     * @return array{event: string, shop_id: string, payload: array, is_valid: bool}
     */
    public function handleWebhook(Request $request, string $rawBody, array $headers): array;
}
