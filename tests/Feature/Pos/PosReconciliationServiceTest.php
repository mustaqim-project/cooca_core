<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReconciliationService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $cashier;
    private Location $location;
    private PosRegister $register;
    private Unit $unit;
    private ProductCategory $category;
    private PosReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Test POS Retail Business',
            'slug' => 'test-pos-retail',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'Budi Cashier',
            'email' => 'budi@example.com',
        ]);
        $this->business->users()->attach($this->cashier->id);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'code' => 'OUT-01',
            'is_active' => true,
        ]);

        $this->register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir Register 1',
            'code' => 'REG-01',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);

        $this->category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan & Minuman',
            'slug' => 'makanan-minuman',
            'is_active' => true,
        ]);

        $this->service = new PosReconciliationService();
    }

    public function test_reconcile_when_balanced_and_no_discrepancies(): void
    {
        $date = Carbon::parse('2026-10-04');

        $shift = PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $this->register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => $date->copy()->setTime(8, 0),
            'closed_at' => $date->copy()->setTime(17, 0),
            'opening_cash' => 100000.0,
            'closing_cash_expected' => 250000.0,
            'closing_cash_actual' => 250000.0,
            'cash_difference' => 0.0,
            'total_cash_sales' => 150000.0,
            'total_non_cash_sales' => 50000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'pos_register_id' => $this->register->id,
            'pos_shift_id' => $shift->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-BAL-001',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 150000.0,
            'total_amount' => 150000.0,
            'paid_amount' => 150000.0,
            'change_amount' => 0.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => PosOrderPayment::METHOD_CASH,
            'amount' => 150000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $reconciliation = $this->service->reconcile($filter);

        $this->assertTrue($reconciliation->isBalanced);
        $this->assertEquals(150000.0, $reconciliation->totalOrdersAmount);
        $this->assertEquals(150000.0, $reconciliation->totalPaymentsAmount);
        $this->assertEquals(0.0, $reconciliation->orderPaymentDiscrepancy);
        $this->assertEquals(150000.0, $reconciliation->totalCashPayments);
        $this->assertEquals(250000.0, $reconciliation->totalShiftExpectedCash);
        $this->assertEquals(250000.0, $reconciliation->totalShiftActualCash);
        $this->assertEquals(0.0, $reconciliation->shiftCashDiscrepancy);
        $this->assertEquals(0, $reconciliation->unsettledOrdersCount);
        $this->assertEquals(1, $reconciliation->balancedShiftsCount);
        $this->assertCount(0, $reconciliation->anomalies);
    }

    public function test_reconcile_detects_unsettled_and_overpaid_orders(): void
    {
        $date = Carbon::parse('2026-10-04');

        // Order 1: Unsettled (Total 100k, Paid 60k)
        $order1 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-UNSETTLED-01',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'paid_amount' => 60000.0,
            'change_amount' => 0.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => PosOrderPayment::METHOD_QRIS,
            'amount' => 60000.0,
            'status' => 'paid',
        ]);

        // Order 2: Overpaid (Total 50k, Paid 70k, no change recorded)
        $order2 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-OVERPAID-02',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 50000.0,
            'total_amount' => 50000.0,
            'paid_amount' => 70000.0,
            'change_amount' => 0.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $order2->id,
            'payment_method' => PosOrderPayment::METHOD_TRANSFER,
            'amount' => 70000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $reconciliation = $this->service->reconcile($filter);

        $this->assertFalse($reconciliation->isBalanced);
        $this->assertEquals(1, $reconciliation->unsettledOrdersCount);
        $this->assertEquals(40000.0, $reconciliation->unsettledOrdersAmount);
        $this->assertEquals(1, $reconciliation->overpaidOrdersCount);
        $this->assertEquals(20000.0, $reconciliation->overpaidOrdersAmount);

        $unsettledAnomaly = $reconciliation->anomalies->firstWhere('type', 'UNSETTLED_ORDER');
        $this->assertNotNull($unsettledAnomaly);
        $this->assertEquals('critical', $unsettledAnomaly['severity']);
        $this->assertEquals('ORD-UNSETTLED-01', $unsettledAnomaly['reference']);
    }

    public function test_reconcile_detects_shift_cash_variance_short_and_over(): void
    {
        $date = Carbon::parse('2026-10-04');

        // Shift 1: Short Cash (-50,000)
        PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $this->register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => $date->copy()->setTime(8, 0),
            'closed_at' => $date->copy()->setTime(14, 0),
            'opening_cash' => 100000.0,
            'closing_cash_expected' => 300000.0,
            'closing_cash_actual' => 250000.0,
            'cash_difference' => -50000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        // Shift 2: Over Cash (+20,000)
        PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $this->register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => $date->copy()->setTime(14, 0),
            'closed_at' => $date->copy()->setTime(21, 0),
            'opening_cash' => 100000.0,
            'closing_cash_expected' => 200000.0,
            'closing_cash_actual' => 220000.0,
            'cash_difference' => 20000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $reconciliation = $this->service->reconcile($filter);

        $this->assertEquals(2, $reconciliation->totalShiftsAudited);
        $this->assertEquals(1, $reconciliation->shortShiftsCount);
        $this->assertEquals(1, $reconciliation->overShiftsCount);
        $this->assertEquals(-30000.0, $reconciliation->shiftCashDiscrepancy);

        $shortAnomaly = $reconciliation->anomalies->firstWhere('type', 'SHORT_CASH');
        $this->assertNotNull($shortAnomaly);
        $this->assertEquals(50000.0, $shortAnomaly['amount']);

        $overAnomaly = $reconciliation->anomalies->firstWhere('type', 'OVER_CASH');
        $this->assertNotNull($overAnomaly);
        $this->assertEquals(20000.0, $overAnomaly['amount']);
    }

    public function test_fraud_audit_detects_void_after_print_and_cashier_risk_ranking(): void
    {
        $date = Carbon::parse('2026-10-04');

        // Order 1: Completed & Printed
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-COMP-01',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'paid_amount' => 100000.0,
            'print_count' => 1,
            'last_printed_at' => now(),
        ]);

        // Order 2: Voided AFTER Printed (Red Flag!)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-VOID-SUSP-02',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_VOIDED,
            'subtotal' => 75000.0,
            'total_amount' => 75000.0,
            'paid_amount' => 0.0,
            'print_count' => 2,
            'last_printed_at' => now(),
            'void_reason' => 'Pelanggan membatalkan pesanan',
            'voided_at' => now(),
        ]);

        // Order 3: Voided without supervisor approval (> 100k)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-VOID-NOSUP-03',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_VOIDED,
            'subtotal' => 120000.0,
            'total_amount' => 120000.0,
            'paid_amount' => 0.0,
            'print_count' => 0,
            'supervisor_approved_by' => null,
            'void_reason' => 'Salah input menu',
            'voided_at' => now(),
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $fraudAudit = $this->service->auditVoidsAndFraud($filter);

        $this->assertEquals(3, $fraudAudit->totalOrders);
        $this->assertEquals(2, $fraudAudit->totalVoidOrders);
        $this->assertEquals(195000.0, $fraudAudit->totalVoidAmount);
        $this->assertEquals(66.67, $fraudAudit->voidRatePercent);
        $this->assertEquals(1, $fraudAudit->voidAfterPrintCount);
        $this->assertEquals(75000.0, $fraudAudit->voidAfterPrintAmount);
        $this->assertEquals(2, $fraudAudit->voidWithoutSupervisorCount);

        // Cashier rankings
        $cashierRank = $fraudAudit->cashierRankings->firstWhere('cashier_id', (string) $this->cashier->id);
        $this->assertNotNull($cashierRank);
        $this->assertEquals('critical', $cashierRank['risk_level']);

        // Suspicious transactions
        $suspiciousVoid = $fraudAudit->suspiciousTransactions->firstWhere('risk_type', 'Void Setelah Cetak Struk');
        $this->assertNotNull($suspiciousVoid);
        $this->assertEquals('ORD-VOID-SUSP-02', $suspiciousVoid['order_number']);
        $this->assertEquals('critical', $suspiciousVoid['severity']);
    }

    public function test_margin_analytics_detects_loss_leaders_and_zero_cogs_warnings(): void
    {
        $date = Carbon::parse('2026-10-04');

        $prodHigh = Product::create([
            'business_id' => $this->business->id,
            'name' => 'High Margin Coffee',
            'code' => 'PRD-HIGH',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $prodLoss = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Loss Leader Sugar',
            'code' => 'PRD-LOSS',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $prodZeroCogs = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Missing Cost Item',
            'code' => 'PRD-ZERO-COGS',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-MARGIN-01',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 110000.0,
            'total_amount' => 110000.0,
            'paid_amount' => 110000.0,
        ]);

        // Item 1: High Margin (Price 50k, HPP 20k -> Gross Profit 30k, Margin 60%)
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prodHigh->id,
            'product_name' => $prodHigh->name,
            'product_code' => $prodHigh->code,
            'unit_price' => 50000.0,
            'unit_cost_hpp' => 20000.0,
            'quantity' => 1,
            'subtotal' => 50000.0,
            'discount_amount' => 0.0,
            'total_price' => 50000.0,
            'total_hpp' => 20000.0,
        ]);

        // Item 2: Loss Leader (Price 30k, HPP 35k -> Loss 5k, Margin -16.67%)
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prodLoss->id,
            'product_name' => $prodLoss->name,
            'product_code' => $prodLoss->code,
            'unit_price' => 30000.0,
            'unit_cost_hpp' => 35000.0,
            'quantity' => 1,
            'subtotal' => 30000.0,
            'discount_amount' => 0.0,
            'total_price' => 30000.0,
            'total_hpp' => 35000.0,
        ]);

        // Item 3: Zero COGS (Price 30k, HPP 0.0 -> Warning!)
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prodZeroCogs->id,
            'product_name' => $prodZeroCogs->name,
            'product_code' => $prodZeroCogs->code,
            'unit_price' => 30000.0,
            'unit_cost_hpp' => 0.0,
            'quantity' => 1,
            'subtotal' => 30000.0,
            'discount_amount' => 0.0,
            'total_price' => 30000.0,
            'total_hpp' => 0.0,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $marginAnalytics = $this->service->analyzeMarginAndProfitability($filter);

        $this->assertEquals(110000.0, $marginAnalytics->totalNetSales);
        $this->assertEquals(55000.0, $marginAnalytics->totalCogs);
        $this->assertEquals(55000.0, $marginAnalytics->totalGrossProfit);
        $this->assertEquals(50.0, $marginAnalytics->overallGrossMarginPct);

        // Zero COGS Warning
        $this->assertEquals(1, $marginAnalytics->zeroCogsItemsCount);
        $this->assertEquals(30000.0, $marginAnalytics->zeroCogsRevenue);
        $this->assertCount(1, $marginAnalytics->zeroCogsWarningProducts);

        // Loss Leader Detection
        $this->assertCount(1, $marginAnalytics->lossLeaderProducts);
        $lossLeader = $marginAnalytics->lossLeaderProducts->first();
        $this->assertEquals('Loss Leader Sugar', $lossLeader['product_name']);
        $this->assertEquals(5000.0, $lossLeader['loss_amount']);

        // Margin Tiers
        $this->assertEquals(1, $marginAnalytics->marginTiers['high_margin']['count']);
        $this->assertEquals(1, $marginAnalytics->marginTiers['negative_margin']['count']);

        // Category Matrix
        $this->assertCount(1, $marginAnalytics->categoryProfitability);
    }

    public function test_discount_analytics_tracks_leakage_and_cashier_manual_discounts(): void
    {
        $date = Carbon::parse('2026-10-04');

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kemeja Polos',
            'code' => 'PRD-SHIRT',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // Order 1: Item Discount 5,000 + Manual Cart Discount 10,000
        $order1 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-DISC-01',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'discount_amount' => 10000.0,
            'total_amount' => 85000.0,
            'paid_amount' => 85000.0,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'product_code' => $prod->code,
            'unit_price' => 100000.0,
            'quantity' => 1,
            'subtotal' => 100000.0,
            'discount_amount' => 5000.0,
            'total_price' => 95000.0,
            'total_hpp' => 50000.0,
        ]);

        // Order 2: Voucher Discount 20,000
        $order2 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-VOUCHER-02',
            'order_date' => $date->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'voucher_code' => 'PROMO20K',
            'voucher_discount_amount' => 20000.0,
            'total_amount' => 80000.0,
            'paid_amount' => 80000.0,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order2->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'product_code' => $prod->code,
            'unit_price' => 100000.0,
            'quantity' => 1,
            'subtotal' => 100000.0,
            'discount_amount' => 0.0,
            'total_price' => 100000.0,
            'total_hpp' => 50000.0,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $discountAnalytics = $this->service->analyzeDiscountsAndPromotions($filter);

        $this->assertEquals(200000.0, $discountAnalytics->totalGrossSales);
        $this->assertEquals(35000.0, $discountAnalytics->totalDiscountAmount);
        $this->assertEquals(17.5, $discountAnalytics->overallDiscountRatePct);
        $this->assertEquals(5000.0, $discountAnalytics->itemDiscountAmount);
        $this->assertEquals(10000.0, $discountAnalytics->orderDiscountAmount);
        $this->assertEquals(20000.0, $discountAnalytics->voucherDiscountAmount);

        // Voucher Summary
        $this->assertCount(1, $discountAnalytics->voucherUsageSummary);
        $vSummary = $discountAnalytics->voucherUsageSummary->first();
        $this->assertEquals('PROMO20K', $vSummary['voucher_code']);
        $this->assertEquals(1, $vSummary['usage_count']);
        $this->assertEquals(20000.0, $vSummary['total_discount_amount']);

        // Cashier Manual Discounts
        $cashierDisc = $discountAnalytics->cashierDiscountRankings->first();
        $this->assertNotNull($cashierDisc);
        $this->assertEquals(35000.0, $cashierDisc['total_discount_given']);
        $this->assertEquals(15000.0, $cashierDisc['manual_discount_amount']); // 5k item + 10k manual cart
    }

    public function test_shift_reconciliation_list_maps_status_and_variances(): void
    {
        $date = Carbon::parse('2026-10-04');

        PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $this->register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'opened_at' => $date->copy()->setTime(8, 0),
            'closed_at' => $date->copy()->setTime(16, 0),
            'opening_cash' => 100000.0,
            'closing_cash_expected' => 300000.0,
            'closing_cash_actual' => 280000.0,
            'cash_difference' => -20000.0,
            'status' => PosShift::STATUS_CLOSED,
            'notes' => 'Kasir lupa kembalian 20rb',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $date,
            endDate: $date
        );

        $shiftList = $this->service->getShiftReconciliationList($filter);

        $this->assertCount(1, $shiftList);
        $shiftItem = $shiftList->first();

        $this->assertEquals('Budi Cashier', $shiftItem['cashier_name']);
        $this->assertEquals('Kasir Register 1', $shiftItem['register_name']);
        $this->assertEquals(-20000.0, $shiftItem['cash_difference']);
        $this->assertEquals('Kurang / Short', $shiftItem['audit_status']);
    }
}
