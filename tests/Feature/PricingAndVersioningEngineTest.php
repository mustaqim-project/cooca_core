<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Calculation\CostingResultService;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Domain\Calculation\HppEngine;
use App\Domain\Calculation\RoundingService;
use App\Domain\Pricing\PricingEngine;
use App\Domain\Versioning\ApprovalWorkflowService;
use App\Domain\Versioning\ProductCostVersionService;
use App\Models\Business;
use App\Models\CostingRun;
use App\Models\CostModel;
use App\Models\Fee;
use App\Models\Product;
use App\Models\ProductCostVersion;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class PricingAndVersioningEngineTest extends TestCase
{
    use RefreshDatabase;

    private PricingEngine $pricingEngine;

    private RoundingService $roundingService;

    private HppEngine $hppEngine;

    private ProductCostVersionService $versionService;

    private ApprovalWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->pricingEngine = new PricingEngine;
        $this->roundingService = new RoundingService;
        $this->hppEngine = new HppEngine;
        $this->versionService = new ProductCostVersionService;
        $this->workflowService = new ApprovalWorkflowService;
    }

    public function test_pricing_engine_markup_vs_margin_vs_target_profit(): void
    {
        $hpp = 60000.0;

        // 1. Markup 40%: 60,000 * 1.40 = 84,000 (§22 Blueprint)
        $markup = $this->pricingEngine->fromMarkup($hpp, 40.0);
        $this->assertEqualsWithDelta(84000.0, $markup['selling_price'], 0.01);
        $this->assertEqualsWithDelta(24000.0, $markup['gross_profit'], 0.01);
        $this->assertEqualsWithDelta(28.57, $markup['margin_percentage'], 0.01);

        // 2. Margin 40%: 60,000 / (1 - 0.40) = 100,000 (§22 Blueprint)
        $margin = $this->pricingEngine->fromMargin($hpp, 40.0);
        $this->assertEqualsWithDelta(100000.0, $margin['selling_price'], 0.01);
        $this->assertEqualsWithDelta(40000.0, $margin['gross_profit'], 0.01);
        $this->assertEqualsWithDelta(66.67, $margin['markup_percentage'], 0.01);

        // 3. Target Profit Rp 25,000: 60,000 + 25,000 = 85,000
        $target = $this->pricingEngine->fromTargetProfit($hpp, 25000.0);
        $this->assertEqualsWithDelta(85000.0, $target['selling_price'], 0.01);
        $this->assertEqualsWithDelta(25000.0, $target['gross_profit'], 0.01);
    }

    public function test_rounding_strategies(): void
    {
        $biz = Business::create(['name' => 'Toko Rounding', 'rounding_strategy' => RoundingService::STRATEGY_ROUND_100]);

        // Round nearest 100: 12,430 -> 12,400 | 12,470 -> 12,500
        $this->assertEqualsWithDelta(12400.0, $this->roundingService->apply(12430.0, $biz), 0.01);
        $this->assertEqualsWithDelta(12500.0, $this->roundingService->apply(12470.0, $biz), 0.01);

        // Round nearest 500
        $biz->update(['rounding_strategy' => RoundingService::STRATEGY_ROUND_500]);
        $this->assertEqualsWithDelta(12500.0, $this->roundingService->apply(12350.0, $biz), 0.01);

        // Round nearest 1000
        $biz->update(['rounding_strategy' => RoundingService::STRATEGY_ROUND_1000]);
        $this->assertEqualsWithDelta(13000.0, $this->roundingService->apply(12600.0, $biz), 0.01);
    }

    public function test_hpp_engine_output_basis_aggregation(): void
    {
        $dto = new CostingResultDTO(
            costModelId: (string) Str::uuid(),
            method: 'simple',
            totalMaterialCost: 100000,
            totalLaborCost: 20000,
            totalMachineCost: 0,
            totalOverheadCost: 0,
            totalHpp: 120000,
            hppPerUnit: 120000
        );

        // Planned basis: Total 120k / 10 planned units = 12,000 / unit (§15)
        $planned = $this->hppEngine->aggregate($dto, CostModel::BASIS_PLANNED, ['planned_output' => 10]);
        $this->assertEqualsWithDelta(12000.0, $planned['hpp_per_unit'], 0.01);

        // Actual basis: Total 120k / 8 actual units = 15,000 / unit
        $actual = $this->hppEngine->aggregate($dto, CostModel::BASIS_ACTUAL, ['actual_output' => 8]);
        $this->assertEqualsWithDelta(15000.0, $actual['hpp_per_unit'], 0.01);

        // Sellable basis: Actual 8 - Defect 2 = 6 sellable units -> 120k / 6 = 20,000 / unit
        $sellable = $this->hppEngine->aggregate($dto, CostModel::BASIS_SELLABLE, ['actual_output' => 8, 'defect_quantity' => 2]);
        $this->assertEqualsWithDelta(20000.0, $sellable['hpp_per_unit'], 0.01);
    }

    public function test_fee_deduction_and_net_revenue(): void
    {
        $biz = Business::create(['name' => 'Marketplace Merchant']);
        $fee10pct = Fee::create([
            'business_id' => $biz->id,
            'name' => 'Shopee Fee',
            'type' => Fee::TYPE_PRICE_DEDUCTION,
            'fee_type' => Fee::FEE_TYPE_PERCENTAGE,
            'fee_value' => 10.0,
        ]);

        $feeFixed = Fee::create([
            'business_id' => $biz->id,
            'name' => 'Payment Gateway Fee',
            'type' => Fee::TYPE_PRICE_DEDUCTION,
            'fee_type' => Fee::FEE_TYPE_FIXED,
            'fee_value' => 2000.0,
        ]);

        // Selling Price: 100,000. Deductions: 10k + 2k = 12k -> Net Revenue = 88,000. HPP = 60,000 -> Net Profit = 28,000 (§21)
        $net = $this->pricingEngine->netRevenue(100000.0, 60000.0, [$fee10pct, $feeFixed]);
        $this->assertEqualsWithDelta(12000.0, $net['total_deductions'], 0.01);
        $this->assertEqualsWithDelta(88000.0, $net['net_revenue'], 0.01);
        $this->assertEqualsWithDelta(28000.0, $net['net_profit'], 0.01);
    }

    public function test_costing_runs_persistence(): void
    {
        $biz = Business::create(['name' => 'Pabrik Run']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Barang Run', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Run CM', 'method' => CostModel::METHOD_SIMPLE]);

        $dto = new CostingResultDTO(
            costModelId: $costModel->id,
            method: 'simple',
            totalMaterialCost: 50000,
            totalLaborCost: 20000,
            totalMachineCost: 5000,
            totalOverheadCost: 10000,
            totalHpp: 85000,
            hppPerUnit: 85000
        );

        $persistService = new CostingResultService;
        $run = $persistService->persist($costModel, $dto, CostingRun::RUN_TYPE_MANUAL);

        $this->assertDatabaseHas('costing_runs', ['id' => $run->id, 'status' => 'completed']);
        $this->assertDatabaseHas('costing_results', ['costing_run_id' => $run->id, 'total_hpp' => 85000]);
        $this->assertEquals(4, $run->result->items()->count());
    }

    public function test_product_cost_versioning_and_approval_workflow(): void
    {
        $user = User::create(['name' => 'Approver', 'email' => 'appr@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Bisnis Versioning']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Kue Tart', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Tart CM', 'method' => CostModel::METHOD_SIMPLE]);

        $dto = new CostingResultDTO($costModel->id, 'simple', 50000, 15000, 0, 0, 65000, 65000);

        // 1. Create Version 1 (Draft)
        $v1 = $this->versionService->createVersion($product, $costModel, $dto, 'v1.0.0');
        $this->assertEquals(1, $v1->version_number);
        $this->assertEquals(ProductCostVersion::STATUS_DRAFT, $v1->status);

        // 2. Transition: draft -> in_review -> approved -> published
        $v1 = $this->workflowService->transition($v1, ProductCostVersion::STATUS_IN_REVIEW);
        $this->assertEquals(ProductCostVersion::STATUS_IN_REVIEW, $v1->status);

        $v1 = $this->workflowService->transition($v1, ProductCostVersion::STATUS_APPROVED);
        $this->assertEquals(ProductCostVersion::STATUS_APPROVED, $v1->status);

        $v1 = $this->workflowService->transition($v1, ProductCostVersion::STATUS_PUBLISHED);
        $this->assertEquals(ProductCostVersion::STATUS_PUBLISHED, $v1->status);

        // 3. Immutability check (§76): attempting to modify approved/published snapshot throws exception
        $this->expectException(InvalidArgumentException::class);
        $v1->update(['total_hpp' => 999999]);
    }

    public function test_api_pricing_calculate_endpoint(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'pricing_api@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Toko Pricing']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        // Margin 50% from HPP 50,000 -> Selling Price Rp 100,000
        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/pricing/calculate', [
                'hpp' => 50000,
                'strategy' => 'margin',
                'percentage_or_value' => 50,
            ]);

        $response->assertOk();
        $this->assertEqualsWithDelta(100000.0, (float) $response->json('pricing.selling_price'), 0.01);
    }
}
