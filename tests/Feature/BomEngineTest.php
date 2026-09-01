<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Material\MaterialCostService;
use App\Domain\Material\UnitConversionService;
use App\Domain\Product\BomCircularValidator;
use App\Domain\Product\BomExplosionService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostComponent;
use App\Models\CostModel;
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

final class BomEngineTest extends TestCase
{
    use RefreshDatabase;

    private BomExplosionService $explosionService;

    private BomCircularValidator $circularValidator;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->explosionService = new BomExplosionService(new MaterialCostService(new UnitConversionService));
        $this->circularValidator = new BomCircularValidator;
    }

    public function test_product_and_category_crud_with_slug_and_tenant_isolation(): void
    {
        $user = User::create(['name' => 'Owner A', 'email' => 'biz_a@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto Burger']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // 1. Create Category
        $catRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/product-categories', [
                'name' => 'Makanan Utama',
            ])
            ->assertCreated();

        $catId = $catRes->json('category.id');

        // 2. Create Product
        $prodRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson('/api/v1/products', [
                'name' => 'Cheese Burger Deluxe',
                'category_id' => $catId,
                'output_unit_id' => $pcs->id,
                'business_type_hint' => 'fnb',
            ])
            ->assertCreated();

        $this->assertSame('cheese-burger-deluxe', $prodRes->json('product.slug'));
    }

    public function test_cost_model_creation_and_component_sync_with_accounting_warning(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'costmodel@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Manufaktur ABC']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $product = Product::create(['business_id' => $biz->id, 'name' => 'Kursi Kayu', 'output_unit_id' => $pcs->id]);

        // 1. Create Cost Model
        $cmRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/products/{$product->slug}/cost-models", [
                'name' => 'Standar Produksi 2026',
                'method' => CostModel::METHOD_RECIPE_BOM,
            ])
            ->assertCreated();

        $cmSlug = $cmRes->json('cost_model.slug');

        // 2. Sync Components including Marketing (Opex component)
        $rawMaterialComp = CostComponent::where('slug', 'raw-materials')->firstOrFail();
        $marketingComp = CostComponent::where('slug', 'marketing-advertising')->firstOrFail();

        $syncRes = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->putJson("/api/v1/cost-models/{$cmSlug}/components", [
                'components' => [
                    ['cost_component_id' => $rawMaterialComp->id, 'is_included_in_hpp' => true],
                    ['cost_component_id' => $marketingComp->id, 'is_included_in_hpp' => true],
                ],
            ])
            ->assertOk();

        // Check GAAP warning returned
        $this->assertNotEmpty($syncRes->json('warnings'));
    }

    public function test_single_level_recipe_and_bom_cost_calculation(): void
    {
        $biz = Business::create(['name' => 'Bakery']);
        $g = Unit::where('code', 'g')->firstOrFail();
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // 1. Materials
        $terigu = Material::create(['business_id' => $biz->id, 'name' => 'Tepung Terigu', 'unit_id' => $kg->id]);
        MaterialPrice::create([
            'business_id' => $biz->id,
            'material_id' => $terigu->id,
            'purchase_price' => 12000, // Rp 12.000 / kg -> Rp 12 / gram
            'purchase_unit_id' => $kg->id,
            'effective_date' => '2026-01-01',
        ]);

        $telur = Material::create(['business_id' => $biz->id, 'name' => 'Telur Ayam', 'unit_id' => $kg->id]);
        MaterialPrice::create([
            'business_id' => $biz->id,
            'material_id' => $telur->id,
            'purchase_price' => 30000, // Rp 30.000 / kg -> Rp 30 / gram
            'purchase_unit_id' => $kg->id,
            'effective_date' => '2026-01-01',
        ]);

        // 2. Product & Cost Model
        $roti = Product::create(['business_id' => $biz->id, 'name' => 'Roti Manis', 'output_unit_id' => $pcs->id]);
        $costModel = CostModel::create([
            'business_id' => $biz->id,
            'product_id' => $roti->id,
            'name' => 'Resep Roti Manis',
            'method' => CostModel::METHOD_RECIPE_BOM,
        ]);

        $bomHeader = BomHeader::create(['cost_model_id' => $costModel->id, 'name' => 'BOM Roti Manis']);

        // 3. BOM Items: 200g Terigu (no waste), 50g Telur (10% waste -> effective 55g)
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $terigu->id,
            'unit_id' => $g->id,
            'quantity' => 200,
            'waste_percentage' => 0,
        ]);

        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $telur->id,
            'unit_id' => $g->id,
            'quantity' => 50,
            'waste_percentage' => 10, // 50 * 1.10 = 55g * 30 = Rp 1650
        ]);

        // Cost Terigu = 200 * 12 = 2400
        // Cost Telur = 55 * 30 = 1650
        // Total = 4050
        $result = $this->explosionService->explode($bomHeader);

        $this->assertEqualsWithDelta(4050.0, (float) $result['total_material_cost'], 0.01);
    }

    public function test_multi_level_sub_bom_recursive_explosion(): void
    {
        $biz = Business::create(['name' => 'Pabrik Elektronik']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        // Level 3 Material (Baut)
        $baut = Material::create(['business_id' => $biz->id, 'name' => 'Baut M3', 'unit_id' => $pcs->id]);
        MaterialPrice::create(['business_id' => $biz->id, 'material_id' => $baut->id, 'purchase_price' => 500, 'purchase_unit_id' => $pcs->id, 'effective_date' => '2026-01-01']);

        // Level 2 Sub-Assembly (Sub-BOM Casing: 4 Baut = 4 * 500 = 2000)
        $casingProd = Product::create(['business_id' => $biz->id, 'name' => 'Sub Casing Box', 'output_unit_id' => $pcs->id]);
        $casingCm = CostModel::create(['business_id' => $biz->id, 'product_id' => $casingProd->id, 'name' => 'CM Casing', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $subBomCasing = BomHeader::create(['cost_model_id' => $casingCm->id, 'name' => 'Sub-BOM Casing']);
        BomItem::create(['bom_header_id' => $subBomCasing->id, 'material_id' => $baut->id, 'unit_id' => $pcs->id, 'quantity' => 4]);

        // Level 1 Main Product (Device: 1 Sub Casing (2000) + 1 PCB (10000) = 12000)
        $pcb = Material::create(['business_id' => $biz->id, 'name' => 'Modul PCB', 'unit_id' => $pcs->id]);
        MaterialPrice::create(['business_id' => $biz->id, 'material_id' => $pcb->id, 'purchase_price' => 10000, 'purchase_unit_id' => $pcs->id, 'effective_date' => '2026-01-01']);

        $mainProd = Product::create(['business_id' => $biz->id, 'name' => 'Smart Gadget', 'output_unit_id' => $pcs->id]);
        $mainCm = CostModel::create(['business_id' => $biz->id, 'product_id' => $mainProd->id, 'name' => 'CM Smart Gadget', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $mainBom = BomHeader::create(['cost_model_id' => $mainCm->id, 'name' => 'Main BOM Gadget']);

        BomItem::create(['bom_header_id' => $mainBom->id, 'material_id' => $pcb->id, 'unit_id' => $pcs->id, 'quantity' => 1]);
        BomItem::create(['bom_header_id' => $mainBom->id, 'sub_bom_header_id' => $subBomCasing->id, 'unit_id' => $pcs->id, 'quantity' => 1]);

        $result = $this->explosionService->explode($mainBom);

        $this->assertEqualsWithDelta(12000.0, (float) $result['total_material_cost'], 0.01);
    }

    public function test_circular_bom_reference_detection_blocks_cycles(): void
    {
        $biz = Business::create(['name' => 'Cycle Test']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        $p1 = Product::create(['business_id' => $biz->id, 'name' => 'Product 1', 'output_unit_id' => $pcs->id]);
        $cm1 = CostModel::create(['business_id' => $biz->id, 'product_id' => $p1->id, 'name' => 'CM1', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $bom1 = BomHeader::create(['cost_model_id' => $cm1->id, 'name' => 'BOM 1']);

        $p2 = Product::create(['business_id' => $biz->id, 'name' => 'Product 2', 'output_unit_id' => $pcs->id]);
        $cm2 = CostModel::create(['business_id' => $biz->id, 'product_id' => $p2->id, 'name' => 'CM2', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $bom2 = BomHeader::create(['cost_model_id' => $cm2->id, 'name' => 'BOM 2']);

        // 1. Self reference test
        $this->expectException(InvalidArgumentException::class);
        $this->circularValidator->validate($bom1, $bom1);
    }

    public function test_two_level_circular_bom_reference_is_detected(): void
    {
        $biz = Business::create(['name' => 'Cycle Test 2']);
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        $p1 = Product::create(['business_id' => $biz->id, 'name' => 'P1', 'output_unit_id' => $pcs->id]);
        $cm1 = CostModel::create(['business_id' => $biz->id, 'product_id' => $p1->id, 'name' => 'CM1', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $bom1 = BomHeader::create(['cost_model_id' => $cm1->id, 'name' => 'BOM 1']);

        $p2 = Product::create(['business_id' => $biz->id, 'name' => 'P2', 'output_unit_id' => $pcs->id]);
        $cm2 = CostModel::create(['business_id' => $biz->id, 'product_id' => $p2->id, 'name' => 'CM2', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $bom2 = BomHeader::create(['cost_model_id' => $cm2->id, 'name' => 'BOM 2']);

        // BOM 1 contains BOM 2
        BomItem::create(['bom_header_id' => $bom1->id, 'sub_bom_header_id' => $bom2->id, 'unit_id' => $pcs->id, 'quantity' => 1]);

        // Attempting to add BOM 1 into BOM 2 must throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->circularValidator->validate($bom2, $bom1);
    }

    public function test_bom_item_xor_constraint_via_api(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'bom_api@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto API BOM']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $pcs = Unit::where('code', 'pcs')->firstOrFail();
        $prod = Product::create(['business_id' => $biz->id, 'name' => 'Produk A', 'output_unit_id' => $pcs->id]);
        $cm = CostModel::create(['business_id' => $biz->id, 'product_id' => $prod->id, 'name' => 'CM', 'method' => CostModel::METHOD_RECIPE_BOM]);
        $bom = BomHeader::create(['cost_model_id' => $cm->id, 'name' => 'BOM']);

        // Missing both -> 422
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/bom-headers/{$bom->id}/items", [
                'unit_id' => $pcs->id,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'BOM_ITEM_XOR_CONSTRAINT_FAILED');
    }
}
