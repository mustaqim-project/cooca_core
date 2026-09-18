<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Models\CommerceOrder;
use App\Models\PosOrder;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TripayService
{
    private string $merchantCode;
    private string $apiKey;
    private string $privateKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct(
        ?string $merchantCode = null,
        ?string $apiKey = null,
        ?string $privateKey = null,
        ?bool $isProduction = null
    ) {
        $this->merchantCode = $merchantCode ?? (string) (SystemSetting::get('tripay_merchant_code') ?: config('services.tripay.merchant_code', env('TRIPAY_MERCHANT_CODE', '')));
        $this->apiKey = $apiKey ?? (string) (SystemSetting::get('tripay_api_key') ?: config('services.tripay.api_key', env('TRIPAY_API_KEY', '')));
        $this->privateKey = $privateKey ?? (string) (SystemSetting::get('tripay_private_key') ?: config('services.tripay.private_key', env('TRIPAY_PRIVATE_KEY', '')));

        $settingProd = SystemSetting::get('tripay_is_production');
        $this->isProduction = $isProduction ?? ($settingProd !== null ? filter_var($settingProd, FILTER_VALIDATE_BOOLEAN) : (bool) config('services.tripay.is_production', env('TRIPAY_IS_PRODUCTION', false)));

        $sandboxUrl = rtrim((string) (SystemSetting::get('tripay_sandbox_url') ?: config('services.tripay.sandbox_url', 'https://tripay.co.id/api-sandbox/')), '/') . '/';
        $prodUrl = rtrim((string) (SystemSetting::get('tripay_prod_url') ?: config('services.tripay.prod_url', 'https://tripay.co.id/api/')), '/') . '/';

        $this->baseUrl = $this->isProduction ? $prodUrl : $sandboxUrl;
    }

    /**
     * Generate HMAC-SHA256 signature for creating a transaction.
     * Formula: hash_hmac('sha256', merchant_code + merchant_ref + amount, private_key)
     */
    public function generateSignature(string $merchantRef, int $amount): string
    {
        return hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);
    }

    /**
     * Verify incoming webhook callback signature from header X-Callback-Signature.
     * Formula: hash_hmac('sha256', raw_body, private_key)
     */
    public function verifyCallbackSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (empty($signatureHeader) || empty($this->privateKey)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->privateKey);

        return hash_equals($expected, trim($signatureHeader));
    }

    /**
     * Calculate QRIS gateway fee: Rp 750 + 0.7% from total amount.
     * In COOCA, this is charged to Merchant & Administrator, NOT to customer.
     */
    public function calculateQrisFee(float $amount): float
    {
        return (float) (750 + round($amount * 0.007));
    }

    /**
     * Get available active payment channels with metadata and icons.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPaymentChannels(): array
    {
        // Try live query to TriPay API if API key is present
        if (! empty($this->apiKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])->timeout(5)->get($this->baseUrl . 'payment/channel');

                if ($response->successful() && ($response->json('success') ?? false)) {
                    $data = (array) $response->json('data', []);
                    if (! empty($data)) {
                        return $data;
                    }
                }
            } catch (Throwable $e) {
                Log::warning('[TripayService] Failed to fetch channels from API: ' . $e->getMessage());
            }
        }

        // Standard Default Channels Fallback (Active on TriPay Indonesia)
        return [
            [
                'group' => 'E-Wallet / QRIS',
                'code' => 'QRIS',
                'name' => 'QRIS Dinamis',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 750, 'percent' => 0.7],
                'fee_customer' => ['flat' => 0, 'percent' => 0],
                'total_fee' => ['flat' => 750, 'percent' => 0.7],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/qris.png',
                'active' => true,
                'description' => 'Scan dengan GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, BRI, dll.',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 4000, 'percent' => 0],
                'total_fee' => ['flat' => 4000, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bca-va.png',
                'active' => true,
                'description' => 'Transfer via ATM, BCA Mobile, KlikBCA',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account (BRIVA)',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 3500, 'percent' => 0],
                'total_fee' => ['flat' => 3500, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bri-va.png',
                'active' => true,
                'description' => 'Transfer via BRImo, ATM BRI, Agen BRILink',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 3500, 'percent' => 0],
                'total_fee' => ['flat' => 3500, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/mandiri-va.png',
                'active' => true,
                'description' => 'Transfer via Livin by Mandiri, ATM Mandiri',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 3500, 'percent' => 0],
                'total_fee' => ['flat' => 3500, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bni-va.png',
                'active' => true,
                'description' => 'Transfer via BNI Mobile Banking, ATM BNI',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 3500, 'percent' => 0],
                'total_fee' => ['flat' => 3500, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/permata-va.png',
                'active' => true,
                'description' => 'Transfer via PermataMobile X, ATM Permata',
            ],
            [
                'group' => 'Virtual Account',
                'code' => 'BSIVA',
                'name' => 'BSI Virtual Account',
                'type' => 'direct',
                'fee_merchant' => ['flat' => 0, 'percent' => 0],
                'fee_customer' => ['flat' => 3500, 'percent' => 0],
                'total_fee' => ['flat' => 3500, 'percent' => 0],
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bsi-va.png',
                'active' => true,
                'description' => 'Transfer via BSI Mobile, ATM BSI',
            ],
        ];
    }

    /**
     * Request creation of a new TriPay transaction for a CommerceOrder.
     *
     * @return array{
     *     success: bool,
     *     reference?: string,
     *     merchant_ref?: string,
     *     payment_method?: string,
     *     pay_code?: string|null,
     *     qr_url?: string|null,
     *     qr_string?: string|null,
     *     checkout_url?: string|null,
     *     fee?: float,
     *     expired_time?: int,
     *     instructions?: array<mixed>,
     *     raw_response?: array<string, mixed>,
     *     message?: string
     * }
     */
    public function createTransaction(CommerceOrder $order, string $channelCode): array
    {
        $amount = (int) round($order->total_amount);
        $merchantRef = $order->order_number;
        $signature = $this->generateSignature($merchantRef, $amount);

        $orderItems = [];
        foreach ($order->items as $item) {
            $orderItems[] = [
                'name' => mb_substr((string) $item->product_name, 0, 100),
                'price' => (int) round((float) $item->unit_price),
                'quantity' => (int) max(1, round((float) $item->quantity)),
            ];
        }

        // If shipping cost exists, append as an item line
        if ((float) $order->shipping_cost > 0) {
            $orderItems[] = [
                'name' => 'Ongkos Kirim Kurir',
                'price' => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
            ];
        }

        // If biteship service fee exists, append as an item line
        if ((float) ($order->biteship_service_fee ?? 0) > 0) {
            $orderItems[] = [
                'name' => 'Biaya Layanan Pengiriman (Biteship)',
                'price' => (int) round((float) $order->biteship_service_fee),
                'quantity' => 1,
            ];
        }

        // If no items extracted, fallback to single order item
        if (empty($orderItems)) {
            $orderItems[] = [
                'name' => "Pesanan #{$order->order_number}",
                'price' => $amount,
                'quantity' => 1,
            ];
        }

        $customerEmail = ! empty($order->customer_email) ? $order->customer_email : 'customer@cooca.id';
        $customerPhone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if (str_starts_with($customerPhone, '0')) {
            $customerPhone = '62' . substr($customerPhone, 1);
        }

        // Set expiry (default 60 minutes or reserved_until)
        $expiredTime = $order->reserved_until ? $order->reserved_until->timestamp : (time() + 3600);

        $payload = [
            'method' => strtoupper(trim($channelCode)),
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $order->customer_name,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => $orderItems,
            'return_url' => url("/b/{$order->business?->slug}/order/{$order->tracking_token}"),
            'expired_time' => $expiredTime,
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(12)->post($this->baseUrl . 'transaction/create', $payload);

            $body = (array) $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                $data = (array) ($body['data'] ?? []);

                // Calculate fee
                $fee = strtoupper($channelCode) === 'QRIS'
                    ? $this->calculateQrisFee((float) $amount)
                    : (float) ($data['total_fee'] ?? 4000);

                return [
                    'success' => true,
                    'reference' => (string) ($data['reference'] ?? ''),
                    'merchant_ref' => (string) ($data['merchant_ref'] ?? $merchantRef),
                    'payment_method' => (string) ($data['payment_method'] ?? $channelCode),
                    'pay_code' => $data['pay_code'] ?? null,
                    'qr_url' => $data['qr_url'] ?? null,
                    'qr_string' => $data['qr_string'] ?? null,
                    'checkout_url' => $data['checkout_url'] ?? null,
                    'fee' => $fee,
                    'expired_time' => (int) ($data['expired_time'] ?? $expiredTime),
                    'instructions' => (array) ($data['instructions'] ?? []),
                    'raw_response' => $data,
                ];
            }

            $errMsg = $body['message'] ?? 'Gagal membuat transaksi TriPay (HTTP ' . $response->status() . ')';
            Log::error("[TripayService] Transaction create failed: {$errMsg}", [
                'payload' => $payload,
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => $errMsg,
            ];
        } catch (Throwable $e) {
            Log::error('[TripayService] Exception creating transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kendala koneksi ke server gateway pembayaran: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Request creation of a TriPay transaction for a PosOrder (Pay-at-Table QR Order).
     *
     * @return array{
     *     success: bool,
     *     reference?: string,
     *     merchant_ref?: string,
     *     payment_method?: string,
     *     pay_code?: string|null,
     *     qr_url?: string|null,
     *     qr_string?: string|null,
     *     checkout_url?: string|null,
     *     fee?: float,
     *     expired_time?: int,
     *     instructions?: array<mixed>,
     *     raw_response?: array<string, mixed>,
     *     message?: string
     * }
     */
    public function createPosOrderTransaction(PosOrder $order, string $channelCode = 'QRIS'): array
    {
        $amount = (int) round($order->total_amount);
        $merchantRef = $order->order_number;
        $signature = $this->generateSignature($merchantRef, $amount);

        $orderItems = [];
        foreach ($order->items as $item) {
            $orderItems[] = [
                'name' => mb_substr((string) $item->product_name, 0, 100),
                'price' => (int) round((float) $item->unit_price),
                'quantity' => (int) max(1, round((float) $item->quantity)),
            ];
        }

        if (empty($orderItems)) {
            $orderItems[] = [
                'name' => "Pesanan Meja #{$order->order_number}",
                'price' => $amount,
                'quantity' => 1,
            ];
        }

        $customerName = $order->customer_name_guest ?: ($order->customer?->name ?: 'Tamu Meja ' . ($order->table_or_reference ?: 'Resto'));
        $customerPhone = preg_replace('/[^0-9]/', '', (string) ($order->customer_phone_guest ?: ($order->customer?->phone ?: '081234567890')));
        if (str_starts_with($customerPhone, '0')) {
            $customerPhone = '62' . substr($customerPhone, 1);
        }

        $expiredTime = time() + 1800; // 30 minutes for dine-in tables

        $payload = [
            'method' => strtoupper(trim($channelCode)),
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $customerName,
            'customer_email' => 'guest@cooca.id',
            'customer_phone' => $customerPhone,
            'order_items' => $orderItems,
            'return_url' => url('/pos/table/' . ($order->posTable?->qr_token ?? '')),
            'expired_time' => $expiredTime,
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(12)->post($this->baseUrl . 'transaction/create', $payload);

            $body = (array) $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                $data = (array) ($body['data'] ?? []);

                $fee = strtoupper($channelCode) === 'QRIS'
                    ? $this->calculateQrisFee((float) $amount)
                    : (float) ($data['total_fee'] ?? 4000);

                return [
                    'success' => true,
                    'reference' => (string) ($data['reference'] ?? ''),
                    'merchant_ref' => (string) ($data['merchant_ref'] ?? $merchantRef),
                    'payment_method' => (string) ($data['payment_method'] ?? $channelCode),
                    'pay_code' => $data['pay_code'] ?? null,
                    'qr_url' => $data['qr_url'] ?? null,
                    'qr_string' => $data['qr_string'] ?? null,
                    'checkout_url' => $data['checkout_url'] ?? null,
                    'fee' => $fee,
                    'expired_time' => (int) ($data['expired_time'] ?? $expiredTime),
                    'instructions' => (array) ($data['instructions'] ?? []),
                    'raw_response' => $data,
                ];
            }

            $errMsg = $body['message'] ?? 'Gagal membuat QRIS pembayaran meja (HTTP ' . $response->status() . ')';
            Log::error("[TripayService] POS table transaction failed: {$errMsg}", ['response' => $body]);

            return ['success' => false, 'message' => $errMsg];
        } catch (Throwable $e) {
            Log::error('[TripayService] POS exception: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Kendala gateway pembayaran: ' . $e->getMessage()];
        }
    }

    /**
     * Request creation of a TriPay transaction for SaaS Subscription / Token Top-Up.
     *
     * @return array<string, mixed>
     */
    public function createSubscriptionTransaction(SubscriptionPayment $payment, string $channelCode = 'QRIS'): array
    {
        $amount = (int) round($payment->total_payable);
        $merchantRef = $payment->order_number;
        $signature = $this->generateSignature($merchantRef, $amount);

        $customerName = $payment->user?->name ?: 'Tenant Cooca';
        $customerEmail = $payment->user?->email ?: 'billing@cooca.id';
        $customerPhone = preg_replace('/[^0-9]/', '', (string) ($payment->user?->phone ?: '081234567890'));
        if (str_starts_with($customerPhone, '0')) {
            $customerPhone = '62' . substr($customerPhone, 1);
        }

        $packageName = $payment->package_name ?: ($payment->payment_type === 'ai_token' ? 'Top-up AI Token' : 'Langganan SaaS Cooca');

        $payload = [
            'method' => strtoupper(trim($channelCode)),
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => [
                [
                    'name' => $packageName,
                    'price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'return_url' => url('/app/billing/payment/' . $payment->id),
            'expired_time' => time() + 86400, // 24 hours
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(12)->post($this->baseUrl . 'transaction/create', $payload);

            $body = (array) $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                $data = (array) ($body['data'] ?? []);

                return [
                    'success' => true,
                    'reference' => (string) ($data['reference'] ?? ''),
                    'merchant_ref' => (string) ($data['merchant_ref'] ?? $merchantRef),
                    'payment_method' => (string) ($data['payment_method'] ?? $channelCode),
                    'pay_code' => $data['pay_code'] ?? null,
                    'qr_url' => $data['qr_url'] ?? null,
                    'qr_string' => $data['qr_string'] ?? null,
                    'checkout_url' => $data['checkout_url'] ?? null,
                    'fee' => (float) ($data['total_fee'] ?? 0),
                    'expired_time' => (int) ($data['expired_time'] ?? (time() + 86400)),
                    'instructions' => (array) ($data['instructions'] ?? []),
                    'raw_response' => $data,
                ];
            }

            return ['success' => false, 'message' => $body['message'] ?? 'Gagal membuat tagihan'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check transaction status directly from TriPay.
     *
     * @return array<string, mixed>|null
     */
    public function getTransactionDetail(string $reference): ?array
    {
        if (empty($this->apiKey) || empty($reference)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(8)->get($this->baseUrl . 'transaction/detail', [
                'reference' => $reference,
            ]);

            if ($response->successful() && ($response->json('success') ?? false)) {
                return (array) $response->json('data');
            }
        } catch (Throwable $e) {
            Log::warning("[TripayService] Failed to check status for {$reference}: " . $e->getMessage());
        }

        return null;
    }
}
