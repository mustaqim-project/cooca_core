<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Overhead\AbcCostingService;
use App\Domain\Overhead\AllocationEngineService;
use App\Models\Activity;
use App\Models\AllocationRule;
use App\Models\Business;
use App\Models\CostDriver;
use App\Models\CostModel;
use App\Models\CostPool;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OverheadAndAllocationEngineTest extends TestCase
{
    use RefreshDatabase;

    private AllocationEngineService $allocationEngine;

    private AbcCostingService $abcService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->allocationEngine = new AllocationEngineService;
        $this->abcService = new AbcCostingService;
    }

    public function test_overhead_crud_and_periodic_normalization(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'ovh_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Overhead']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        // Yearly rent: Rp 120,000,000
        $res = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/overheads', [
                'name' => 'Sewa Bangunan Tahunan',
                'amount' => 120000000,
                'period' => Overhead::PERIOD_YEARLY,
                'behavior' => Overhead::BEHAVIOR_FIXED,
            ])
            ->assertCreated();

        $this->assertEqualsWithDelta(10000000.0, (float) $res->json('overhead.monthly_amount'), 0.01);
    }

    public function test_cost_pool_aggregation_and_manual_override(): void
    {
        $biz = Business::create(['name' => 'Pabrik Garmen']);

        $ovh1 = Overhead::create(['business_id' => $biz->id, 'name' => 'Sewa Gudang', 'amount' => 10000000, 'period' => Overhead::PERIOD_MONTHLY]);
        $ovh2 = Overhead::create(['business_id' => $biz->id, 'name' => 'Listrik Pabrik', 'amount' => 5000000, 'period' => Overhead::PERIOD_MONTHLY]);

        // Auto-sum pool: 10jt + 5jt = 15jt
        $pool = CostPool::create(['business_id' => $biz->id, 'name' => 'Facility Pool']);
        $pool->overheads()->attach([$ovh1->id, $ovh2->id]);

        $this->assertEqualsWithDelta(15000000.0, $pool->totalAmount(), 0.01);

        // Manual override: Rp 20,000,000
        $pool->update(['manual_override_amount' => 20000000]);
        $this->assertEqualsWithDelta(20000000.0, $pool->totalAmount(), 0.01);
    }

    public function test_traditional_allocation_methods(): void
    {
        $biz = Business::create(['name' => 'Manufaktur']);
        $pool = CostPool::create(['business_id' => $biz->id, 'name' => 'Overhead Pool', 'manual_override_amount' => 10000000]); // 10 Juta

        // 1. Per Unit: 10 Juta / 1000 units = 10,000 / unit (§18)
        $perUnit = $this->allocationEngine->perUnit($pool, 1000);
        $this->assertEqualsWithDelta(10000.0, $perUnit, 0.01);

        // 2. Revenue %: 10 Juta * (20 Juta / 100 Juta) = 2,000,000
        $revPct = $this->allocationEngine->revenuePercentage($pool, 20000000, 100000000);
        $this->assertEqualsWithDelta(2000000.0, $revPct, 0.01);

        // 3. Labor Hours: 10 Juta * (50 hrs / 200 hrs) = 2,500,000
        $laborHour = $this->allocationEngine->laborHour($pool, 50, 200);
        $this->assertEqualsWithDelta(2500000.0, $laborHour, 0.01);

        // 4. Machine Hours: 10 Juta * (30 hrs / 150 hrs) = 2,000,000
        $machHour = $this->allocationEngine->machineHour($pool, 30, 150);
        $this->assertEqualsWithDelta(2000000.0, $machHour, 0.01);

        // 5. Physical Metric (Area/Weight/Volume): 10 Juta * (20 m² / 100 m²) = 2,000,000
        $physical = $this->allocationEngine->physicalMetric($pool, 20, 100);
        $this->assertEqualsWithDelta(2000000.0, $physical, 0.01);
    }

    public function test_allocation_rule_preview_api(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'rule_api@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Rules']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pool = CostPool::create(['business_id' => $biz->id, 'name' => 'Pool Operasional', 'manual_override_amount' => 12000000]);
        $driver = CostDriver::create(['business_id' => $biz->id, 'name' => 'Total Unit Bulanan', 'type' => CostDriver::TYPE_PER_UNIT]);

        $rule = AllocationRule::create([
            'business_id' => $biz->id,
            'cost_pool_id' => $pool->id,
            'cost_driver_id' => $driver->id,
            'total_driver_capacity' => 1200, // 1200 unit kapasitas bulanan
        ]);

        // Preview: 12 Juta / 1200 = Rp 10.000 / unit
        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/allocation-rules/{$rule->id}/preview", [
                'product_metric' => 1,
            ])
            ->assertOk();

        $this->assertEqualsWithDelta(10000.0, (float) $response->json('allocated_overhead_amount'), 0.01);
    }

    public function test_activity_based_costing_abc_rate_and_product_consumption(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'abc_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Pabrik Presisi ABC']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // 1. Cost Pool: Biaya QC = Rp 10.000.000 (§19 Blueprint)
        $qcPool = CostPool::create(['business_id' => $biz->id, 'name' => 'Pool QC', 'manual_override_amount' => 10000000]);

        // 2. Activity: Inspeksi Kualitas (Kapasitas: 1.000 kali inspeksi) -> Rate = Rp 10.000 / inspeksi
        $activity = Activity::create([
            'business_id' => $biz->id,
            'cost_pool_id' => $qcPool->id,
            'name' => 'Inspeksi Kualitas Komponen',
            'cost_driver_name' => 'Jumlah Inspeksi',
            'total_activity_capacity' => 1000,
        ]);

        $this->assertEqualsWithDelta(10000.0, $this->abcService->activityRate($activity), 0.01);

        // 3. Product & Cost Model
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Produk Presisi A', 'output_unit_id' => $pcs->id]);
        $cm = CostModel::create(['business_id' => $biz->id, 'product_id' => $product->id, 'name' => 'Model ABC', 'method' => CostModel::METHOD_ABC]);

        // 4. Product A consumes 5 inspeksi = 5 * 10,000 = Rp 50,000
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/cost-models/{$cm->slug}/abc-activities", [
                'activity_id' => $activity->id,
                'consumed_quantity' => 5,
            ])
            ->assertCreated();

        // 5. Query ABC calculation endpoint
        $calcRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->getJson("/api/v1/cost-models/{$cm->slug}/abc-cost")
            ->assertOk();

        $this->assertEqualsWithDelta(50000.0, (float) $calcRes->json('abc_calculation.total_abc_cost'), 0.01);
    }
}
