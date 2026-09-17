<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Domain\Payment\TripayService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TripayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private CommercePaymentMethod $paymentMethod;
    private GlobalCustomer $globalCustomer;

    private string $testMerchantCode = 'T38171';
    private string $testApiKey = 'DEV-jeLy0ZJGZZHW5bYFw9IUbUCzfbZazFBcY3RVOZVz';
    private string $testPrivateKey = '8ScV0-22135-RCMuz-DZzhe-h0Dul';

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        // Configure TriPay sandbox credentials
        Config::set('services.tripay.merchant_code', $this->testMerchantCode);
        Config::set('services.tripay.api_key', $this->testApiKey);
        Config::set('services.tripay.private_key', $this->testPrivateKey);
        Config::set('services.tripay.is_production', false);

        $this->merchantUser = User::create([
            'name' => 'Owner TriPay Store',
            'email' => 'owner@tripaystore.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'TriPay UMKM Store',
            'slug' => 'tripay-umkm',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->merchantUser->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Toko Utama',
            'is_primary' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Kopi Robusta 250g',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 100000,
            'base_cost' => 45000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'TriPay UMKM Store',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'min_order_amount' => 0,
            'order_auto_cancel_minutes' => 60,
        ]);

        $this->globalCustomer = GlobalCustomer::create([
            'google_id' => 'google-user-tripay',
            'name' => 'Pelanggan TriPay',
            'email' => 'customer@tripaytest.com',
            'phone' => '081299998888',
            'phone_verified_at' => now(),
        ]);
    }

    public function test_tripay_service_generates_correct_signature(): void
    {
        $service = new TripayService(
            merchantCode: $this->testMerchantCode,
            apiKey: $this->testApiKey,
            privateKey: $this->testPrivateKey
        );

        $merchantRef = 'ORD-20260917-001';
        $amount = 100000;

        $expectedSignature = hash_hmac('sha256', $this->testMerchantCode . $merchantRef . $amount, $this->testPrivateKey);
        $generatedSignature = $service->generateSignature($merchantRef, $amount);

        $this->assertSame($expectedSignature, $generatedSignature);
    }

    public function test_tripay_service_calculates_qris_fee_correctly(): void
    {
        $service = new TripayService();

        // Formula: 750 + round(amount * 0.007)
        // 100,000 * 0.007 = 700 => 750 + 700 = 1450
        $fee100k = $service->calculateQrisFee(100000.0);
        $this->assertEquals(1450.0, $fee100k);

        // 50,000 * 0.007 = 350 => 750 + 350 = 1100
        $fee50k = $service->calculateQrisFee(50000.0);
        $this->assertEquals(1100.0, $fee50k);
    }

    public function test_tripay_callback_rejects_invalid_signature(): void
    {
        $payload = [
            'merchant_ref' => 'ORD-INVALID-SIG',
            'reference' => 'DEV-T3817100001',
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/v1/payments/tripay/callback', $payload, [
            'X-Callback-Signature' => 'invalid_signature_hash_123',
            'X-Callback-Event' => 'payment_status',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_tripay_callback_handles_successful_qris_payment_and_commits_stock(): void
    {
        $stockService = new StockService();

        // 1. Setup Order
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-TRIPAY-001',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'fulfillment_type' => 'pickup',
            'subtotal' => 100000,
            'total_amount' => 100000,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T38171REF001',
            'gateway_fee' => 1450,
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => $this->globalCustomer->name,
            'customer_phone' => '6281299998888',
            'customer_email' => $this->globalCustomer->email,
            'reserved_until' => now()->addMinutes(60),
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);

        // Reserve stock
        $stockService->reserveProductStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $this->product,
            productQuantity: 2.0
        );

        $initialStock = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(2.0, (float) $initialStock->reserved_quantity);
        $this->assertEquals(20.0, (float) $initialStock->quantity);

        // 2. Prepare Valid Callback Payload & Signature
        $callbackData = [
            'reference' => 'DEV-T38171REF001',
            'merchant_ref' => 'ORD-TRIPAY-001',
            'payment_method' => 'QRIS',
            'payment_method_code' => 'QRIS',
            'total_amount' => 100000,
            'fee_merchant' => 1450,
            'fee_customer' => 0,
            'total_fee' => 1450,
            'amount_received' => 98550,
            'is_closed_payment' => 1,
            'status' => 'PAID',
            'paid_at' => time(),
        ];

        $rawBody = json_encode($callbackData);
        $validSignature = hash_hmac('sha256', $rawBody, $this->testPrivateKey);

        // 3. Send Webhook Request
        $response = $this->call(
            'POST',
            '/api/v1/payments/tripay/callback',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => $validSignature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 4. Assert Order State
        $order->refresh();
        $this->assertSame(CommerceOrder::STATUS_PAID, $order->status);
        $this->assertSame(CommerceOrder::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertEquals(1450.0, (float) $order->gateway_fee);
        $this->assertEquals(98550.0, $order->net_revenue);

        // 5. Assert Physical Stock Committed
        $finalStock = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(0.0, (float) $finalStock->reserved_quantity);
        $this->assertEquals(18.0, (float) $finalStock->quantity); // 20 - 2 committed
    }

    public function test_tripay_callback_is_idempotent(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-TRIPAY-IDEMP',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'fulfillment_type' => 'pickup',
            'subtotal' => 100000,
            'total_amount' => 100000,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'paid_at' => now(),
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => $this->globalCustomer->name,
            'customer_phone' => '6281299998888',
        ]);

        $callbackData = [
            'reference' => 'DEV-T38171REF-IDEMP',
            'merchant_ref' => 'ORD-TRIPAY-IDEMP',
            'status' => 'PAID',
        ];
        $rawBody = json_encode($callbackData);
        $validSignature = hash_hmac('sha256', $rawBody, $this->testPrivateKey);

        $response = $this->call(
            'POST',
            '/api/v1/payments/tripay/callback',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => $validSignature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Order was already marked as paid.');
    }

    public function test_tripay_callback_handles_expired_status_and_releases_stock(): void
    {
        $stockService = new StockService();

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-TRIPAY-EXP',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'fulfillment_type' => 'pickup',
            'subtotal' => 100000,
            'total_amount' => 100000,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'BCAVA',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => $this->globalCustomer->name,
            'customer_phone' => '6281299998888',
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 100000,
            'quantity' => 3,
            'subtotal' => 300000,
        ]);

        $stockService->reserveProductStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $this->product,
            productQuantity: 3.0
        );

        $callbackData = [
            'reference' => 'DEV-T38171REF-EXP',
            'merchant_ref' => 'ORD-TRIPAY-EXP',
            'status' => 'EXPIRED',
        ];
        $rawBody = json_encode($callbackData);
        $validSignature = hash_hmac('sha256', $rawBody, $this->testPrivateKey);

        $response = $this->call(
            'POST',
            '/api/v1/payments/tripay/callback',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => $validSignature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            $rawBody
        );

        $response->assertStatus(200);

        $order->refresh();
        $this->assertSame(CommerceOrder::STATUS_EXPIRED, $order->status);
        $this->assertSame(CommerceOrder::PAYMENT_FAILED, $order->payment_status);

        // Stock reserved released back
        $finalStock = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(0.0, (float) $finalStock->reserved_quantity);
        $this->assertEquals(20.0, (float) $finalStock->quantity); // Total physical stock remains intact
    }

    public function test_public_order_tracking_status_polling_endpoint(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-POLL-001',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'fulfillment_type' => 'pickup',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'paid_at' => now(),
            'tracking_token' => 'poll-token-12345',
            'customer_name' => $this->globalCustomer->name,
            'customer_phone' => '6281299998888',
        ]);

        $response = $this->getJson("/b/{$this->business->slug}/order/{$order->tracking_token}/status");

        $response->assertStatus(200);
        $response->assertJson([
            'is_paid' => true,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'payment_channel' => 'QRIS',
        ]);
    }

    public function test_storefront_checkout_with_tripay_qris(): void
    {
        // Mock TriPay create transaction API
        Http::fake([
            '*transaction/create*' => Http::response([
                'success' => true,
                'message' => 'Transaction created',
                'data' => [
                    'reference' => 'DEV-T3817100099',
                    'merchant_ref' => 'ORD-MOCK-001',
                    'payment_method' => 'QRIS',
                    'pay_code' => null,
                    'qr_url' => 'https://tripay.co.id/qr/sample-qris.png',
                    'qr_string' => '00020101021126580014ID.LINKAJA.WWW...',
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-T3817100099',
                    'total_fee' => 1450,
                    'expired_time' => time() + 3600,
                ],
            ], 200),
        ]);

        $payload = [
            'customer_name' => 'Budi Pembeli',
            'customer_phone' => '081234567890',
            'customer_email' => 'budi@example.com',
            'fulfillment_type' => 'pickup',
            'payment_gateway' => 'tripay',
            'payment_channel' => 'QRIS',
            'payment_method_id' => null,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('order.payment_gateway', 'tripay');
        $response->assertJsonPath('order.payment_channel', 'QRIS');

        $createdOrder = CommerceOrder::where('business_id', $this->business->id)->latest()->first();
        $this->assertNotNull($createdOrder);
        $this->assertSame(CommerceOrder::GATEWAY_TRIPAY, $createdOrder->payment_gateway);
        $this->assertSame('QRIS', $createdOrder->payment_channel);
        $this->assertSame('DEV-T3817100099', $createdOrder->gateway_reference);
        $this->assertSame('https://tripay.co.id/qr/sample-qris.png', $createdOrder->gateway_qr_url);
    }
}
