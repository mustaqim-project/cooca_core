<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Profitability\BepEngine;
use App\Domain\Profitability\ProfitabilityEngine;
use App\Domain\Simulation\SimulationEngine;
use App\Domain\Variance\VarianceEngine;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\LaborRate;
use App\Models\Material;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProfitabilityVarianceAndSimulationEngineTest extends TestCase
{
    use RefreshDatabase;

    private ProfitabilityEngine $profitabilityEngine;

    private BepEngine $bepEngine;

    private VarianceEngine $varianceEngine;

    private SimulationEngine $simulationEngine;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->profitabilityEngine = new ProfitabilityEngine;
        $this->bepEngine = new BepEngine;
        $this->varianceEngine = new VarianceEngine;
        $this->simulationEngine = new SimulationEngine;
    }

    public function test_profitability_engine_metrics(): void
    {
        // Selling Price: 100,000, HPP: 60,000, Variable Cost: 50,000, Units: 10
        $result = $this->profitabilityEngine->analyze(100000.0, 60000.0, 50000.0, 10.0);

        // Revenue: 1,000,000, COGS: 600,000 -> Gross Profit: 400,000, Margin: 40% (§25)
        $this->assertEqualsWithDelta(1000000.0, $result['total_revenue'], 0.01);
        $this->assertEqualsWithDelta(400000.0, $result['gross_profit'], 0.01);
        $this->assertEqualsWithDelta(40.0, $result['gross_margin_percentage'], 0.01);

        // Unit CM: 100k - 50k = 50k (50% CM Ratio)
        $this->assertEqualsWithDelta(50000.0, $result['unit_contribution_margin'], 0.01);
        $this->assertEqualsWithDelta(50.0, $result['contribution_margin_ratio'], 0.01);
        $this->assertEqualsWithDelta(500000.0, $result['total_contribution_margin'], 0.01);
    }

    public function test_bep_engine_unit_and_revenue_break_even(): void
    {
        // Fixed Cost: Rp 10,000,000, Selling Price: Rp 100,000, Variable Cost: Rp 50,000 (§24)
        // Unit CM = Rp 50,000.
        // BEP Units = 10,000,000 / 50,000 = 200 units.
        // BEP Revenue = 10,000,000 / 0.50 = Rp 20,000,000.
        $analysis = $this->bepEngine->analyze(10000000.0, 100000.0, 50000.0, 300.0);

        $this->assertEqualsWithDelta(200.0, $analysis['bep_units'], 0.01);
        $this->assertEqualsWithDelta(20000000.0, $analysis['bep_revenue'], 0.01);

        // Safety Margin with 300 expected units: 300 - 200 = 100 units (Rp 10,000,000 revenue)
        $this->assertEqualsWithDelta(100.0, $analysis['safety_margin_units'], 0.01);
        $this->assertEqualsWithDelta(10000000.0, $analysis['safety_margin_revenue'], 0.01);
    }

    public function test_variance_engine_material_and_labor_separation(): void
    {
        // 1. Material Price Variance (§29):
        // Standard Price = 10,000/kg, Actual Price = 12,000/kg, Actual Quantity = 100 kg
        // Variance = (12,000 - 10,000) * 100 = Rp 200,000 (Unfavorable)
        $matPriceVar = $this->varianceEngine->materialPriceVariance(12000.0, 10000.0, 100.0);
        $this->assertEqualsWithDelta(200000.0, $matPriceVar['amount'], 0.01);
        $this->assertEquals('unfavorable', $matPriceVar['nature']);

        // 2. Material Quantity Variance (§29):
        // Standard Quantity = 90 kg, Actual Quantity = 100 kg, Standard Price = 10,000
        // Variance = (100 - 90) * 10,000 = Rp 100,000 (Unfavorable)
        $matQtyVar = $this->varianceEngine->materialQuantityVariance(100.0, 90.0, 10000.0);
        $this->assertEqualsWithDelta(100000.0, $matQtyVar['amount'], 0.01);
        $this->assertEquals('unfavorable', $matQtyVar['nature']);

        // 3. Labor Rate Variance:
        // Std Rate = 30,000, Actual Rate = 28,000, Actual Hours = 10 hrs
        // Variance = (28,000 - 30,000) * 10 = -Rp 20,000 (Favorable)
        $labRateVar = $this->varianceEngine->laborRateVariance(28000.0, 30000.0, 10.0);
        $this->assertEqualsWithDelta(-20000.0, $labRateVar['amount'], 0.01);
        $this->assertEquals('favorable', $labRateVar['nature']);

        // 4. Root-cause ranking (§87)
        $ranked = $this->varianceEngine->rankContributors([
            'Labor Rate' => $labRateVar,
            'Material Price' => $matPriceVar,
            'Material Quantity' => $matQtyVar,
        ]);

        $this->assertEquals('Material Price', $ranked[0]['name']);
        $this->assertEquals('Material Quantity', $ranked[1]['name']);
        $this->assertEquals('Labor Rate', $ranked[2]['name']);
    }

    public function test_what_if_simulation_engine_sandboxing(): void
    {
        $biz = Business::create(['name' => 'Pabrik Simulasi']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Produk Sim', 'output_unit_id' => $pcs->id]);

        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Model Sim', 'method' => CostModel::METHOD_SIMPLE]);

        // Direct labor = Rp 50,000
        $rate = LaborRate::create(['business_id' => $biz->id, 'name' => 'Labor Sim', 'basis' => LaborRate::BASIS_HOURLY, 'rate_amount' => 50000]);
        $costModel->labors()->create(['labor_rate_id' => $rate->id, 'quantity' => 1]);

        // Run simulation: Labor cost increase +20% -> 50,000 * 1.20 = 60,000 (§23)
        $simulation = $this->simulationEngine->run($costModel, [
            'labor_change_pct' => 20.0,
            'target_markup_pct' => 50.0,
        ]);

        $this->assertEqualsWithDelta(50000.0, $simulation['baseline']['total_hpp'], 0.01);
        $this->assertEqualsWithDelta(60000.0, $simulation['simulated']['total_hpp'], 0.01);
        $this->assertEqualsWithDelta(10000.0, $simulation['impact']['delta_hpp_amount'], 0.01);
        $this->assertEqualsWithDelta(20.0, $simulation['impact']['delta_hpp_percentage'], 0.01);

        // Verify no costing results or production data were mutated in DB!
        $this->assertDatabaseCount('costing_results', 0);
    }

    public function test_api_profitability_and_bep_endpoints(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'prof_api@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Prof']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        // 1. Profitability API
        $resProf = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/profitability/analyze', [
                'selling_price' => 50000,
                'hpp_per_unit' => 30000,
                'variable_cost_per_unit' => 25000,
                'units_sold' => 100,
            ])
            ->assertOk();

        $this->assertEqualsWithDelta(2000000.0, (float) $resProf->json('profitability.gross_profit'), 0.01);

        // 2. BEP API
        $resBep = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/bep/calculate', [
                'total_fixed_cost' => 5000000,
                'selling_price' => 50000,
                'variable_cost_per_unit' => 25000,
            ])
            ->assertOk();

        $this->assertEqualsWithDelta(200.0, (float) $resBep->json('bep_analysis.bep_units'), 0.01);
    }
}
