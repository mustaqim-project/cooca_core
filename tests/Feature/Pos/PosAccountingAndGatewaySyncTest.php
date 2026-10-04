<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\PosTableSession;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosAccountingAndGatewaySyncTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private PosShift $shift;
    private PosTable $table;
    private Product $product;
    private AutoJournalService $journalService;
    private CashLedgerService $cashLedgerService;

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

        $this->journalService = new AutoJournalService();
        $this->cashLedgerService = new CashLedgerService();

        $this->owner = User::create([
            'name' => 'Owner Bistro',
            'email' => 'owner@bistro.cooca.com',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Cooca Gourmet Bistro',
            'slug' => 'cooca-gourmet-bistro',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->journalService->ensureStandardAccounts($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Resto Utama',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'shift_number' => 'SHIFT-2026-001',
            'opening_cash' => 500000,
            'status' => PosShift::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'T-01',
            'name' => 'Meja VIP 01',
            'capacity' => 4,
            'status' => PosTable::STATUS_OCCUPIED,
            'qr_token' => 'qr-token-vip-01',
            'is_active' => true,
        ]);

        $portion = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'is_standard' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan Utama',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $portion->id,
            'name' => 'Steak Wagyu A5',
            'code' => 'STK-WAGYU',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 100000.0,
            'base_cost' => 45000.0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_sync_gateway_status_paid_records_payment_stock_cash_ledger_and_auto_journal(): void
    {
        $session = PosTableSession::create([
            'business_id' => $this->business->id,
            'pos_table_id' => $this->table->id,
            'pos_shift_id' => $this->shift->id,
            'session_number' => 'SESS-101',
            'customer_name' => 'Budi Sudarsono',
            'customer_phone' => '081299887766',
            'status' => PosTableSession::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'pos_table_id' => $this->table->id,
            'pos_table_session_id' => $session->id,
            'order_number' => 'POS-2026-T1-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T38171POS999',
            'gateway_qr_url' => 'https://tripay.co.id/qr/table-01-qris.png',
            'gateway_fee' => 1100.0,
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'total_hpp_cost' => 45000.0,
            'total_gross_profit' => 55000.0,
            'paid_amount' => 0.0,
            'customer_name_guest' => 'Budi Sudarsono',
            'customer_phone_guest' => '081299887766',
            'table_or_reference' => 'Meja VIP 01',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'quantity' => 1,
            'unit_price' => 100000.0,
            'unit_cost_hpp' => 45000.0,
            'subtotal' => 100000.0,
            'total_price' => 100000.0,
            'total_hpp' => 45000.0,
        ]);

        Http::fake([
            '*/transaction/detail*' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-T38171POS999',
                    'merchant_ref' => 'POS-2026-T1-001',
                    'payment_method' => 'QRIS',
                    'payment_name' => 'QRIS Dinamis',
                    'total_fee' => 1100,
                    'amount_received' => 98900,
                    'status' => 'PAID',
                    'paid_at' => time(),
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('pos.orders.sync_gateway', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 1. Assert Order Updated
        $order->refresh();
        $this->assertEquals(PosOrder::STATUS_CONFIRMED, $order->status);
        $this->assertEquals(100000.0, (float) $order->paid_amount);
        $this->assertEquals(PosOrder::GATEWAY_TRIPAY, $order->payment_gateway);

        // 2. Assert PosOrderPayment Created
        $payment = PosOrderPayment::where('pos_order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(100000.0, (float) $payment->amount);
        $this->assertEquals('DEV-T38171POS999', $payment->reference_number);

        // 3. Assert Stock Movement
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('movement_type', StockMovement::TYPE_POS_SALE)
            ->where('reference_id', $order->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-1.0, (float) $movement->quantity_change);

        // 4. Assert Cash Ledger Transaction Created
        $cashTx = CashTransaction::where('business_id', $this->business->id)
            ->where('reference_type', 'pos_order')
            ->where('reference_id', (string) $payment->id)
            ->first();
        $this->assertNotNull($cashTx, 'CashTransaction for TriPay POS Resync must be created');
        $this->assertEquals(CashTransaction::TYPE_IN, $cashTx->type);
        $this->assertEquals((float) $payment->net_amount, (float) $cashTx->amount);

        // 5. Assert Auto-Journal Entry Created & Balanced
        $journal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_ORDER)
            ->where('reference_id', $order->id)
            ->with('lines')
            ->first();
        $this->assertNotNull($journal, 'Double-entry JournalEntry for TriPay POS Resync must be created');
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);
        $this->assertGreaterThan(0, (float) $journal->total_debit);

        // Verify Journal Lines (Debits: Gateway/Cash, COGS; Credits: Revenue, Inventory)
        $debitLines = $journal->lines->where('type', JournalEntryLine::TYPE_DEBIT);
        $creditLines = $journal->lines->where('type', JournalEntryLine::TYPE_CREDIT);
        $this->assertNotEmpty($debitLines);
        $this->assertNotEmpty($creditLines);
    }

    public function test_sync_gateway_status_is_idempotent_no_duplicate_journals_or_cash_ledger(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-IDEMP-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-IDEMP-001',
            'subtotal' => 50000.0,
            'total_amount' => 50000.0,
            'total_hpp_cost' => 22000.0,
            'paid_amount' => 0.0,
            'table_or_reference' => 'Meja 5',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 50000.0,
            'unit_cost_hpp' => 22000.0,
            'subtotal' => 50000.0,
            'total_price' => 50000.0,
            'total_hpp' => 22000.0,
        ]);

        Http::fake([
            '*/transaction/detail*' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-IDEMP-001',
                    'merchant_ref' => 'POS-2026-IDEMP-001',
                    'payment_method' => 'QRIS',
                    'total_fee' => 750,
                    'status' => 'PAID',
                ],
            ], 200),
        ]);

        // First Sync
        $this->actingAs($this->owner)->post(route('pos.orders.sync_gateway', $order));

        $journalCountAfterFirst = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_ORDER)
            ->where('reference_id', $order->id)
            ->count();
        $this->assertEquals(1, $journalCountAfterFirst);

        // Reset status to allow secondary resync check
        $order->update(['status' => PosOrder::STATUS_WAITING_PAYMENT]);

        // Second Sync
        $this->actingAs($this->owner)->post(route('pos.orders.sync_gateway', $order));

        $journalCountAfterSecond = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_ORDER)
            ->where('reference_id', $order->id)
            ->count();
        $this->assertEquals(1, $journalCountAfterSecond, 'Idempotency must prevent duplicate journal entries');

        $paymentCount = PosOrderPayment::where('pos_order_id', $order->id)->count();
        $this->assertEquals(1, $paymentCount, 'Idempotency must prevent duplicate payments');
    }

    public function test_sync_gateway_status_expired_voids_order_without_accounting_records(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-EXP-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-EXPIRED-999',
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'paid_amount' => 0.0,
            'table_or_reference' => 'Meja 9',
        ]);

        Http::fake([
            '*/transaction/detail*' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'DEV-EXPIRED-999',
                    'merchant_ref' => 'POS-2026-EXP-001',
                    'status' => 'EXPIRED',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('pos.orders.sync_gateway', $order));

        $order->refresh();
        $this->assertEquals(PosOrder::STATUS_VOIDED, $order->status);
        $this->assertNotEmpty($order->void_reason);

        // Verify No Journals or Cash transactions
        $journalCount = JournalEntry::where('reference_id', $order->id)->count();
        $this->assertEquals(0, $journalCount);

        $cashCount = CashTransaction::where('reference_id', $order->id)->count();
        $this->assertEquals(0, $cashCount);
    }

    public function test_tripay_webhook_pos_order_creates_auto_journal_and_cash_inflow(): void
    {
        $session = PosTableSession::create([
            'business_id' => $this->business->id,
            'pos_table_id' => $this->table->id,
            'pos_shift_id' => $this->shift->id,
            'session_number' => 'SESS-201',
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081255667788',
            'status' => PosTableSession::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'pos_table_id' => $this->table->id,
            'pos_table_session_id' => $session->id,
            'order_number' => 'POS-2026-HOOK-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_WAITING_PAYMENT,
            'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T38171HOOK001',
            'gateway_fee' => 1100.0,
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'total_hpp_cost' => 45000.0,
            'paid_amount' => 0.0,
            'customer_name_guest' => 'Siti Nurhaliza',
            'customer_phone_guest' => '081255667788',
            'table_or_reference' => 'Meja VIP 01',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'quantity' => 1,
            'unit_price' => 100000.0,
            'unit_cost_hpp' => 45000.0,
            'subtotal' => 100000.0,
            'total_price' => 100000.0,
            'total_hpp' => 45000.0,
        ]);

        $callbackData = [
            'reference' => 'DEV-T38171HOOK001',
            'merchant_ref' => 'POS-2026-HOOK-001',
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

        // Assert Order confirmed
        $order->refresh();
        $this->assertEquals(PosOrder::STATUS_CONFIRMED, $order->status);

        // Assert Auto-Journal created
        $journal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_ORDER)
            ->where('reference_id', $order->id)
            ->with('lines')
            ->first();

        $this->assertNotNull($journal, 'Webhook must create double-entry JournalEntry for POS table order');
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);
    }
}
