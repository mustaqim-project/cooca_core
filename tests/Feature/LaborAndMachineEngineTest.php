<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Labor\LaborCostService;
use App\Domain\Machine\MachineCostService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\CostModelLabor;
use App\Models\CostModelMachine;
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
use Tests\TestCase;

final class LaborAndMachineEngineTest extends TestCase
{
    use RefreshDatabase;

    private LaborCostService $laborService;

    private MachineCostService $machineService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->laborService = new LaborCostService;
        $this->machineService = new MachineCostService;
    }

    public function test_labor_rate_crud_and_monthly_to_hourly_conversion(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'labor_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Labor']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        // Monthly salary: Rp 4,000,000, 22 days, 8 hrs/day, 80% utilization = 140.8 productive hours
        $rateRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/labor-rates', [
                'name' => 'Koki Utama',
                'basis' => LaborRate::BASIS_MONTHLY,
                'rate_amount' => 4000000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_rate' => 80,
                'overtime_multiplier' => 1.5,
            ])
            ->assertCreated();

        $slug = $rateRes->json('labor_rate.slug');

        // Check hourly equivalent endpoint
        $eqRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->getJson("/api/v1/labor-rates/{$slug}/hourly-equivalent")
            ->assertOk();

        // 22 * 8 * 0.8 = 140.8 productive hours
        $this->assertEqualsWithDelta(140.8, (float) $eqRes->json('monthly_productive_hours'), 0.01);
        // 4,000,000 / 140.8 = 28409.09
        $this->assertEqualsWithDelta(28409.09, (float) $eqRes->json('hourly_rate_equivalent'), 0.01);
    }

    public function test_labor_assignment_with_overtime_multiplier(): void
    {
        $biz = Business::create(['name' => 'Workshop Bengkel']);
        $rate = LaborRate::create([
            'business_id' => $biz->id,
            'name' => 'Teknisi Bubut',
            'basis' => LaborRate::BASIS_HOURLY,
            'rate_amount' => 20000, // Rp 20.000 / hr
            'overtime_multiplier' => 1.5, // Lembur 1.5x = Rp 30.000 / hr
        ]);

        $item = new CostModelLabor([
            'quantity' => 3,
            'regular_hours' => 2,
            'overtime_hours' => 1,
        ]);
        $item->setRelation('laborRate', $rate);

        // (2 * 20,000) + (1 * 20,000 * 1.5) = 40,000 + 30,000 = 70,000
        $cost = $this->laborService->calculateLaborCost($item);
        $this->assertEqualsWithDelta(70000.0, $cost, 0.01);
    }

    public function test_machine_depreciation_and_operating_cost_per_hour(): void
    {
        $biz = Business::create(['name' => 'Pabrik Kopi']);
        $machine = Machine::create([
            'business_id' => $biz->id,
            'name' => 'Mesin Roasting Pro',
            'purchase_price' => 100000000, // 100 Juta
            'residual_value' => 10000000,   // 10 Juta (Depreciable = 90 Juta)
            'useful_life_hours' => 10000,   // 10.000 Jam -> Depresiasi = 9.000 / Jam
            'maintenance_cost_per_hour' => 2000,
            'electricity_cost_per_hour' => 4000,
        ]);

        // Total Cost/hr = 9000 + 2000 + 4000 = 15,000 / hr
        $depreciation = $this->machineService->depreciationPerHour($machine);
        $totalCostPerHour = $this->machineService->costPerHour($machine);

        $this->assertEqualsWithDelta(9000.0, $depreciation, 0.01);
        $this->assertEqualsWithDelta(15000.0, $totalCostPerHour, 0.01);

        // Machine assignment 2.5 hours = 2.5 * 15,000 = 37,500
        $item = new CostModelMachine([
            'hours_used' => 2.5,
        ]);
        $item->setRelation('machine', $machine);

        $this->assertEqualsWithDelta(37500.0, $this->machineService->calculateMachineCost($item), 0.01);
    }

    public function test_combined_cost_model_live_preview(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'preview_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Kafe Roastery']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $kg = Unit::where('code', 'kg')->firstOrFail();
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // 1. Material (1 kg Green Bean = Rp 100.000)
        $greenBean = Material::create(['business_id' => $biz->id, 'name' => 'Green Bean Arabika', 'unit_id' => $kg->id]);
        MaterialPrice::create(['business_id' => $biz->id, 'material_id' => $greenBean->id, 'purchase_price' => 100000, 'purchase_unit_id' => $kg->id, 'effective_date' => '2026-01-01']);

        // 2. Product & Cost Model
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Roasted Coffee 1kg', 'output_unit_id' => $pcs->id]);
        $cm = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Model Standar', 'method' => CostModel::METHOD_RECIPE_BOM]);

        // 3. BOM Header & 1 Item (1kg Green Bean = 100,000)
        $bom = BomHeader::create(['cost_model_id' => $cm->id, 'name' => 'BOM Roasted Coffee']);
        BomItem::create(['bom_header_id' => $bom->id, 'material_id' => $greenBean->id, 'unit_id' => $kg->id, 'quantity' => 1]);

        // 4. Labor (Roaster: 0.5 jam @ Rp 30.000/hr = Rp 15.000)
        $rate = LaborRate::create(['business_id' => $biz->id, 'name' => 'Roaster', 'basis' => LaborRate::BASIS_HOURLY, 'rate_amount' => 30000]);
        CostModelLabor::create(['cost_model_id' => $cm->id, 'labor_rate_id' => $rate->id, 'quantity' => 0.5]);

        // 5. Machine (Mesin: 0.5 jam @ Rp 10.000/hr = Rp 5.000)
        $machine = Machine::create([
            'business_id' => $biz->id,
            'name' => 'Mesin 1kg',
            'purchase_price' => 20000000,
            'useful_life_hours' => 2500, // Depresiasi = 8.000 / hr
            'maintenance_cost_per_hour' => 1000,
            'electricity_cost_per_hour' => 1000, // Total = 10.000 / hr
        ]);
        CostModelMachine::create(['cost_model_id' => $cm->id, 'machine_id' => $machine->id, 'hours_used' => 0.5]);

        // Total Material = 100.000, Labor = 15.000, Machine = 5.000 -> Total Direct Manufacturing Cost = 120.000
        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->getJson("/api/v1/cost-models/{$cm->slug}/preview")
            ->assertOk();

        $this->assertEqualsWithDelta(100000.0, (float) $response->json('preview.summary.total_material_cost'), 0.01);
        $this->assertEqualsWithDelta(15000.0, (float) $response->json('preview.summary.total_labor_cost'), 0.01);
        $this->assertEqualsWithDelta(5000.0, (float) $response->json('preview.summary.total_machine_cost'), 0.01);
        $this->assertEqualsWithDelta(115000.0, (float) $response->json('preview.summary.prime_cost'), 0.01);
        $this->assertEqualsWithDelta(20000.0, (float) $response->json('preview.summary.conversion_cost'), 0.01);
        $this->assertEqualsWithDelta(120000.0, (float) $response->json('preview.summary.direct_cost_subtotal'), 0.01);
    }
}
