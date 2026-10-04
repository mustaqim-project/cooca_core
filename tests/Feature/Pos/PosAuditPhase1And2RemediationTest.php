<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Http\Requests\Pos\PosCheckoutRequest;
use App\Http\Requests\Pos\PosCloseShiftRequest;
use App\Http\Requests\Pos\PosOpenShiftRequest;
use App\Http\Requests\Pos\PosRefundOrderRequest;
use App\Http\Requests\Pos\PosVoidOrderRequest;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosAuditPhase1And2RemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private Business $business;
    private Location $location;
    private Product $product;
    private PosShiftService $shiftService;
    private PosOrderService $orderService;
    private AutoJournalService $journalService;
    private CashLedgerService $cashLedgerService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->shiftService = new PosShiftService();
        $this->orderService = new PosOrderService();
        $this->journalService = new AutoJournalService();
        $this->cashLedgerService = new CashLedgerService();

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->cashier = User::create([
            'name' => 'Kasir Utama',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->cashier->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Cooca Coffee & Resto',
            'email' => 'business@example.com',
            'phone' => '081234567890',
        ]);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->business->users()->attach($this->cashier->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman',
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'cup',
            'symbol' => 'cup',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        $this->journalService->ensureStandardAccounts($this->business);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Espresso Single',
            'code' => 'ESP-001',
            'selling_price' => 25000.0,
            'base_cost' => 10000.0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);
    }

    /**
     * Test F-01: Shift summary accurately deducts change_amount from cash sales, eliminating Phantom Cash Deficit.
     */
    public function test_shift_summary_deducts_change_amount_preventing_phantom_cash_deficit_f01(): void
    {
        Context::setBusiness($this->business);

        // Open shift with starting cash 100,000
        $shift = $this->shiftService->openShift(
            business: $this->business,
            user: $this->cashier,
            openingCash: 100000.0,
            locationId: $this->location->id
        );

        // Order 1: Total 25,000. Customer hands 50,000 cash. Change is 25,000. Net cash received is 25,000.
        $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 25000.0,
                    'quantity' => 1,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 50000.0, // Handed cash
                ],
            ],
            attributes: [
                'location_id' => $this->location->id,
            ],
            shift: $shift
        );

        // Order 2: Total 50,000. Customer hands 100,000 cash. Change is 50,000. Net cash received is 50,000.
        $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 25000.0,
                    'quantity' => 2,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 100000.0, // Handed cash
                ],
            ],
            attributes: [
                'location_id' => $this->location->id,
            ],
            shift: $shift
        );

        $summary = $this->shiftService->getShiftSummary($shift);

        // Net cash sales should be 25,000 + 50,000 = 75,000 (NOT 150,000 brute cash!)
        $this->assertEquals(75000.0, $summary['cash_sales']);

        // Expected cash should be 100,000 (opening) + 75,000 (net sales) = 175,000
        $this->assertEquals(175000.0, $summary['expected_cash']);

        // When cashier closes shift with actual counted cash in drawer (175,000), difference must be exactly 0
        $closedShift = $this->shiftService->closeShift($shift, 175000.0);
        $this->assertEquals(0.0, (float) $closedShift->cash_difference);
        $this->assertEquals(175000.0, (float) $closedShift->closing_cash_expected);
        $this->assertEquals(75000.0, (float) $closedShift->total_cash_sales);
    }

    /**
     * Test F-02: Void order records cash outflow in CashLedger and generates a balanced reversing journal entry.
     */
    public function test_void_order_records_cash_outflow_and_reversing_journal_f02(): void
    {
        Context::setBusiness($this->business);

        $shift = $this->shiftService->openShift(
            business: $this->business,
            user: $this->cashier,
            openingCash: 50000.0,
            locationId: $this->location->id
        );

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 25000.0,
                    'quantity' => 2,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 60000.0, // Handed cash, change = 10,000, net cash = 50,000
                ],
            ],
            attributes: [
                'location_id' => $this->location->id,
            ],
            shift: $shift
        );

        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->status);

        // Initial cash inflow recorded
        $initialInflow = CashTransaction::where('business_id', $this->business->id)
            ->where('reference_type', 'pos_order')
            ->where('type', CashTransaction::TYPE_IN)
            ->first();
        $this->assertNotNull($initialInflow);
        $this->assertEquals(50000.0, (float) $initialInflow->amount);

        // Initial sales journal recorded
        $saleJournal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_ORDER)
            ->where('reference_id', $order->id)
            ->first();
        $this->assertNotNull($saleJournal);

        // Now void the order
        $voidedOrder = $this->orderService->voidOrder($order, $this->owner, 'Kasir salah input produk');

        $this->assertEquals(PosOrder::STATUS_VOIDED, $voidedOrder->status);
        $this->assertEquals('Kasir salah input produk', $voidedOrder->void_reason);

        // Verify Cash Outflow is recorded
        $outflow = CashTransaction::where('business_id', $this->business->id)
            ->where('reference_type', 'pos_void')
            ->where('type', CashTransaction::TYPE_OUT)
            ->first();
        $this->assertNotNull($outflow);
        $this->assertEquals(50000.0, (float) $outflow->amount);

        // Verify Reversing Journal Entry is created
        $voidJournal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_VOID)
            ->where('reference_id', $order->id)
            ->with('lines')
            ->first();

        $this->assertNotNull($voidJournal);
        $this->assertEquals((float) $voidJournal->total_debit, (float) $voidJournal->total_credit);
        $this->assertGreaterThan(0, (float) $voidJournal->total_debit);
    }

    /**
     * Test F-02: Refund order records cash outflow in CashLedger and generates a balanced reversing journal entry.
     */
    public function test_refund_order_records_cash_outflow_and_reversing_journal_f02(): void
    {
        Context::setBusiness($this->business);

        $shift = $this->shiftService->openShift(
            business: $this->business,
            user: $this->cashier,
            openingCash: 50000.0,
            locationId: $this->location->id
        );

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 25000.0,
                    'quantity' => 1,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 25000.0,
                ],
            ],
            attributes: [
                'location_id' => $this->location->id,
            ],
            shift: $shift
        );

        // Refund order
        $refundedOrder = $this->orderService->refundOrder($order, $this->owner, 'Barang tumpah sebelum diterima', true);

        $this->assertEquals(PosOrder::STATUS_REFUNDED, $refundedOrder->status);

        // Verify Cash Outflow is recorded
        $outflow = CashTransaction::where('business_id', $this->business->id)
            ->where('reference_type', 'pos_refund')
            ->where('type', CashTransaction::TYPE_OUT)
            ->first();
        $this->assertNotNull($outflow);
        $this->assertEquals(25000.0, (float) $outflow->amount);

        // Verify Reversing Journal Entry is created
        $refundJournal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_REFUND)
            ->where('reference_id', $order->id)
            ->first();

        $this->assertNotNull($refundJournal);
        $this->assertEquals((float) $refundJournal->total_debit, (float) $refundJournal->total_credit);
    }

    /**
     * Test F-11: FormRequests exist and validate required attributes.
     */
    public function test_pos_form_requests_validation_rules_f11(): void
    {
        $checkoutReq = new PosCheckoutRequest();
        $this->assertArrayHasKey('items', $checkoutReq->rules());
        $this->assertArrayHasKey('payments', $checkoutReq->rules());
        $this->assertArrayHasKey('vehicle_license_plate', $checkoutReq->rules());
        $this->assertArrayHasKey('laundry_weight_kg', $checkoutReq->rules());
        $this->assertArrayHasKey('items.*.dosage_instructions', $checkoutReq->rules());

        $openShiftReq = new PosOpenShiftRequest();
        $this->assertArrayHasKey('opening_cash', $openShiftReq->rules());
        $this->assertArrayHasKey('opening_denominations', $openShiftReq->rules());

        $closeShiftReq = new PosCloseShiftRequest();
        $this->assertArrayHasKey('closing_cash_actual', $closeShiftReq->rules());
        $this->assertArrayHasKey('closing_denominations', $closeShiftReq->rules());

        $voidReq = new PosVoidOrderRequest();
        $this->assertArrayHasKey('reason', $voidReq->rules());

        $refundReq = new PosRefundOrderRequest();
        $this->assertArrayHasKey('reason', $refundReq->rules());
        $this->assertArrayHasKey('items', $refundReq->rules());
    }

    /**
     * Test F-08: AJAX refund returns JSON 422 when an InvalidArgumentException occurs instead of 302 redirect.
     */
    public function test_pos_refund_returns_json_422_on_error_when_wants_json_f08(): void
    {
        Context::setBusiness($this->business);

        // Create a voided order
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-TEST-VOIDED',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_VOIDED, // Not completed!
            'subtotal' => 25000.0,
            'total_amount' => 25000.0,
            'paid_amount' => 25000.0,
            'change_amount' => 0.0,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.orders.refund', $order->id), [
                'reason' => 'Ingin refund order yang sudah void',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
