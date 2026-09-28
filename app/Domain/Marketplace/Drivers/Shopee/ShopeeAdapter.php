<?php

declare(strict_types=1);

namespace App\Domain\Marketplace\Drivers\Shopee;

use App\Domain\Marketplace\Contracts\MarketplaceAdapterInterface;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeAdapter implements MarketplaceAdapterInterface
{
    protected string $host;
    protected int $partnerId;
    protected string $partnerKey;

    public function __construct()
    {
        $isProd           = filter_var(SystemSetting::get('shopee_is_production') ?? config('services.shopee.is_production', true), FILTER_VALIDATE_BOOLEAN);
        $defaultHost      = $isProd ? 'https://partner.shopeemobile.com' : 'https://partner.test-stable.shopeemobile.com';
        $this->host       = (string) (SystemSetting::get('shopee_host') ?? config('services.shopee.host', $defaultHost));
        $this->partnerId  = (int) (SystemSetting::get('shopee_partner_id') ?? config('services.shopee.partner_id', 0));
        $this->partnerKey = (string) (SystemSetting::get('shopee_partner_key') ?? config('services.shopee.partner_key', ''));
    }

    public function getChannel(): string
    {
        return MarketplaceAccount::CHANNEL_SHOPEE;
    }

    public function getName(): string
    {
        return 'Shopee';
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->partnerId) && ! empty($this->partnerKey);
    }

    public function generateSignature(string $path, int $timestamp, string $accessToken = '', string $shopId = ''): string
    {
        $baseStr = sprintf('%s%s%s%s%s', $this->partnerId, $path, $timestamp, $accessToken, $shopId);
        return hash_hmac('sha256', $baseStr, $this->partnerKey);
    }

    public function getAuthUrl(Business $business, string $redirectUri, string $state): string
    {
        $timestamp = time();
        $path      = '/api/v2/shop/auth_partner';
        $sign      = $this->generateSignature($path, $timestamp);

        $params = [
            'partner_id' => $this->partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign,
            'redirect'   => $redirectUri,
            'state'      => $state,
        ];

        return $this->host . $path . '?' . http_build_query($params);
    }

    public function handleAuthCallback(Business $business, array $params, string $redirectUri): array
    {
        $code      = (string) ($params['code'] ?? '');
        $shopId    = (string) ($params['shop_id'] ?? ($params['main_account_id'] ?? ''));
        $timestamp = time();
        $path      = '/api/v2/auth/token/get';
        $sign      = $this->generateSignature($path, $timestamp);

        if (empty($code)) {
            throw new \InvalidArgumentException('Authorization code Shopee tidak ditemukan dalam respon callback.');
        }

        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $sign);

        $response = Http::timeout(15)->post($url, [
            'code'       => $code,
            'partner_id' => $this->partnerId,
            'shop_id'    => (int) $shopId,
        ]);

        $data = $response->json();

        if (! $response->successful() || ! empty($data['error'])) {
            $msg = $data['message'] ?? ($data['error'] ?? 'Gagal menukarkan authorization code Shopee.');
            throw new \RuntimeException((string) $msg);
        }

        return [
            'shop_id'            => (string) ($data['shop_id'] ?? $shopId),
            'shop_name'          => 'Shopee Store #' . ($data['shop_id'] ?? $shopId),
            'access_token'       => (string) $data['access_token'],
            'refresh_token'      => (string) $data['refresh_token'],
            'expires_in'         => (int) ($data['expire_in'] ?? 14400),
            'refresh_expires_in' => 2592000, // 30 days
            'extra'              => [
                'merchant_id_list' => $data['merchant_id_list'] ?? [],
                'shop_id_list'     => $data['shop_id_list'] ?? [],
            ],
        ];
    }

    public function refreshToken(MarketplaceAccount $account): array
    {
        $timestamp = time();
        $path      = '/api/v2/auth/access_token/get';
        $sign      = $this->generateSignature($path, $timestamp);

        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $sign);

        $response = Http::timeout(15)->post($url, [
            'partner_id'    => $this->partnerId,
            'shop_id'       => (int) $account->shop_id,
            'refresh_token' => $account->refresh_token,
        ]);

        $data = $response->json();

        if (! $response->successful() || ! empty($data['error'])) {
            throw new \RuntimeException($data['message'] ?? 'Gagal memperbarui token Shopee.');
        }

        return [
            'access_token'  => (string) $data['access_token'],
            'refresh_token' => (string) $data['refresh_token'],
            'expires_in'    => (int) ($data['expire_in'] ?? 14400),
        ];
    }

    public function pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/product/update_stock';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        $stockData = [
            'item_id' => (int) $mapping->external_product_id,
            'stock_list' => [
                [
                    'seller_stock' => [
                        [
                            'stock' => max(0, $stock),
                        ],
                    ],
                ],
            ],
        ];

        if ($mapping->external_sku_id) {
            $stockData['stock_list'][0]['model_id'] = (int) $mapping->external_sku_id;
        }

        $response = Http::timeout(15)->post($url, $stockData);
        $data = $response->json();

        return $response->successful() && empty($data['error']);
    }

    public function pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/product/update_price';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        $priceData = [
            'item_id' => (int) $mapping->external_product_id,
            'price_list' => [
                [
                    'original_price' => $price,
                ],
            ],
        ];

        if ($mapping->external_sku_id) {
            $priceData['price_list'][0]['model_id'] = (int) $mapping->external_sku_id;
        }

        $response = Http::timeout(15)->post($url, $priceData);
        $data = $response->json();

        return $response->successful() && empty($data['error']);
    }

    public function getProducts(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/product/get_item_list';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&offset=%s&page_size=%s&item_status=NORMAL',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign,
            $params['offset'] ?? 0,
            $params['limit'] ?? 20
        );

        $response = Http::timeout(15)->get($url);
        $data = $response->json();

        $items = [];
        if ($response->successful() && ! empty($data['response']['item'])) {
            foreach ($data['response']['item'] as $item) {
                $items[] = [
                    'id'         => (string) $item['item_id'],
                    'name'       => $item['item_name'] ?? ('Shopee Item #' . $item['item_id']),
                    'sku'        => $item['item_sku'] ?? '',
                    'price'      => (float) ($item['price'] ?? 0),
                    'stock'      => (int) ($item['stock'] ?? 0),
                    'image_url'  => $item['image_url'] ?? null,
                    'variations' => [],
                ];
            }
        }

        return [
            'products' => $items,
            'has_next' => (bool) ($data['response']['has_next_page'] ?? false),
        ];
    }

    public function pullOrders(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/order/get_order_list';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $timeFrom = $params['time_from'] ?? (time() - 86400 * 7);
        $timeTo   = $params['time_to'] ?? time();

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&time_range_field=create_time&time_from=%s&time_to=%s&page_size=20',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign,
            $timeFrom,
            $timeTo
        );

        $response = Http::timeout(15)->get($url);
        $data = $response->json();

        $orders = [];
        if ($response->successful() && ! empty($data['response']['order_list'])) {
            foreach ($data['response']['order_list'] as $ord) {
                $orders[] = [
                    'order_id'     => (string) $ord['order_sn'],
                    'order_sn'     => (string) $ord['order_sn'],
                    'buyer_name'   => 'Shopee Buyer (' . substr((string) $ord['order_sn'], -4) . ')',
                    'total_amount' => (float) ($ord['total_amount'] ?? 0),
                    'status'       => (string) ($ord['order_status'] ?? 'UNPAID'),
                    'items'        => [],
                    'raw'          => $ord,
                ];
            }
        }

        return $orders;
    }

    public function handleWebhook(Request $request, string $rawBody, array $headers): array
    {
        $signHeader = $headers['x-shopee-sign'][0] 
            ?? ($headers['x-shopee-sign'] 
            ?? ($headers['authorization'][0] 
            ?? ($headers['authorization'] ?? '')));
        
        $payload = json_decode($rawBody, true) ?: [];

        // Shopee Webhook signature check: HMAC-SHA256 of url|rawBody signed with partnerKey
        $calcSign = ! empty($this->partnerKey) 
            ? hash_hmac('sha256', $request->fullUrl() . '|' . $rawBody, $this->partnerKey) 
            : '';
        $isValid  = ! empty($signHeader) && ! empty($this->partnerKey) && hash_equals($calcSign, (string) $signHeader);

        return [
            'event'    => (string) ($payload['code'] ?? 'order.status_update'),
            'shop_id'  => (string) ($payload['shop_id'] ?? ''),
            'payload'  => $payload,
            'is_valid' => $isValid,
        ];
    }
}
