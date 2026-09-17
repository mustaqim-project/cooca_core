<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\PosTableSession;
use App\Models\Product;
use App\Models\SubscriptionPayment;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosQrOrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private PosShift $shift;
    private PosTable $table;
    private Product $product;
    private CashAccount $cashAccount;

    private string $testMerchantCode = 'T38171';
    private string $testApiKey = 'DEV-jeLy0ZJGZZHW5bYFw9IUbUCzfbZazFBcY3RVOZVz';
    private string $testPrivateKey = '8ScV0-22135-RCMuz-DZzhe-h0Dul';

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        Config::set('services.tripay.merchant_code', $this->testMerchantCode);
        Config::set('services.tripay.api_key', $this->testApiKey);
        Config::set('services.tripay.private_key', $this->testPrivateKey);
        Config::set('services.tripay.is_production', false);

        $this->merchantUser = User::create([
            'name' => 'Resto Owner',
            'email' => 'resto@cooca.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Cooca Bistro',
            'slug' => 'cooca-bistro',
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
            'name' => 'Resto Cabang Utama',
            'is_primary' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->merchantUser->id,
            'shift_number' => 'SHIFT-001',
            'opening_cash' => 500000,
            'status' => PosShift::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'M-07',
            'name' => 'Meja 7',
            'capacity' => 4,
            'status' => PosTable::STATUS_OCCUPIED,
            'qr_token' => 'qr-token-meja-07',
            'is_active' => true,
        ]);

        $portion = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $portion->id,
            'name' => 'Nasi Goreng Spesial Wagyu',
            'code' => 'NASGOR-01',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 50000,
            'base_cost' => 22000,
            'is_active' => true,
        ]);

        // Add initial stock
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'reserved_quantity' => 0,
        ]);

        $this->cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Kas Operasional Resto',
            'type' => CashAccount::TYPE_CASH,
            'current_balance' => 1000000,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_order_pay_at_table_via_tripay_qris(): void
    {
        Http::fake([
            '*transaction/create*' => Http::response([
                'success' => true,
                'message' => 'Transaction created',
                'data' => [
                    'reference' => 'DEV-T38171POS001',
                    'merchant_ref' => 'POS-TABLE-M07-TEST',
                    'payment_method' => 'QRIS',
                    'pay_code' => null,
                    'qr_url' => 'https://tripay.co.id/qr/table-07-qris.png',
                    'qr_string' => '00020101021126580014ID.LINKAJA.WWW...',
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-T38171POS001',
                    'total_fee' => 1100, // 750 + 0.7%
                    'expired_time' => time() + 900,
                ],
            ], 200),
        ]);

        $payload = [
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '081299887766',
            'notes' => 'Tolong pedas sedikit',
            'payment_mode' => 'pay_now',
            'payment_channel' => 'QRIS',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Tidak pakai seledri',
                ],
            ],
        ];

        $response = $this->postJson("/t/{$this->table->qr_token}/order", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('order.status', PosOrder::STATUS_WAITING_PAYMENT);
        $response->assertJsonPath('payment.qr_url', 'https://tripay.co.id/qr/table-07-qris.png');
        $response->assertJsonPath('payment.channel', 'QRIS');

        $createdOrder = PosOrder::where('pos_table_id', $this->table->id)->latest()->first();
        $this->assertNotNull($createdOrder);
        $this->assertSame(PosOrder::STATUS_WAITING_PAYMENT, $createdOrder->status);
        $this->assertSame(PosOrder::GATEWAY_TRIPAY, $createdOrder->payment_gateway);
        $this->assertSame('https://tripay.co.id/qr/table-07-qris.png', $createdOrder->gateway_qr_url);
        $this->assertEquals(100000.0, (float) $createdOrder->total_amount);
        $this->assertEquals(0.0, (float) $createdOrder->paid_amount);

        // Check status polling endpoint returns is_paid = false
        $statusResponse = $this->getJson("/t/{$this->table->qr_token}/order/{$createdOrder->id}/status");
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'success' => true,
            'is_paid' => false,
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'gateway_qr_url' => 'https://tripay.co.id/qr/table-07-qris.png',
        ]);
    }

    public function test_tripay_webhook_paid_confirms_pos_order_creates_payment_and_deducts_stock(): void
    {
        $session = PosTableSession::create([
            'business_id' => $this->business->id,
            'pos_table_id' => $this->table->id,
            'pos_shift_id' => $this->shift->id,
            'session_number' => 'SESS-001',
            'customer_name' => 'Ahmad Tamu',
            'customer_phone' => '081233445566',
            'status' => PosTableSession::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->merchantUser->id,
            'pos_shift_id' => $this->shift->id,
            'pos_table_id' => $this->table->id,
            'pos_table_session_id' => $session->id,
            'order_number' => 'POS-2026-MEJA7-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T38171POS001',
            'gateway_qr_url' => 'https://tripay.co.id/qr/table-07-qris.png',
            'gateway_fee' => 1100.0,
            'subtotal' => 100000,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'customer_name_guest' => 'Ahmad Tamu',
            'customer_phone_guest' => '081233445566',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
            'hpp_cost' => 22000,
            'gross_profit' => 28000,
        ]);

        // Simulating webhook callback from TriPay
        $callbackData = [
            'reference' => 'DEV-T38171POS001',
            'merchant_ref' => 'POS-2026-MEJA7-001',
            'payment_method' => 'QRIS',
            'total_amount' => 100000,
            'fee_merchant' => 1100,
            'fee_customer' => 0,
            'total_fee' => 1100,
            'amount_received' => 98900,
            'status' => 'PAID',
            'paid_at' => time(),
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
        $response->assertJson(['success' => true]);

        // 1. PosOrder must be confirmed with full paid_amount
        $order->refresh();
        $this->assertSame(PosOrder::STATUS_CONFIRMED, $order->status);
        $this->assertEquals(100000.0, (float) $order->paid_amount);
        $this->assertTrue($order->isPaid());

        // 2. PosOrderPayment record must exist
        $paymentRecord = PosOrderPayment::where('pos_order_id', $order->id)->first();
        $this->assertNotNull($paymentRecord);
        $this->assertSame('qris', $paymentRecord->payment_method);
        $this->assertEquals(100000.0, (float) $paymentRecord->amount);
        $this->assertEquals('paid', $paymentRecord->status);
        $this->assertEquals(1450.0, (float) $paymentRecord->fee_amount);

        // 3. CashTransaction must be recorded
        $cashTx = CashTransaction::where('business_id', $this->business->id)
            ->where('reference_id', $order->id)
            ->first();
        $this->assertNotNull($cashTx);
        $this->assertEquals(98550.0, (float) $cashTx->amount);
        $this->assertEquals(CashTransaction::TYPE_IN, $cashTx->type);

        // 4. Polling endpoint now returns is_paid = true
        $statusResponse = $this->getJson("/t/{$this->table->qr_token}/order/{$order->id}/status");
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'success' => true,
            'is_paid' => true,
            'status' => PosOrder::STATUS_CONFIRMED,
        ]);
    }

    public function test_tripay_webhook_paid_auto_approves_subscription_payment(): void
    {
        $subscriptionPayment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->merchantUser->id,
            'order_number' => 'SUB-2026-TEST-001',
            'payment_type' => 'subscription',
            'package_name' => 'Paket Pro Tahunan',
            'package_duration_days' => 365,
            'plan_code' => 'core-annual',
            'cycle' => 'annual',
            'amount' => 1200000,
            'unique_code' => 345,
            'total_payable' => 1200345,
            'payment_method' => 'qris',
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $callbackData = [
            'reference' => 'DEV-T38171SUB999',
            'merchant_ref' => 'SUB-2026-TEST-001',
            'payment_method' => 'QRIS',
            'total_amount' => 1200345,
            'fee_merchant' => 9152,
            'status' => 'PAID',
            'paid_at' => time(),
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
        $response->assertJson(['success' => true]);

        // Subscription payment must be automatically approved and entitlements granted
        $subscriptionPayment->refresh();
        $this->assertSame(SubscriptionPayment::STATUS_APPROVED, $subscriptionPayment->status);
        $this->assertNotNull($subscriptionPayment->approved_at);
        $this->assertTrue($subscriptionPayment->isPaid());
    }
}
