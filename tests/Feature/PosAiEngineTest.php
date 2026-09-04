<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosAiEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Product $product1;
    private Product $product2;
    private Customer $customer;
    private AiSalesAnalysisService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Manager AI',
            'email' => 'manager_ai@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap AI POS',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        \App\Models\BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => \App\Models\BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 1000,
            'ai_tokens_remaining' => 1000,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Flagship',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Signature Coffee',
            'slug' => 'signature-coffee',
        ]);

        // Product 1: High Margin (HPP 10.000, Jual 35.000 -> Margin 71.4%)
        $this->product1 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'code' => 'SIG-01',
            'name' => 'Kopi Susu Aren Spesial',
            'slug' => 'kopi-susu-aren-spesial',
            'base_cost' => 10000,
            'selling_price' => 35000,
            'is_active' => true,
        ]);

        // Product 2: Low Margin (HPP 22.000, Jual 25.000 -> Margin 12%)
        $this->product2 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'code' => 'SIG-02',
            'name' => 'Croissant Almond',
            'slug' => 'croissant-almond',
            'base_cost' => 22000,
            'selling_price' => 25000,
            'is_active' => true,
        ]);

        // Stocks: 15 cups of Kopi, 40 Croissants
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product1->id,
            'quantity' => 15,
            'last_cost' => 10000,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product2->id,
            'quantity' => 40,
            'last_cost' => 22000,
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Dewi Lestari',
            'phone' => '081987654321',
            'membership_tier' => 'gold',
            'points_balance' => 350,
        ]);

        // Seed 10 historical completed orders
        for ($day = 1; $day <= 7; $day++) {
            $orderDate = Carbon::today()->subDays($day)->toDateString();
            $order = PosOrder::create([
                'business_id' => $this->business->id,
                'location_id' => $this->location->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'order_number' => "ORD-AI-{$day}",
                'order_date' => $orderDate,
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'dine_in',
                'subtotal' => 60000,
                'total_amount' => 60000,
                'paid_amount' => 60000,
                'change_amount' => 0,
                'total_hpp_cost' => 32000,
                'total_gross_profit' => 28000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_id' => $this->product1->id,
                'product_name' => $this->product1->name,
                'unit_price' => 35000,
                'unit_cost_hpp' => 10000,
                'quantity' => 1,
                'subtotal' => 35000,
                'total_price' => 35000,
                'total_hpp' => 10000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_id' => $this->product2->id,
                'product_name' => $this->product2->name,
                'unit_price' => 25000,
                'unit_cost_hpp' => 22000,
                'quantity' => 1,
                'subtotal' => 25000,
                'total_price' => 25000,
                'total_hpp' => 22000,
            ]);
        }

        $this->aiService = new AiSalesAnalysisService;
    }

    public function test_ai_pos_cockpit_page_is_accessible(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('pos.ai.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Intelijensi');
        $response->assertSee('Peramalan Penjualan');
        $response->assertSee('Menu Engineering');
    }

    public function test_ai_sales_forecasting_generates_projections_and_seasonality(): void
    {
        $forecast = $this->aiService->getSalesForecasting($this->business, 14);

        $this->assertIsArray($forecast);
        $this->assertCount(14, $forecast['forecast']);
        $this->assertGreaterThan(0, $forecast['total_forecast_revenue']);
        $this->assertNotEmpty($forecast['trend_direction']);
        $this->assertNotEmpty($forecast['peak_projected_day']);

        // Check forecast daily structure
        $firstDay = $forecast['forecast'][0];
        $this->assertArrayHasKey('predicted_revenue', $firstDay);
        $this->assertArrayHasKey('upper_bound', $firstDay);
        $this->assertArrayHasKey('lower_bound', $firstDay);
        $this->assertGreaterThanOrEqual($firstDay['lower_bound'], $firstDay['upper_bound']);
    }

    public function test_ai_stock_prediction_and_reorder_calculation(): void
    {
        $stockReport = $this->aiService->getStockPredictionAndReorder($this->business);

        $this->assertIsArray($stockReport);
        $this->assertGreaterThanOrEqual(2, $stockReport['total_analyzed']);

        // Find product1
        $p1 = collect($stockReport['products'])->firstWhere('product_id', $this->product1->id);
        $this->assertNotNull($p1);
        $this->assertGreaterThan(0, $p1['daily_velocity']);
        $this->assertIsNumeric($p1['runout_days']);
        $this->assertGreaterThan(0, $p1['suggested_reorder_qty']);
    }

    public function test_ai_anomaly_and_fraud_detection(): void
    {
        // Add 1 extreme outlier order: Rp 5.000.000 (normal is ~60.000)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-OUTLIER-999',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'takeaway',
            'subtotal' => 5000000,
            'total_amount' => 5000000,
            'paid_amount' => 5000000,
            'change_amount' => 0,
            'total_hpp_cost' => 1000000,
            'total_gross_profit' => 4000000,
        ]);

        $anomalies = $this->aiService->detectAnomaliesAndFraud($this->business);

        $this->assertIsArray($anomalies);
        $this->assertGreaterThan(0, $anomalies['total_alerts']);

        $outlierAlert = collect($anomalies['alerts'])->firstWhere('type', 'unusual_order_value');
        $this->assertNotNull($outlierAlert);
    }

    public function test_ai_product_profitability_bcg_matrix(): void
    {
        $matrix = $this->aiService->getProductProfitabilityMatrix($this->business);

        $this->assertIsArray($matrix);
        $this->assertArrayHasKey('stars', $matrix);
        $this->assertArrayHasKey('plowhorses', $matrix);
        $this->assertArrayHasKey('puzzles', $matrix);
        $this->assertArrayHasKey('dogs', $matrix);

        // All 2 products must be categorized into one of the 4 quadrants
        $totalClassified = count($matrix['stars']) + count($matrix['plowhorses']) + count($matrix['puzzles']) + count($matrix['dogs']);
        $this->assertEquals(2, $totalClassified);
    }

    public function test_ai_smart_pricing_and_promo_recommendations(): void
    {
        // Product 2 has only 12% margin, so it should be flagged for dynamic pricing
        $pricing = $this->aiService->getSmartPricingRecommendations($this->business);

        $this->assertNotEmpty($pricing);
        $flagged = collect($pricing)->firstWhere('product_id', $this->product2->id);
        $this->assertNotNull($flagged);
        $this->assertGreaterThan(25000, $flagged['recommended_price']);

        // Bundling: Product 1 and Product 2 were bought together 7 times
        $bundles = $this->aiService->getSmartPromoRecommendations($this->business);
        $this->assertNotEmpty($bundles);
        $this->assertEquals(7, $bundles[0]['frequency_co_purchased']);
    }

    public function test_ai_customer_behavior_rfm_segmentation(): void
    {
        $rfm = $this->aiService->getCustomerBehaviorRfm($this->business);

        $this->assertIsArray($rfm);
        $this->assertGreaterThanOrEqual(1, $rfm['summary']['champions_count'] + $rfm['summary']['loyal_count']);
    }

    public function test_ai_natural_language_reporting_indonesian_queries(): void
    {
        $this->actingAs($this->user);

        // 1. Ask about today's sales
        $resToday = $this->postJson(route('pos.ai.ask'), ['query' => 'Berapa penjualan hari ini?']);
        $resToday->assertStatus(200);
        $resToday->assertJsonPath('success', true);
        $this->assertNotEmpty($resToday->json('response.headline'));

        // 2. Ask about stock prediction
        $resStock = $this->postJson(route('pos.ai.ask'), ['query' => 'Produk apa yang stoknya mau habis?']);
        $resStock->assertStatus(200);
        $resStock->assertJsonPath('success', true);
        $this->assertNotEmpty($resStock->json('response.details'));

        // 3. Ask about fraud & suspicious transactions
        $resFraud = $this->postJson(route('pos.ai.ask'), ['query' => 'Apakah ada transaksi mencurigakan atau fraud?']);
        $resFraud->assertStatus(200);
        $resFraud->assertJsonPath('success', true);
        $this->assertNotEmpty($resFraud->json('response.action_suggestion'));
    }
}
