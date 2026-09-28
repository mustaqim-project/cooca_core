<?php

declare(strict_types=1);

namespace App\Domain\Marketplace\Drivers\Tokopedia;

use App\Domain\Marketplace\Contracts\MarketplaceAdapterInterface;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TokopediaAdapter implements MarketplaceAdapterInterface
{
    protected string $host;
    protected string $fsId;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->host         = (string) (SystemSetting::get('tokopedia_host') ?? config('services.tokopedia.host', 'https://fs.tokopedia.net'));
        $this->fsId         = (string) (SystemSetting::get('tokopedia_fs_id') ?? config('services.tokopedia.fs_id', ''));
        $this->clientId     = (string) (SystemSetting::get('tokopedia_client_id') ?? config('services.tokopedia.client_id', ''));
        $this->clientSecret = (string) (SystemSetting::get('tokopedia_client_secret') ?? config('services.tokopedia.client_secret', ''));
    }

    public function getChannel(): string
    {
        return MarketplaceAccount::CHANNEL_TOKOPEDIA;
    }

    public function getName(): string
    {
        return 'Tokopedia';
    }

    public function getAuthUrl(Business $business, string $redirectUri, string $state): string
    {
        // For Tokopedia Open API / Seller OAuth
        $params = [
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
        ];

        return 'https://accounts.tokopedia.com/oauth/authorize?' . http_build_query($params);
    }

    public function handleAuthCallback(Business $business, array $params, string $redirectUri): array
    {
        $code = (string) ($params['code'] ?? '');

        if (empty($code)) {
            throw new \InvalidArgumentException('Authorization code Tokopedia tidak ditemukan.');
        }

        $basicAuth = base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $basicAuth,
        ])->asForm()->timeout(15)->post('https://accounts.tokopedia.com/token?grant_type=authorization_code', [
            'code'         => $code,
            'redirect_uri' => $redirectUri,
        ]);

        $data = $response->json();

        if (! $response->successful() || empty($data['access_token'])) {
            throw new \RuntimeException($data['error_description'] ?? 'Gagal otentikasi Tokopedia OAuth.');
        }

        return [
            'shop_id'            => (string) ($data['shop_id'] ?? ($data['user_id'] ?? 'TP_STORE')),
            'shop_name'          => 'Tokopedia Official Store',
            'access_token'       => (string) $data['access_token'],
            'refresh_token'      => (string) ($data['refresh_token'] ?? $data['access_token']),
            'expires_in'         => (int) ($data['expires_in'] ?? 86400),
            'refresh_expires_in' => 2592000,
            'extra'              => [
                'token_type' => $data['token_type'] ?? 'Bearer',
            ],
        ];
    }

    public function refreshToken(MarketplaceAccount $account): array
    {
        $basicAuth = base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $basicAuth,
        ])->asForm()->timeout(15)->post('https://accounts.tokopedia.com/token?grant_type=client_credentials');

        $data = $response->json();

        if (! $response->successful() || empty($data['access_token'])) {
            throw new \RuntimeException($data['error_description'] ?? 'Gagal memperbarui token Tokopedia.');
        }

        return [
            'access_token'  => (string) $data['access_token'],
            'refresh_token' => (string) ($data['refresh_token'] ?? $data['access_token']),
            'expires_in'    => (int) ($data['expires_in'] ?? 86400),
        ];
    }

    public function pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool
    {
        $url = $this->host . '/inventory/v1/fs/' . $this->fsId . '/stock/update';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type'  => 'application/json',
        ])->timeout(15)->post($url, [
            'shop_id' => (int) $account->shop_id,
            'products' => [
                [
                    'product_id' => (int) $mapping->external_product_id,
                    'stock'      => max(0, $stock),
                ],
            ],
        ]);

        $data = $response->json();

        return $response->successful() && empty($data['header']['error_code']);
    }

    public function pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool
    {
        $url = $this->host . '/inventory/v1/fs/' . $this->fsId . '/price/update';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type'  => 'application/json',
        ])->timeout(15)->post($url, [
            'shop_id' => (int) $account->shop_id,
            'products' => [
                [
                    'product_id' => (int) $mapping->external_product_id,
                    'price'      => round($price),
                ],
            ],
        ]);

        $data = $response->json();

        return $response->successful() && empty($data['header']['error_code']);
    }

    public function getProducts(MarketplaceAccount $account, array $params = []): array
    {
        $url = $this->host . '/inventory/v1/fs/' . $this->fsId . '/product/info';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->timeout(15)->get($url, [
            'shop_id'  => $account->shop_id,
            'page'     => $params['page'] ?? 1,
            'per_page' => $params['limit'] ?? 20,
        ]);

        $data = $response->json();
        $items = [];

        if ($response->successful() && ! empty($data['data'])) {
            foreach ($data['data'] as $prod) {
                $items[] = [
                    'id'         => (string) $prod['basic']['product_id'],
                    'name'       => (string) $prod['basic']['name'],
                    'sku'        => (string) ($prod['other']['sku'] ?? ''),
                    'price'      => (float) ($prod['price']['value'] ?? 0),
                    'stock'      => (int) ($prod['stock']['value'] ?? 0),
                    'image_url'  => $prod['pictures'][0]['url_thumbnail'] ?? null,
                    'variations' => [],
                ];
            }
        }

        return [
            'products' => $items,
            'has_next' => ! empty($data['data']),
        ];
    }

    public function pullOrders(MarketplaceAccount $account, array $params = []): array
    {
        $url = $this->host . '/v2/order/list';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->timeout(15)->get($url, [
            'fs_id'     => $this->fsId,
            'shop_id'   => $account->shop_id,
            'from_date' => $params['from_date'] ?? (time() - 86400 * 7),
            'to_date'   => $params['to_date'] ?? time(),
            'page'      => 1,
            'per_page'  => 20,
        ]);

        $data = $response->json();
        $orders = [];

        if ($response->successful() && ! empty($data['data'])) {
            foreach ($data['data'] as $ord) {
                $orders[] = [
                    'order_id'     => (string) $ord['order_id'],
                    'order_sn'     => (string) ($ord['invoice_ref_num'] ?? $ord['order_id']),
                    'buyer_name'   => $ord['buyer_info']['buyer_fullname'] ?? 'Tokopedia Buyer',
                    'total_amount' => (float) ($ord['item_price'] ?? 0),
                    'status'       => (string) ($ord['order_status'] ?? 'UNPAID'),
                    'items'        => $ord['order_detail'] ?? [],
                    'raw'          => $ord,
                ];
            }
        }

        return $orders;
    }

    public function handleWebhook(Request $request, string $rawBody, array $headers): array
    {
        $token = $headers['x-tkpd-token'][0] 
            ?? ($headers['x-tkpd-token'] 
            ?? ($headers['x-tokopedia-signature'][0] 
            ?? ($headers['x-tokopedia-signature'] 
            ?? ($headers['authorization'][0] 
            ?? ($headers['authorization'] ?? '')))));

        if (str_starts_with((string) $token, 'Bearer ')) {
            $token = substr((string) $token, 7);
        }

        $payload = json_decode($rawBody, true) ?: [];
        $secret  = (string) (SystemSetting::get('tokopedia_webhook_secret') ?? config('services.tokopedia.webhook_secret', $this->clientSecret));

        // Tokopedia validates either via shared webhook secret or HMAC-SHA256 signature
        $calcSign = ! empty($secret) ? hash_hmac('sha256', $rawBody, $secret) : '';
        $isValid  = ! empty($token) && ! empty($secret) && (hash_equals($secret, (string) $token) || hash_equals($calcSign, (string) $token));

        return [
            'event'    => (string) ($payload['msg_type'] ?? 'order_notification'),
            'shop_id'  => (string) ($payload['shop_id'] ?? ''),
            'payload'  => $payload,
            'is_valid' => $isValid,
        ];
    }
}
