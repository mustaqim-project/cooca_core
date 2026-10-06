<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReconciliationService;
use App\Domain\Report\Pos\PosReportingService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesReturn;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Master Acceptance Test Suite: Rekayasa & Remediasi Sistem Reporting Penjualan POS (Fase 1 – 8).
 */
class PosComprehensiveMasterReportingTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $cashierA;
    private User $ownerB;
    private Location $locationA;
    private PosRegister $registerA;
    private PosShift $shiftA;
    private ProductCategory $categoryMakanan;
    private ProductCategory $categoryMinuman;
    private Unit $unitPcs;
    private Product $productAyam;
    private Product $productEsTeh;
    private Customer $customerA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // Business A (Tenant Utama)
        $this->businessA = Business::create([
            'name' => 'Resto Nusantara Jaya',
            'slug' => 'resto-nusantara-jaya-' . Str::random(5),
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
        ]);

        $this->ownerA = User::create([
            'name' => 'Budi Santoso (Owner)',
            'email' => 'budi_' . Str::random(5) . '@nusantara.test',
            'password' => Hash::make('Password123!'),
        ]);
        $this->ownerA->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->businessA->id])->save();
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->cashierA = User::create([
            'name' => 'Siti Kasir',
            'email' => 'siti_' . Str::random(5) . '@nusantara.test',
            'password' => Hash::make('Password123!'),
        ]);
        $this->cashierA->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->businessA->id])->save();
        $this->businessA->users()->attach($this->cashierA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        // Business B (Tenant Lain untuk Uji Isolasi)
        $this->businessB = Business::create([
            'name' => 'Kafe Pesisir Lain',
            'slug' => 'kafe-pesisir-' . Str::random(5),
            'is_active' => true,
        ]);
        $this->ownerB = User::create([
            'name' => 'Hendra Owner B',
            'email' => 'hendra_' . Str::random(5) . '@pesisir.test',
            'password' => Hash::make('Password123!'),
        ]);
        $this->ownerB->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->businessB->id])->save();
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Context::setBusiness($this->businessA);
        session(['active_business_id' => $this->businessA->id]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Cabang Tebet',
            'code' => 'TBT-01',
            'is_active' => true,
        ]);

        $this->registerA = PosRegister::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'Kasir POS 1',
            'code' => 'POS-01',
            'is_active' => true,
        ]);

        $this->shiftA = PosShift::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerA->id,
            'user_id' => $this->cashierA->id,
            'opened_at' => Carbon::today()->setTime(8, 0),
            'closed_at' => Carbon::today()->setTime(17, 0),
            'opening_cash' => 100000.0,
            'closing_cash_actual' => 350000.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        $this->unitPcs = Unit::create([
            'business_id' => $this->businessA->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'symbol' => 'prs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);

        $this->categoryMakanan = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Makanan Utama',
            'slug' => 'makanan-utama',
            'is_active' => true,
        ]);

        $this->categoryMinuman = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Minuman Dingin',
            'slug' => 'minuman-dingin',
            'is_active' => true,
        ]);

        $this->productAyam = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryMakanan->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Ayam Bakar Madu',
            'code' => 'AYM-01',
            'buy_price' => 15000.0,
            'sell_price' => 30000.0,
            'is_active' => true,
        ]);

        $this->productEsTeh = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->categoryMinuman->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Es Teh Manis',
            'code' => 'TEH-01',
            'buy_price' => 2000.0,
            'sell_price' => 5000.0,
            'is_active' => true,
        ]);

        $this->customerA = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pelanggan Setia A',
            'phone' => '08123456789',
        ]);
    }

    /**
     * Skenario 1: Verifikasi Pipeline Transaksi -> Agregasi Reporting -> Rekonsiliasi 3-Arah.
     */
    public function test_end_to_end_pos_reporting_aggregation_and_reconciliation(): void
    {
        // Order 1: Sukses, Makanan + Minuman, Split Pay (Cash 20k + QRIS 15k)
        $order1 = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_shift_id' => $this->shiftA->id,
            'user_id' => $this->cashierA->id,
            'customer_id' => $this->customerA->id,
            'order_number' => 'ORD-MASTER-001',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'subtotal' => 35000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'service_charge_amount' => 0.0,
            'total_amount' => 35000.0,
            'paid_amount' => 35000.0,
            'change_amount' => 0.0,
            'total_hpp_cost' => 17000.0,
            'total_gross_profit' => 18000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $this->productAyam->id,
            'product_name' => $this->productAyam->name,
            'unit_price' => 30000.0,
            'unit_cost_hpp' => 15000.0,
            'quantity' => 1.0,
            'subtotal' => 30000.0,
            'total_price' => 30000.0,
            'total_hpp' => 15000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $this->productEsTeh->id,
            'product_name' => $this->productEsTeh->name,
            'unit_price' => 5000.0,
            'unit_cost_hpp' => 2000.0,
            'quantity' => 1.0,
            'subtotal' => 5000.0,
            'total_price' => 5000.0,
            'total_hpp' => 2000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => 'cash',
            'amount' => 20000.0,
            'status' => 'success',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => 'qris',
            'amount' => 15000.0,
            'status' => 'success',
        ]);

        // Order 2: Sukses, Diskon Voucher Rp 5.000, Tunai Rp 30.000 (Bayar 50k, Kembalian 20k)
        $order2 = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_shift_id' => $this->shiftA->id,
            'user_id' => $this->cashierA->id,
            'order_number' => 'ORD-MASTER-002',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'takeaway',
            'subtotal' => 35000.0,
            'voucher_code' => 'HEMAT5K',
            'voucher_discount_amount' => 5000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'service_charge_amount' => 0.0,
            'total_amount' => 30000.0,
            'paid_amount' => 50000.0,
            'change_amount' => 20000.0,
            'total_hpp_cost' => 17000.0,
            'total_gross_profit' => 13000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order2->id,
            'product_id' => $this->productAyam->id,
            'product_name' => $this->productAyam->name,
            'unit_price' => 30000.0,
            'unit_cost_hpp' => 15000.0,
            'quantity' => 1.0,
            'subtotal' => 30000.0,
            'total_price' => 30000.0,
            'total_hpp' => 15000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order2->id,
            'product_id' => $this->productEsTeh->id,
            'product_name' => $this->productEsTeh->name,
            'unit_price' => 5000.0,
            'unit_cost_hpp' => 2000.0,
            'quantity' => 1.0,
            'subtotal' => 5000.0,
            'total_price' => 5000.0,
            'total_hpp' => 2000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order2->id,
            'payment_method' => 'cash',
            'amount' => 50000.0,
            'status' => 'success',
        ]);

        // Order 3: Void / Dibatalkan
        PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_shift_id' => $this->shiftA->id,
            'user_id' => $this->cashierA->id,
            'order_number' => 'ORD-MASTER-VOID',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_VOIDED,
            'void_reason' => 'Pelanggan batal pesan',
            'voided_by' => $this->ownerA->id,
            'voided_at' => Carbon::now(),
            'subtotal' => 30000.0,
            'total_amount' => 30000.0,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->businessA->id,
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $reportingService = new PosReportingService();
        $kpis = $reportingService->getKpiSummary($filter);

        // Validasi KPI Summary
        $this->assertEquals(2, $kpis->totalOrders);
        $this->assertEquals(70000.0, $kpis->grossSales);
        $this->assertEquals(5000.0, $kpis->totalDiscount);
        $this->assertEquals(65000.0, $kpis->netSales);
        $this->assertEquals(34000.0, $kpis->totalHpp);
        $this->assertEquals(31000.0, $kpis->grossProfit);
        $this->assertEquals(32500.0, $kpis->averageOrderValue);

        // Validasi Breakdown Produk
        $products = $reportingService->getProductPerformance($filter);
        $this->assertCount(2, $products);

        // Validasi 3-Way Reconciliation
        $reconciliationService = new PosReconciliationService();
        $reconResult = $reconciliationService->reconcile($filter);
        $this->assertEquals(65000.0, $reconResult->totalOrdersAmount);
        $this->assertEquals(85000.0, $reconResult->totalPaymentsAmount);
    }

    /**
     * Skenario 2: Verifikasi HTTP GET Dashboard Pelaporan dengan 15 Sub-Modul Tabs.
     */
    public function test_web_controller_renders_all_reporting_tabs_successfully(): void
    {
        $tabs = [
            'overview', 'transactions', 'products', 'categories',
            'cashiers', 'outlets', 'payments', 'discounts',
            'refunds', 'voids', 'shifts', 'hourly',
            'customers', 'channels', 'profitability'
        ];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->ownerA)
                ->get(route('pos.reports.index', ['tab' => $tab]));

            $response->assertStatus(200);
            $response->assertSee(__('pos.reports_title'));
        }
    }

    /**
     * Skenario 3: Verifikasi Endpoint JSON Quick-View Detail Transaksi & Proteksi Tenant.
     */
    public function test_quick_view_order_detail_json_endpoint_and_tenant_isolation(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_shift_id' => $this->shiftA->id,
            'user_id' => $this->cashierA->id,
            'order_number' => 'ORD-DETAIL-TEST',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 30000.0,
            'total_amount' => 30000.0,
            'paid_amount' => 30000.0,
            'total_hpp_cost' => 15000.0,
            'total_gross_profit' => 15000.0,
        ]);

        // 1. Owner A berhasil melihat detail JSON
        $responseA = $this->actingAs($this->ownerA)
            ->getJson(route('pos.reports.orders.detail', ['order' => $order->id]));

        $responseA->assertStatus(200);
        $responseA->assertJsonPath('success', true);
        $responseA->assertJsonPath('data.order_number', 'ORD-DETAIL-TEST');
        $responseA->assertJsonPath('data.financial.gross_profit', 15000);

        // 2. Owner B (Tenant Lain) dilarang mengakses (404/403 Isolated)
        $responseB = $this->actingAs($this->ownerB)
            ->getJson(route('pos.reports.orders.detail', ['order' => $order->id]));

        $this->assertTrue(in_array($responseB->status(), [403, 404]));
    }

    /**
     * Skenario 4: Verifikasi Master 9-Sheet Excel Export Endpoint Streaming.
     */
    public function test_master_excel_9_sheet_export_streams_properly(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get(route('pos.reports.export-excel', [
                'preset' => 'today',
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Laporan_POS_', (string) $response->headers->get('content-disposition'));
    }
}
