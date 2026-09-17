<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Finance\PaymentSettlementService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\Report\FinancialReportService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\PaymentGatewayCallbackLog;
use App\Models\PaymentSettlement;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PaymentGatewayRemediationSuiteTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Testing Gateway',
            'email' => 'owner.gateway@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Resto & Toko Cooca Testing',
            'slug' => 'resto-toko-cooca-testing',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $unit = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Signature 250ml',
            'sku' => 'KOP-SIG-250',
            'selling_price' => 25000,
            'purchase_price' => 10000,
            'base_cost' => 10000,
            'is_active' => true,
        ]);
    }

    public function test_pos_paid_qr_order_completion_prevents_double_billing(): void
    {
        $table = PosTable::create([
            'business_id' => $this->business->id,
            'name' => 'Meja 01',
            'table_number' => '01',
            'capacity' => 4,
            'status' => 'occupied',
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-QR-001',
            'order_date' => Carbon::today()->toDateString(),
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'status' => PosOrder::STATUS_CONFIRMED,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T12345678',
            'table_or_reference' => 'Meja 01',
        ]);

        $service = new PosOrderService();
        $completed = $service->completePaidQrOrder($order, $this->user);

        $this->assertEquals(PosOrder::STATUS_COMPLETED, $completed->status);
        $this->assertEquals(50000, (float) $completed->paid_amount);
    }

    public function test_pos_shift_summary_segregates_cash_and_gateway_sales(): void
    {
        $shift = PosShift::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'opening_cash' => 100000,
            'opened_at' => now()->subHours(2),
            'status' => PosShift::STATUS_OPEN,
        ]);

        // Cash order
        $cashOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'pos_shift_id' => $shift->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-CASH-001',
            'order_date' => Carbon::today()->toDateString(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'paid_amount' => 25000,
            'status' => PosOrder::STATUS_COMPLETED,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $cashOrder->id,
            'payment_method' => PosOrderPayment::METHOD_CASH,
            'amount' => 25000,
            'status' => 'paid',
        ]);

        // QRIS Gateway order
        $qrisOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'pos_shift_id' => $shift->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-QRIS-002',
            'order_date' => Carbon::today()->toDateString(),
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'status' => PosOrder::STATUS_COMPLETED,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $qrisOrder->id,
            'payment_method' => PosOrderPayment::METHOD_QRIS,
            'amount' => 50000,
            'status' => 'paid',
        ]);

        $shiftService = new PosShiftService();
        $summary = $shiftService->getShiftSummary($shift);

        $this->assertEquals(25000, $summary['cash_sales']);
        $this->assertEquals(50000, $summary['non_cash_sales']);
        $this->assertEquals(50000, $summary['gateway_sales']);
        // Physical drawer cash expected should only be opening_cash (100k) + cash_sales (25k) = 125k
        $this->assertEquals(125000, $summary['expected_cash']);
    }

    public function test_financial_report_consolidates_commerce_orders_and_gateway_fees(): void
    {
        // Online Storefront Order
        $commerceOrder = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-STORE-001',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => 'Pelanggan Online',
            'customer_phone' => '081299998888',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_fee' => 350,
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'total_amount' => 60000,
            'paid_at' => now(),
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $commerceOrder->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 25000,
            'quantity' => 2,
            'subtotal' => 50000,
        ]);

        $reportService = new FinancialReportService();
        $incomeStatement = $reportService->getIncomeStatement(
            $this->business,
            Carbon::today()->startOfMonth(),
            Carbon::today()->endOfMonth()
        );

        $this->assertGreaterThanOrEqual(50000, $incomeStatement['revenues']['total_gross_sales']);
        $this->assertGreaterThanOrEqual(20000, $incomeStatement['cogs']['total_cogs']); // 2 unit * 10.000 HPP
        $this->assertGreaterThanOrEqual(350, $incomeStatement['gateway_fees']['total'] ?? 0);
    }

    public function test_payment_settlement_reconciliation_workflow(): void
    {
        $commerceOrder = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-STORE-RECON-001',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => 'Pelanggan Reconcile',
            'customer_phone' => '081299998888',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_fee' => 700,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total_amount' => 100000,
            'paid_at' => now(),
        ]);

        $settlementService = new PaymentSettlementService();
        $unsettled = $settlementService->getUnsettledPayments($this->business);

        $this->assertNotEmpty($unsettled['items']);
        $this->assertEquals(100000, $unsettled['summary']['total_gross']);

        // Execute reconciliation
        $settlement = $settlementService->reconcile(
            business: $this->business,
            data: [
                'settlement_number' => 'STL-TRIPAY-001',
                'settlement_date' => Carbon::today()->toDateString(),
                'payment_channel' => 'QRIS',
                'gross_amount' => 100000,
                'fee_amount' => 700,
                'net_amount' => 99300,
                'destination_bank' => 'BCA Toko',
            ],
            allocations: [
                [
                    'payment_type' => 'commerce_order',
                    'payment_id' => $commerceOrder->id,
                    'amount' => 100000,
                ],
            ],
            userId: $this->user->id
        );

        $this->assertEquals(PaymentSettlement::STATUS_COMPLETED, $settlement->status);
        $this->assertEquals(99300, (float) $settlement->net_amount);

        // Verify that it is no longer unsettled
        $unsettledAfter = $settlementService->getUnsettledPayments($this->business);
        $this->assertEmpty($unsettledAfter['items']);
    }

    public function test_payment_gateway_callback_log_and_manual_sync_failover(): void
    {
        // 1. Test Webhook Audit Log creation
        $log = PaymentGatewayCallbackLog::create([
            'business_id' => $this->business->id,
            'gateway' => 'tripay',
            'event' => 'payment_status',
            'merchant_ref' => 'ORD-STORE-FAILOVER-001',
            'tripay_reference' => 'DEV-T998877',
            'status_code' => 200,
            'status' => PaymentGatewayCallbackLog::STATUS_SUCCESS,
            'payload' => ['status' => 'PAID', 'amount' => 50000],
        ]);

        $this->assertDatabaseHas('payment_gateway_callback_logs', [
            'id' => $log->id,
            'merchant_ref' => 'ORD-STORE-FAILOVER-001',
            'status' => 'success',
        ]);

        // 2. Test manual sync endpoint for Storefront Order
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-STORE-FAILOVER-001',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => 'Pelanggan Failover',
            'customer_phone' => '081299998888',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'gateway_reference' => 'DEV-T998877',
            'subtotal' => 50000,
            'total_amount' => 50000,
        ]);

        $response = $this->actingAs($this->user)->post(route('storefront.orders.sync_gateway', $order->id));
        $response->assertStatus(302); // Redirect back with flash notification
    }

    public function test_pos_terminal_incoming_orders_endpoint_exposes_paid_status_and_gateway_data(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-INCOMING-001',
            'order_date' => Carbon::today()->toDateString(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'paid_amount' => 25000,
            'status' => PosOrder::STATUS_CONFIRMED,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-TINCOMING001',
            'table_or_reference' => 'Meja 05',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('pos.incoming-orders'));
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'orders' => [
                '*' => ['id', 'order_number', 'is_paid', 'payment_gateway', 'paid_amount', 'status_badge'],
            ],
        ]);
    }

    public function test_pos_terminal_pay_table_order_with_paid_qr_order_completes_without_double_charge(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TABLE-PAID-001',
            'order_date' => Carbon::today()->toDateString(),
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'status' => PosOrder::STATUS_CONFIRMED,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-TPAID001',
            'table_or_reference' => 'Meja 02',
        ]);

        // When cashier closes an already paid table order, no payments array is required
        $response = $this->actingAs($this->user)->postJson(route('pos.orders.pay-table', $order->id), []);
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->status);
    }

    public function test_payment_settlement_web_controller_index_and_reconcile_endpoints(): void
    {
        // 1. Test Index View
        $response = $this->actingAs($this->user)->get(route('finance.settlements.index'));
        $response->assertOk();
        $response->assertViewIs('app.finance.settlements.index');

        // 2. Test Reconcile POST action
        $commerceOrder = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-SETTLE-HTTP-001',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => 'Pelanggan Settle HTTP',
            'customer_phone' => '081299998888',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_fee' => 700,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total_amount' => 100000,
            'paid_at' => now(),
        ]);

        $postData = [
            'settlement_number' => 'STL-HTTP-001',
            'settlement_date' => Carbon::today()->toDateString(),
            'destination_bank' => 'BCA Toko Operasional',
            'notes' => 'Pencairan TriPay via Web Controller',
            'fee_amount' => 700,
            'allocations' => [
                [
                    'payment_type' => 'commerce_order',
                    'payment_id' => $commerceOrder->id,
                    'amount' => 100000,
                ],
            ],
        ];

        $reconcileResponse = $this->actingAs($this->user)->post(route('finance.settlements.reconcile'), $postData);
        $reconcileResponse->assertStatus(302); // Redirect back with success toast
        $reconcileResponse->assertSessionHas('success');

        $this->assertDatabaseHas('payment_settlements', [
            'business_id' => $this->business->id,
            'settlement_number' => 'STL-HTTP-001',
            'status' => 'completed',
        ]);
    }
}
