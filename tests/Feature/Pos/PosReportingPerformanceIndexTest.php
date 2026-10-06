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
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosReportingPerformanceIndexTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private PosReportingService $reportingService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->business = Business::create([
            'name' => 'Resto Berkah Performance',
            'slug' => 'resto-berkah-perf-' . Str::random(5),
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Owner Performance',
            'email' => 'owner_perf_' . Str::random(5) . '@cooca.test',
            'password' => Hash::make('Password123!'),
        ]);
        $this->user->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->business->id])->save();

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'code' => 'OUT-PUSAT',
            'is_active' => true,
        ]);

        $this->reportingService = new PosReportingService();
    }

    public function test_composite_indexes_exist_on_database_tables(): void
    {
        // 1. pos_orders table indexes
        $ordersIndexNames = array_column(Schema::getIndexes('pos_orders'), 'name');
        $this->assertContains('pos_orders_biz_status_date_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_loc_date_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_user_date_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_shift_status_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_cust_date_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_type_date_idx', $ordersIndexNames);
        $this->assertContains('pos_orders_biz_channel_date_idx', $ordersIndexNames);

        // 2. pos_order_items table indexes
        $itemsIndexNames = array_column(Schema::getIndexes('pos_order_items'), 'name');
        $this->assertContains('pos_order_items_order_prod_idx', $itemsIndexNames);

        // 3. pos_order_payments table indexes
        $paymentsIndexNames = array_column(Schema::getIndexes('pos_order_payments'), 'name');
        $this->assertContains('pos_order_payments_order_method_idx', $paymentsIndexNames);

        // 4. pos_shifts table indexes
        $shiftsIndexNames = array_column(Schema::getIndexes('pos_shifts'), 'name');
        $this->assertContains('pos_shifts_biz_status_opened_idx', $shiftsIndexNames);
        $this->assertContains('pos_shifts_biz_user_opened_idx', $shiftsIndexNames);

        // 5. sales_returns table indexes
        $returnsIndexNames = array_column(Schema::getIndexes('sales_returns'), 'name');
        $this->assertContains('sales_returns_biz_status_date_idx', $returnsIndexNames);
    }

    public function test_reporting_queries_execute_performantly_with_composite_indexes(): void
    {
        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi',
            'slug' => 'cat-kopi',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Arabika',
            'code' => 'KOP-001',
            'buy_price' => 10000,
            'sell_price' => 25000,
            'is_active' => true,
        ]);

        $register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir 01',
            'code' => 'REG-01',
            'is_active' => true,
        ]);

        $shift = PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $register->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'status' => 'closed',
            'opening_cash' => 100000,
            'closing_cash_actual' => 600000,
            'opened_at' => Carbon::parse('2026-10-01 08:00:00'),
            'closed_at' => Carbon::parse('2026-10-01 17:00:00'),
        ]);

        // Generate sample batch of orders
        for ($i = 0; $i < 10; $i++) {
            $order = PosOrder::create([
                'business_id' => $this->business->id,
                'location_id' => $this->location->id,
                'pos_shift_id' => $shift->id,
                'user_id' => $this->user->id,
                'order_number' => 'ORD-PERF-' . $i . '-' . Str::random(4),
                'order_date' => '2026-10-01',
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'subtotal' => 50000,
                'discount_amount' => 5000,
                'tax_amount' => 4500,
                'service_charge_amount' => 0,
                'total_amount' => 49500,
                'paid_amount' => 50000,
                'change_amount' => 500,
                'total_hpp_cost' => 20000,
                'total_gross_profit' => 25000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => 25000,
                'unit_cost_hpp' => 10000,
                'quantity' => 2,
                'subtotal' => 50000,
                'total_price' => 45000,
                'total_hpp' => 20000,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => 'qris',
                'amount' => 49500,
                'status' => 'success',
            ]);
        }

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::parse('2026-10-01'),
            endDate: Carbon::parse('2026-10-01'),
            locationId: $this->location->id,
            userId: $this->user->id
        );

        $startTime = microtime(true);

        $kpis = $this->reportingService->getKpiSummary($filter);
        $products = $this->reportingService->getProductPerformance($filter);
        $cashiers = $this->reportingService->getCashierPerformance($filter);
        $payments = $this->reportingService->getPaymentMethodBreakdown($filter);

        $durationMs = (microtime(true) - $startTime) * 1000;

        $this->assertEquals(10, $kpis->totalOrders);
        $this->assertEquals(500000, $kpis->grossSales);
        $this->assertNotEmpty($products);
        $this->assertNotEmpty($cashiers);
        $this->assertNotEmpty($payments);

        // Execution must be fast (< 150ms)
        $this->assertLessThan(150, $durationMs, "Reporting queries took {$durationMs}ms, which exceeded 150ms limit");
    }
}
