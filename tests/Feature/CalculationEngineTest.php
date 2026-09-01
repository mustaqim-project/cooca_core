<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Calculation\CalculationEngine;
use App\Domain\Formula\FormulaAstBuilder;
use App\Domain\Formula\FormulaDependencyGraph;
use App\Domain\Formula\FormulaEvaluator;
use App\Domain\Formula\FormulaTokenizer;
use App\Models\AllocationRule;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostDriver;
use App\Models\CostModel;
use App\Models\CostModelLabor;
use App\Models\CostModelMachine;
use App\Models\CostPool;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class CalculationEngineTest extends TestCase
{
    use RefreshDatabase;

    private FormulaTokenizer $tokenizer;

    private FormulaAstBuilder $astBuilder;

    private FormulaEvaluator $evaluator;

    private FormulaDependencyGraph $dependencyGraph;

    private CalculationEngine $calculationEngine;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->tokenizer = new FormulaTokenizer;
        $this->astBuilder = new FormulaAstBuilder;
        $this->evaluator = new FormulaEvaluator;
        $this->dependencyGraph = new FormulaDependencyGraph;
        $this->calculationEngine = new CalculationEngine;
    }

    public function test_formula_tokenizer_and_operator_precedence(): void
    {
        // 1. Expression: 10 + 20 * 2 = 50 (BODMAS/PEMDAS)
        $tokens = $this->tokenizer->tokenize('10 + 20 * 2');
        $parsed = $this->astBuilder->build($tokens);
        $result = $this->evaluator->evaluate($parsed['ast'], []);

        $this->assertEqualsWithDelta(50.0, $result, 0.001);

        // 2. Expression with Parentheses: (10 + 20) * 2 = 60
        $tokens2 = $this->tokenizer->tokenize('(10 + 20) * 2');
        $parsed2 = $this->astBuilder->build($tokens2);
        $result2 = $this->evaluator->evaluate($parsed2['ast'], []);

        $this->assertEqualsWithDelta(60.0, $result2, 0.001);
    }

    public function test_formula_security_rejects_malicious_input(): void
    {
        // System injection attempt
        $this->expectException(InvalidArgumentException::class);
        $this->tokenizer->tokenize("system('rm -rf') + 10");
    }

    public function test_formula_evaluator_with_variables(): void
    {
        $tokens = $this->tokenizer->tokenize('material + (labor * 1.5) + overhead');
        $parsed = $this->astBuilder->build($tokens);

        $variables = [
            'material' => 100.0,
            'labor' => 20.0,
            'overhead' => 30.0,
        ];

        // 100 + (20 * 1.5) + 30 = 160
        $result = $this->evaluator->evaluate($parsed['ast'], $variables);
        $this->assertEqualsWithDelta(160.0, $result, 0.001);
    }

    public function test_formula_division_by_zero_protection(): void
    {
        $tokens = $this->tokenizer->tokenize('100 / 0');
        $parsed = $this->astBuilder->build($tokens);

        $this->expectException(InvalidArgumentException::class);
        $this->evaluator->evaluate($parsed['ast'], []);
    }

    public function test_formula_dependency_graph_cycle_detection(): void
    {
        $graph = [
            'HPP' => ['prime_cost', 'overhead'],
            'prime_cost' => ['material', 'HPP'], // Circular!
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->dependencyGraph->detectCircular($graph);
    }

    public function test_calculation_engine_recipe_bom_strategy(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'calc_bom@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Burger Calc']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();
        $kg = Unit::where('code', 'kg')->firstOrFail();

        // 1. Material (100g Daging = 100g @ Rp 150/g = Rp 15.000)
        $daging = Material::create(['business_id' => $biz->id, 'name' => 'Daging Sapi', 'unit_id' => $kg->id]);
        MaterialPrice::create(['business_id' => $biz->id, 'material_id' => $daging->id, 'purchase_price' => 150000, 'purchase_unit_id' => $kg->id, 'effective_date' => '2026-01-01']);

        // 2. Product & Cost Model
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Burger Spesial', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Standar 2026', 'method' => CostModel::METHOD_RECIPE_BOM]);

        // 3. BOM (100g Daging = 15,000)
        $bom = BomHeader::create(['cost_model_id' => $costModel->id, 'name' => 'BOM Burger']);
        BomItem::create(['bom_header_id' => $bom->id, 'material_id' => $daging->id, 'unit_id' => $g->id, 'quantity' => 100]);

        // 4. Labor (0.2 jam @ Rp 30.000/hr = Rp 6.000)
        $chef = LaborRate::create(['business_id' => $biz->id, 'name' => 'Chef', 'basis' => LaborRate::BASIS_HOURLY, 'rate_amount' => 30000]);
        CostModelLabor::create(['cost_model_id' => $costModel->id, 'labor_rate_id' => $chef->id, 'quantity' => 0.2]);

        // 5. Machine (0.1 jam @ Rp 10.000/hr = Rp 1.000)
        $grill = Machine::create(['business_id' => $biz->id, 'name' => 'Grill', 'purchase_price' => 10000000, 'useful_life_hours' => 1000]);
        CostModelMachine::create(['cost_model_id' => $costModel->id, 'machine_id' => $grill->id, 'hours_used' => 0.1]);

        // 6. Overhead Allocation (Pool 5 Juta / 1000 unit = Rp 5.000 / unit)
        $pool = CostPool::create(['business_id' => $biz->id, 'name' => 'Operasional', 'manual_override_amount' => 5000000]);
        $driver = CostDriver::create(['business_id' => $biz->id, 'name' => 'Unit Driver', 'type' => CostDriver::TYPE_PER_UNIT]);
        AllocationRule::create(['business_id' => $biz->id, 'cost_pool_id' => $pool->id, 'cost_driver_id' => $driver->id, 'cost_model_id' => $costModel->id, 'total_driver_capacity' => 1000]);

        // Total HPP = Material (15k) + Labor (6k) + Machine (1k) + Overhead (5k) = Rp 27,000
        $result = $this->calculationEngine->calculate($costModel);

        $this->assertEqualsWithDelta(15000.0, $result->totalMaterialCost, 0.01);
        $this->assertEqualsWithDelta(6000.0, $result->totalLaborCost, 0.01);
        $this->assertEqualsWithDelta(1000.0, $result->totalMachineCost, 0.01);
        $this->assertEqualsWithDelta(5000.0, $result->totalOverheadCost, 0.01);
        $this->assertEqualsWithDelta(27000.0, $result->totalHpp, 0.01);
    }

    public function test_calculation_engine_custom_formula_strategy(): void
    {
        $biz = Business::create(['name' => 'Bisnis Custom']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        $product = Product::create(['business_id' => $biz->id, 'name' => 'Produk Custom', 'output_unit_id' => $pcs->id]);

        $tokens = $this->tokenizer->tokenize('prime_cost * 1.20 + 5000');
        $parsed = $this->astBuilder->build($tokens);

        $costModel = CostModel::create([
            'business_id' => $biz->id,
            'product_id' => $product->id,
            'name' => 'Custom Costing',
            'method' => CostModel::METHOD_CUSTOM,
            'formula_definition' => [
                'expression' => 'prime_cost * 1.20 + 5000',
                'ast' => $parsed['ast'],
                'variables' => $parsed['variables'],
            ],
        ]);

        // Labor = 10,000 -> prime_cost = 10,000
        $rate = LaborRate::create(['business_id' => $biz->id, 'name' => 'Staff', 'basis' => LaborRate::BASIS_HOURLY, 'rate_amount' => 10000]);
        CostModelLabor::create(['cost_model_id' => $costModel->id, 'labor_rate_id' => $rate->id, 'quantity' => 1]);

        // Formula: 10,000 * 1.20 + 5000 = 12,000 + 5,000 = 17,000
        $result = $this->calculationEngine->calculate($costModel);
        $this->assertEqualsWithDelta(17000.0, $result->totalHpp, 0.01);
    }

    public function test_api_dry_run_formula_evaluation(): void
    {
        $response = $this->postJson('/api/v1/formulas/evaluate', [
            'expression' => '(material + labor) * 1.10',
            'variables' => [
                'material' => 50000,
                'labor' => 20000,
            ],
        ]);

        $response->assertOk();
        $this->assertEqualsWithDelta(77000.0, (float) $response->json('result'), 0.01);
    }

    public function test_api_calculation_endpoint(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'api_calc@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Kafe API']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Kopi Latte', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Latte Simple', 'method' => CostModel::METHOD_SIMPLE]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/costing/calculate/{$costModel->slug}");

        $response->assertOk()
            ->assertJsonPath('result.cost_model_id', $costModel->id);
    }
}
