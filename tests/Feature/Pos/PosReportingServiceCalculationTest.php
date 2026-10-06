<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Models\Business;
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
use App\Support\Math\FinancialMath;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosReportingServiceCalculationTest extends TestCase
{
    use RefreshDatabase;

    private PosReportingService $service;
    private Business $business;
    private User $cashier;
    private Location $location;
    private PosRegister $register;
    private PosShift $shift;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->service = new PosReportingService();

        $this->cashier = User::create([
            'name' => 'Budi Kasir',
            'email' => 'budi.kasir@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Resto & Cafe Berkah',
            'email' => 'resto.berkah@example.com',
            'phone' => '08123456789',
            'operating_mode' => 'team',
            'industry_type' => 'fnb',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'code' => 'OUT-01',
            'address' => 'Jl. Merdeka No. 10',
            'is_active' => true,
        ]);

        $this->register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir 01',
            'code' => 'REG-01',
            'status' => 'active',
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'pos_register_id' => $this->register->id,
            'user_id' => $this->cashier->id,
            'opened_at' => Carbon::today()->setTime(8, 0),
            'opening_cash' => 200000,
            'status' => PosShift::STATUS_OPEN,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'symbol' => 'prs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);
    }

    /**
     * Test 1: Verifikasi helper FinancialMath menangani division by zero, margin %, dan growth %.
     */
    public function test_financial_math_handles_edge_cases_and_safe_division(): void
    {
        // Safe division
        $this->assertEquals(0.0, FinancialMath::safeDivide(100.0, 0.0));
        $this->assertEquals(5.0, FinancialMath::safeDivide(100.0, 20.0));

        // Margin calculation
        $this->assertEquals(0.0, FinancialMath::calculateMargin(50000.0, 0.0));
        $this->assertEquals(25.0, FinancialMath::calculateMargin(25000.0, 100000.0));

        // Growth calculation
        $this->assertEquals(100.0, FinancialMath::calculateGrowth(50000.0, 0.0));
        $this->assertEquals(0.0, FinancialMath::calculateGrowth(0.0, 0.0));
        $this->assertEquals(50.0, FinancialMath::calculateGrowth(150000.0, 100000.0));
        $this->assertEquals(-20.0, FinancialMath::calculateGrowth(80000.0, 100000.0));
    }

    /**
     * Test 2: Verifikasi perhitungan Gross Sales, Discounts, Net Sales, Tax, HPP & Gross Profit secara akurat.
     */
    public function test_kpi_summary_calculates_all_financial_metrics_accurately(): void
    {
        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan Utama',
            'slug' => 'makanan-utama',
        ]);

        $product1 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Nasi Goreng Spesial',
            'code' => 'NASGOR-01',
            'price' => 30000,
            'cost_price' => 15000,
            'type' => 'goods',
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Es Teh Manis',
            'code' => 'ESTEH-01',
            'price' => 10000,
            'cost_price' => 3000,
            'type' => 'goods',
            'is_active' => true,
        ]);

        // Order 1: 2 Nasgor (60k, HPP 30k) + 2 Es Teh (20k, HPP 6k) = Subtotal 80k, Diskon 5k, PPN 10% (7.5k), Service 5% (3.75k)
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'pos_register_id' => $this->register->id,
            'pos_shift_id' => $this->shift->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-20261004-001',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'sales_channel' => 'pos_direct',
            'subtotal' => 80000,
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'discount_amount' => 5000,
            'tax_percentage' => 10,
            'tax_amount' => 7500,
            'service_charge_percentage' => 5,
            'service_charge_amount' => 3750,
            'rounding_amount' => 0,
            'total_amount' => 86250,
            'paid_amount' => 100000,
            'change_amount' => 13750,
            'total_hpp_cost' => 36000,
            'total_gross_profit' => 39000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'product_code' => $product1->code,
            'unit_price' => 30000,
            'unit_cost_hpp' => 15000,
            'quantity' => 2,
            'subtotal' => 60000,
            'discount_amount' => 0,
            'total_price' => 60000,
            'total_hpp' => 30000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'product_code' => $product2->code,
            'unit_price' => 10000,
            'unit_cost_hpp' => 3000,
            'quantity' => 2,
            'subtotal' => 20000,
            'discount_amount' => 0,
            'total_price' => 20000,
            'total_hpp' => 6000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 86250,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            includeComparison: false
        );

        $kpi = $this->service->getKpiSummary($filter);

        $this->assertEquals(80000.0, $kpi->grossSales, 'Gross sales harus 80.000');
        $this->assertEquals(5000.0, $kpi->totalDiscount, 'Total discount harus 5.000');
        $this->assertEquals(75000.0, $kpi->grossRevenue, 'Gross revenue harus 75.000');
        $this->assertEquals(75000.0, $kpi->netSales, 'Net sales tanpa refund harus 75.000');
        $this->assertEquals(7500.0, $kpi->taxAmount, 'Pajak PPN harus 7.500');
        $this->assertEquals(3750.0, $kpi->serviceChargeAmount, 'Service charge harus 3.750');
        $this->assertEquals(86250.0, $kpi->grandTotal, 'Grand total harus 86.250');
        $this->assertEquals(36000.0, $kpi->totalHpp, 'Total HPP modal harus 36.000');
        $this->assertEquals(39000.0, $kpi->grossProfit, 'Gross profit harus 39.000 (75.000 - 36.000)');
        $this->assertEquals(52.0, $kpi->grossMarginPercent, 'Gross margin % harus 52% (39.000 / 75.000 * 100)');
        $this->assertEquals(1, $kpi->totalOrders, 'Total orders harus 1');
        $this->assertEquals(4.0, $kpi->totalItemsSold, 'Total items sold harus 4');
        $this->assertEquals(75000.0, $kpi->averageOrderValue, 'AOV harus 75.000');
        $this->assertEquals(20000.0, $kpi->averageSellingPrice, 'ASP harus 20.000 (80.000 / 4)');
    }

    /**
     * Test 3: Verifikasi pengurangan SalesReturn (Retur / Refund) dari Net Sales.
     */
    public function test_kpi_summary_deducts_sales_returns_from_net_sales(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-20261004-002',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'total_hpp_cost' => 40000,
            'total_gross_profit' => 60000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_name' => 'Kemeja Polos',
            'unit_price' => 100000,
            'unit_cost_hpp' => 40000,
            'quantity' => 1,
            'subtotal' => 100000,
            'total_price' => 100000,
            'total_hpp' => 40000,
        ]);

        // Buat Sales Return sebesar 25.000
        SalesReturn::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_order_id' => $order->id,
            'return_number' => 'RET-20261004-001',
            'return_date' => Carbon::today(),
            'status' => 'completed',
            'total_amount' => 25000,
            'reason' => 'Ukuran salah',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            includeComparison: false
        );

        $kpi = $this->service->getKpiSummary($filter);

        $this->assertEquals(100000.0, $kpi->grossSales);
        $this->assertEquals(25000.0, $kpi->refundAmount, 'Refund amount harus 25.000');
        $this->assertEquals(75000.0, $kpi->netSales, 'Net sales harus berkurang menjadi 75.000 (100.000 - 25.000)');
    }

    /**
     * Test 4: Verifikasi filter multi-dimensi (Outlet, Kasir, Saluran Penjualan).
     */
    public function test_multi_dimensional_filtering_by_location_and_cashier(): void
    {
        $location2 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Cabang 2',
            'code' => 'OUT-02',
            'is_active' => true,
        ]);

        $cashier2 = User::create([
            'name' => 'Siti Kasir',
            'email' => 'siti.kasir@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business->users()->attach($cashier2->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
        ]);

        // Order 1 di Outlet Utama oleh Budi (50k)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-ORDER-1',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'pos_direct',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'total_hpp_cost' => 20000,
            'total_gross_profit' => 30000,
        ]);

        // Order 2 di Outlet Cabang 2 oleh Siti (120k)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $location2->id,
            'user_id' => $cashier2->id,
            'order_number' => 'POS-ORDER-2',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'gofood',
            'subtotal' => 120000,
            'total_amount' => 120000,
            'total_hpp_cost' => 50000,
            'total_gross_profit' => 70000,
        ]);

        // Filter Hanya Outlet Cabang 2
        $filterLocation = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            locationId: $location2->id,
            includeComparison: false
        );

        $kpiLocation = $this->service->getKpiSummary($filterLocation);
        $this->assertEquals(1, $kpiLocation->totalOrders);
        $this->assertEquals(120000.0, $kpiLocation->netSales);

        // Filter Hanya Saluran Gofood
        $filterChannel = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            salesChannel: 'gofood',
            includeComparison: false
        );

        $kpiChannel = $this->service->getKpiSummary($filterChannel);
        $this->assertEquals(1, $kpiChannel->totalOrders);
        $this->assertEquals(120000.0, $kpiChannel->netSales);
    }

    /**
     * Test 5: Verifikasi pemanggilan sub-laporan (Product, Category, Cashier, Outlet, Payment, Channel).
     */
    public function test_sub_report_aggregations_return_expected_structures(): void
    {
        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Latte',
            'code' => 'KOP-01',
            'price' => 25000,
            'cost_price' => 10000,
            'type' => 'goods',
            'is_active' => true,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'POS-SUB-01',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'pos_direct',
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'total_amount' => 45000,
            'total_hpp_cost' => 20000,
            'total_gross_profit' => 25000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'unit_price' => 25000,
            'unit_cost_hpp' => 10000,
            'quantity' => 2,
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'total_price' => 45000,
            'total_hpp' => 20000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 45000,
            'fee_amount' => 315,
            'net_amount' => 44685,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            includeComparison: false
        );

        $products = $this->service->getProductPerformance($filter);
        $this->assertNotEmpty($products);
        $this->assertEquals('Kopi Latte', $products->first()->product_name);
        $this->assertEquals(45000.0, (float) $products->first()->net_sales);

        $categories = $this->service->getCategoryPerformance($filter);
        $this->assertNotEmpty($categories);
        $this->assertEquals('Minuman', $categories->first()->category_name);
        $this->assertEquals(100.0, (float) $categories->first()->contribution_percent);

        $cashiers = $this->service->getCashierPerformance($filter);
        $this->assertNotEmpty($cashiers);
        $this->assertEquals('Budi Kasir', $cashiers->first()->cashier_name);

        $payments = $this->service->getPaymentMethodBreakdown($filter);
        $this->assertNotEmpty($payments);
        $this->assertEquals('qris', $payments->first()->payment_method);
        $this->assertEquals(45000.0, (float) $payments->first()->total_amount);
        $this->assertEquals(315.0, (float) $payments->first()->total_fee);

        $channels = $this->service->getSalesChannelBreakdown($filter);
        $this->assertNotEmpty($channels);
        $this->assertEquals('pos_direct', $channels->first()->channel_code);
        $this->assertEquals('Kasir Langsung (POS Direct)', $channels->first()->channel_name);
    }

    /**
     * Test 6: Verifikasi Engine Finansial Online Food Delivery F&B (ShopeeFood, GoFood, GrabFood, Storefront, POS Direct).
     */
    public function test_online_food_delivery_financial_engine_calculations_for_fnb(): void
    {
        // 1. ShopeeFood: Net Sales 90.000 (100k - 10k discount), HPP 40.000. Fee 20% = 18.000, Payout = 72.000, Real Profit = 32.000
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-SF-01',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'shopeefood',
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'total_amount' => 90000,
            'total_hpp_cost' => 40000,
            'total_gross_profit' => 50000,
        ]);

        // 2. GoFood: Net Sales 200.000, HPP 80.000. Fee 20% = 40.000, Payout = 160.000, Real Profit = 80.000
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-GF-01',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'gofood',
            'subtotal' => 200000,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'total_hpp_cost' => 80000,
            'total_gross_profit' => 120000,
        ]);

        // 3. GrabFood: Net Sales 100.000, HPP 50.000. Fee 25% = 25.000, Payout = 75.000, Real Profit = 25.000
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-GRB-01',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'grabfood',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'total_hpp_cost' => 50000,
            'total_gross_profit' => 50000,
        ]);

        // 4. Toko Online Storefront: Net Sales 100.000, HPP 40.000. Fee 0% = 0, Payout = 100.000, Real Profit = 60.000
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-STR-01',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'storefront',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'total_hpp_cost' => 40000,
            'total_gross_profit' => 60000,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today()->startOfDay(),
            endDate: Carbon::today()->endOfDay(),
            includeComparison: false
        );

        $channels = $this->service->getSalesChannelBreakdown($filter)->keyBy('channel_code');

        // Verify ShopeeFood calculations
        $sf = $channels->get('shopeefood');
        $this->assertNotNull($sf);
        $this->assertEquals('ShopeeFood', $sf->channel_name);
        $this->assertEquals('online_delivery', $sf->channel_type);
        $this->assertEquals(100000.0, (float) $sf->gross_sales);
        $this->assertEquals(10000.0, (float) $sf->total_discount);
        $this->assertEquals(90000.0, (float) $sf->net_sales);
        $this->assertEquals(20.0, (float) $sf->platform_fee_percent);
        $this->assertEquals(18000.0, (float) $sf->platform_fee_amount);
        $this->assertEquals(72000.0, (float) $sf->net_merchant_payout);
        $this->assertEquals(40000.0, (float) $sf->total_hpp);
        $this->assertEquals(32000.0, (float) $sf->real_gross_profit);
        $this->assertEquals(round((32000.0 / 72000.0) * 100, 2), round((float) $sf->real_margin_percent, 2));
        $this->assertEquals('#EE4D2D', $sf->badge_bg);

        // Verify GoFood calculations
        $gf = $channels->get('gofood');
        $this->assertNotNull($gf);
        $this->assertEquals('GoFood (GoBiz)', $gf->channel_name);
        $this->assertEquals(200000.0, (float) $gf->net_sales);
        $this->assertEquals(20.0, (float) $gf->platform_fee_percent);
        $this->assertEquals(40000.0, (float) $gf->platform_fee_amount);
        $this->assertEquals(160000.0, (float) $gf->net_merchant_payout);
        $this->assertEquals(80000.0, (float) $gf->real_gross_profit);
        $this->assertEquals(50.0, (float) $gf->real_margin_percent);
        $this->assertEquals('#EE2724', $gf->badge_bg);

        // Verify GrabFood calculations
        $grb = $channels->get('grabfood');
        $this->assertNotNull($grb);
        $this->assertEquals('GrabFood', $grb->channel_name);
        $this->assertEquals(100000.0, (float) $grb->net_sales);
        $this->assertEquals(25.0, (float) $grb->platform_fee_percent);
        $this->assertEquals(25000.0, (float) $grb->platform_fee_amount);
        $this->assertEquals(75000.0, (float) $grb->net_merchant_payout);
        $this->assertEquals(25000.0, (float) $grb->real_gross_profit);
        $this->assertEquals(round((25000.0 / 75000.0) * 100, 2), round((float) $grb->real_margin_percent, 2));
        $this->assertEquals('#00B14F', $grb->badge_bg);

        // Verify Storefront calculations (0% fee)
        $str = $channels->get('storefront');
        $this->assertNotNull($str);
        $this->assertEquals('Toko Online Storefront', $str->channel_name);
        $this->assertEquals(0.0, (float) $str->platform_fee_percent);
        $this->assertEquals(0.0, (float) $str->platform_fee_amount);
        $this->assertEquals(100000.0, (float) $str->net_merchant_payout);
        $this->assertEquals(60000.0, (float) $str->real_gross_profit);
        $this->assertEquals(60.0, (float) $str->real_margin_percent);
        $this->assertEquals('#007AFF', $str->badge_bg);
    }
}
