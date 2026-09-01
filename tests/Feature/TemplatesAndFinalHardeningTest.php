<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Currency\CurrencyConversionService;
use App\Domain\Report\ReportingService;
use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CostModel;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\ProductCostVersion;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TemplatesAndFinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    private BusinessTemplateService $templateService;

    private CurrencyConversionService $currencyService;

    private ReportingService $reportingService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(BusinessTemplateSeeder::class);

        $this->templateService = new BusinessTemplateService;
        $this->currencyService = new CurrencyConversionService;
        $this->reportingService = new ReportingService;
    }

    public function test_25_business_templates_seeder_availability(): void
    {
        $count = BusinessTypeTemplate::count();
        $this->assertEquals(25, $count);

        $resto = BusinessTypeTemplate::where('code', 'fnb_resto')->firstOrFail();
        $this->assertNotEmpty($resto->default_cost_components);
    }

    public function test_business_template_application_service(): void
    {
        $biz = Business::create(['name' => 'Kedai Kopi Baru']);
        $template = BusinessTypeTemplate::where('code', 'fnb_cafe')->firstOrFail();

        $result = $this->templateService->apply($biz, $template);

        $this->assertGreaterThan(0, $result['components_created']);
        $this->assertDatabaseHas('cost_components', [
            'business_id' => $biz->id,
            'name' => 'Biji Kopi (Beans)',
        ]);
    }

    public function test_multi_currency_exchange_rates_and_conversion(): void
    {
        $idr = Currency::where('code', 'IDR')->firstOrFail();
        $usd = Currency::where('code', 'USD')->firstOrFail();

        ExchangeRate::create([
            'from_currency_id' => $usd->id,
            'to_currency_id' => $idr->id,
            'rate' => 16000.0,
            'snapshot_date' => '2026-01-01',
        ]);

        // 10 USD -> 160,000 IDR
        $converted = $this->currencyService->convert(10.0, $usd, $idr);
        $this->assertEqualsWithDelta(160000.0, $converted, 0.01);

        // 320,000 IDR -> 20 USD (Inverse)
        $inverse = $this->currencyService->convert(320000.0, $idr, $usd);
        $this->assertEqualsWithDelta(20.0, $inverse, 0.01);
    }

    public function test_multi_location_management_and_isolation(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'loc_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Distributor Multi Cabang']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/locations', [
                'name' => 'Gudang Pusat Surabaya',
                'address' => 'Jl. Industri No. 10',
                'is_primary' => true,
            ])
            ->assertCreated();

        $this->assertEquals('gudang-pusat-surabaya', $response->json('location.slug'));
    }

    public function test_reporting_suite_and_tenant_isolation(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'rep_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Pabrik Laporan']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $prod = Product::create(['business_id' => $biz->id, 'name' => 'Produk Report', 'output_unit_id' => $pcs->id]);
        $cm = CostModel::create(['business_id' => $biz->id, 'product_id' => $prod->id, 'name' => 'CM Report', 'method' => CostModel::METHOD_SIMPLE]);

        ProductCostVersion::create([
            'business_id' => $biz->id,
            'product_id' => $prod->id,
            'cost_model_id' => $cm->id,
            'version_number' => 1,
            'version_label' => 'v1.0.0',
            'status' => ProductCostVersion::STATUS_APPROVED,
            'total_hpp' => 50000,
            'hpp_per_unit' => 50000,
            'hpp_snapshot' => [
                'total_material_cost' => 30000,
                'total_labor_cost' => 15000,
                'total_machine_cost' => 5000,
                'total_overhead_cost' => 0,
            ],
        ]);

        $res = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->getJson('/api/v1/reports/hpp-per-product')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
        $this->assertEquals('Produk Report', $res->json('data.0.product_name'));
        $this->assertEqualsWithDelta(50000.0, (float) $res->json('data.0.hpp_per_unit'), 0.01);
    }

    public function test_api_documentation_endpoint(): void
    {
        $response = $this->getJson('/api/v1/docs')->assertOk();
        $this->assertEquals('Universal HPP Calculator Engine API', $response->json('api_name'));
        $this->assertNotEmpty($response->json('modules'));
    }
}
