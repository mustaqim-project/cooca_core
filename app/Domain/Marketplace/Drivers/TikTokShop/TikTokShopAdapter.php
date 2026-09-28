<?php

declare(strict_types=1);

namespace App\Domain\Marketplace\Drivers\TikTokShop;

use App\Domain\Marketplace\Contracts\MarketplaceAdapterInterface;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TikTokShopAdapter implements MarketplaceAdapterInterface
{
    protected string $host;
    protected string $authHost;
    protected string $appKey;
    protected string $appSecret;
    protected string $serviceId;

    public function __construct()
    {
        $this->host      = (string) (SystemSetting::get('tiktok_shop_host') ?? config('services.tiktok_shop.host', 'https://open-api.tiktokglobalshop.com'));
        $this->authHost  = (string) (SystemSetting::get('tiktok_shop_auth_host') ?? config('services.tiktok_shop.auth_host', 'https://services.tiktokshop.com/open/authorize'));
        $this->appKey    = (string) (SystemSetting::get('tiktok_shop_app_key') ?? config('services.tiktok_shop.app_key', ''));
        $this->appSecret = (string) (SystemSetting::get('tiktok_shop_app_secret') ?? config('services.tiktok_shop.app_secret', ''));
        $this->serviceId = (string) (SystemSetting::get('tiktok_shop_service_id') ?? config('services.tiktok_shop.service_id', ''));
    }

    public function getChannel(): string
    {
        return MarketplaceAccount::CHANNEL_TIKTOK;
    }

    public function getName(): string
    {
        return 'TikTok Shop + Tokopedia';
    }

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->appKey) && ! empty($this->appSecret);
    }

    public function generateSignature(string $path, array $params): string
    {
        ksort($params);
        $signStr = $this->appSecret . $path;
        foreach ($params as $k => $v) {
            if ($k !== 'sign' && $k !== 'access_token') {
                $signStr .= $k . $v;
            }
        }
        $signStr .= $this->appSecret;

        return hash_hmac('sha256', $signStr, $this->appSecret);
    }

    public function getAuthUrl(Business $business, string $redirectUri, string $state): string
    {
        $configuredUri = SystemSetting::get('tiktok_tokopedia_redirect_uri')
            ?? SystemSetting::get('tiktok_shop_redirect_uri')
            ?? config('services.tiktok_shop.redirect_uri')
            ?? $redirectUri;

        $targetRedirect = ! empty($configuredUri) ? $configuredUri : $redirectUri;

        $params = [
            'service_id'   => $this->serviceId ?: $this->appKey,
            'state'        => $state,
            'redirect_uri' => $targetRedirect,
        ];

        return $this->authHost . '?' . http_build_query($params);
    }

    public function handleAuthCallback(Business $business, array $params, string $redirectUri): array
    {
        $authCode = (string) ($params['code'] ?? ($params['auth_code'] ?? ''));

        if (empty($authCode)) {
            throw new \InvalidArgumentException('Authorization code TikTok Shop tidak ditemukan dalam callback.');
        }

        $url = 'https://auth.tiktok-shops.com/api/v2/token/get';

        $response = Http::timeout(15)->get($url, [
            'app_key'    => $this->appKey,
            'app_secret' => $this->appSecret,
            'auth_code'  => $authCode,
            'grant_type' => 'authorized_code',
        ]);

        $data = $response->json();

        if (! $response->successful() || ($data['code'] ?? 0) !== 0) {
            $msg = $data['message'] ?? 'Gagal menukarkan token TikTok Shop.';
            throw new \RuntimeException((string) $msg);
        }

        $tokenData = $data['data'] ?? [];
        $sellerName = $tokenData['seller_name'] ?? ('TikTok Shop #' . ($tokenData['open_id'] ?? 'Store'));

        return [
            'shop_id'            => (string) ($tokenData['open_id'] ?? ($tokenData['seller_base_region'] ?? 'ID_SHOP')),
            'shop_name'          => (string) $sellerName,
            'access_token'       => (string) ($tokenData['access_token'] ?? ''),
            'refresh_token'      => (string) ($tokenData['refresh_token'] ?? ''),
            'expires_in'         => (int) ($tokenData['access_token_expire_in'] ?? 86400),
            'refresh_expires_in' => (int) ($tokenData['refresh_token_expire_in'] ?? 2592000),
            'extra'              => [
                'seller_base_region' => $tokenData['seller_base_region'] ?? 'ID',
                'user_type'          => $tokenData['user_type'] ?? 0,
            ],
        ];
    }

    public function refreshToken(MarketplaceAccount $account): array
    {
        $url = 'https://auth.tiktok-shops.com/api/v2/token/refresh';

        $response = Http::timeout(15)->get($url, [
            'app_key'       => $this->appKey,
            'app_secret'    => $this->appSecret,
            'refresh_token' => $account->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        $data = $response->json();

        if (! $response->successful() || ($data['code'] ?? 0) !== 0) {
            throw new \RuntimeException($data['message'] ?? 'Gagal memperbarui token TikTok Shop.');
        }

        $tokenData = $data['data'] ?? [];

        return [
            'access_token'  => (string) ($tokenData['access_token'] ?? ''),
            'refresh_token' => (string) ($tokenData['refresh_token'] ?? ''),
            'expires_in'    => (int) ($tokenData['access_token_expire_in'] ?? 86400),
        ];
    }

    public function pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool
    {
        $path = sprintf('/product/202309/products/%s/stocks/update', $mapping->external_product_id);
        $timestamp = time();

        $queryParams = [
            'app_key'   => $this->appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $account->settings['shop_cipher'] ?? $account->shop_id,
        ];
        $sign = $this->generateSignature($path, $queryParams);
        $queryParams['sign'] = $sign;

        $body = [
            'skus' => [
                [
                    'id' => $mapping->external_sku_id ?: $mapping->external_product_id,
                    'stock_infos' => [
                        [
                            'available_stock' => max(0, $stock),
                        ],
                    ],
                ],
            ],
        ];

        $url = $this->host . $path . '?' . http_build_query($queryParams);

        $response = Http::withHeaders([
            'x-tts-access-token' => (string) $account->access_token,
            'Content-Type'       => 'application/json',
        ])->timeout(15)->post($url, $body);

        $data = $response->json();

        return $response->successful() && ($data['code'] ?? 0) === 0;
    }

    public function pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool
    {
        $path = sprintf('/product/202309/products/%s/prices/update', $mapping->external_product_id);
        $timestamp = time();

        $queryParams = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $account->settings['shop_cipher'] ?? $account->shop_id,
        ];
        $sign = $this->generateSignature($path, $queryParams);
        $queryParams['sign'] = $sign;

        $body = [
            'skus' => [
                [
                    'id'    => $mapping->external_sku_id ?: $mapping->external_product_id,
                    'price' => [
                        'currency'          => 'IDR',
                        'amount'            => (string) round($price),
                        'original_price'    => (string) round($price),
                    ],
                ],
            ],
        ];

        $url = $this->host . $path . '?' . http_build_query($queryParams);

        $response = Http::withHeaders([
            'x-tts-access-token' => (string) $account->access_token,
            'Content-Type'       => 'application/json',
        ])->timeout(15)->post($url, $body);

        $data = $response->json();

        return $response->successful() && ($data['code'] ?? 0) === 0;
    }

    public function getProducts(MarketplaceAccount $account, array $params = []): array
    {
        $path = '/product/202309/products/search';
        $timestamp = time();

        $queryParams = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'page_size'   => (string) ($params['limit'] ?? 20),
            'shop_cipher' => $account->settings['shop_cipher'] ?? $account->shop_id,
        ];
        $sign = $this->generateSignature($path, $queryParams);
        $queryParams['sign'] = $sign;

        $url = $this->host . $path . '?' . http_build_query($queryParams);

        $response = Http::withHeaders([
            'x-tts-access-token' => (string) $account->access_token,
        ])->timeout(15)->post($url, [
            'status' => 'ACTIVATE',
        ]);

        $data = $response->json();
        $items = [];

        if ($response->successful() && ! empty($data['data']['products'])) {
            foreach ($data['data']['products'] as $prod) {
                $sku = $prod['skus'][0] ?? [];
                $items[] = [
                    'id'         => (string) $prod['id'],
                    'name'       => (string) ($prod['title'] ?? 'TikTok Product #' . $prod['id']),
                    'sku'        => (string) ($sku['seller_sku'] ?? ''),
                    'price'      => (float) ($sku['price']['original_price'] ?? ($sku['price']['tax_exclusive_price'] ?? 0)),
                    'stock'      => (int) ($sku['stock_infos'][0]['available_stock'] ?? 0),
                    'image_url'  => $prod['main_images'][0]['urls'][0] ?? null,
                    'variations' => [],
                ];
            }
        }

        return [
            'products' => $items,
            'has_next' => ! empty($data['data']['next_page_token']),
        ];
    }

    public function pullOrders(MarketplaceAccount $account, array $params = []): array
    {
        $path = '/order/202309/orders/search';
        $timestamp = time();

        $queryParams = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'page_size'   => '20',
            'shop_cipher' => $account->settings['shop_cipher'] ?? $account->shop_id,
        ];
        $sign = $this->generateSignature($path, $queryParams);
        $queryParams['sign'] = $sign;

        $url = $this->host . $path . '?' . http_build_query($queryParams);

        $response = Http::withHeaders([
            'x-tts-access-token' => (string) $account->access_token,
        ])->timeout(15)->post($url, [
            'order_status' => 'ALL',
        ]);

        $data = $response->json();
        $orders = [];

        if ($response->successful() && ! empty($data['data']['orders'])) {
            foreach ($data['data']['orders'] as $ord) {
                $orders[] = [
                    'order_id'     => (string) $ord['id'],
                    'order_sn'     => (string) $ord['id'],
                    'buyer_name'   => $ord['recipient_address']['name'] ?? 'TikTok Buyer',
                    'total_amount' => (float) ($ord['payment']['total_amount'] ?? 0),
                    'status'       => (string) ($ord['status'] ?? 'UNPAID'),
                    'items'        => $ord['line_items'] ?? [],
                    'raw'          => $ord,
                ];
            }
        }

        return $orders;
    }

    public function handleWebhook(Request $request, string $rawBody, array $headers): array
    {
        $signature = $headers['authorization'][0] 
            ?? ($headers['authorization'] 
            ?? ($headers['x-tts-signature'][0] 
            ?? ($headers['x-tts-signature'] ?? '')));
        $payload   = json_decode($rawBody, true) ?: [];

        // Verify webhook signature with app_secret using HMAC-SHA256
        $calcSign = ! empty($this->appSecret) ? hash_hmac('sha256', $rawBody, $this->appSecret) : '';
        $isValid  = ! empty($signature) && ! empty($this->appSecret) && hash_equals($calcSign, (string) $signature);

        return [
            'event'    => (string) ($payload['type'] ?? ($payload['event'] ?? 'order_status_change')),
            'shop_id'  => (string) ($payload['shop_id'] ?? ($payload['open_id'] ?? '')),
            'payload'  => $payload,
            'is_valid' => $isValid,
        ];
    }
}
