<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * BUG-004: POS harus menampilkan effective stock dari Material master stock,
 * bukan product-level stock fiktif.
 */
final class PosEffectiveStockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $locA;
    private Location $locB;
    private Unit $unit;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Kasir', 'email' => 'kasir_eff@example.com', 'password' => 'password123']);
        $this->business = Business::create(['name' => 'Bisnis POS Eff']);
        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->locA = Location::create(['business_id' => $this->business->id, 'name' => 'WH-A', 'type' => 'outlet', 'is_active' => true, 'is_primary' => true]);
        $this->locB = Location::create(['business_id' => $this->business->id, 'name' => 'WH-B', 'type' => 'outlet', 'is_active' => true]);
        $this->unit = Unit::create(['business_id' => $this->business->id, 'code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs', 'category' => 'quantity']);

        $panci = Material::create(['business_id' => $this->business->id, 'code' => 'PANC', 'name' => 'Panci', 'unit_id' => $this->unit->id]);
        $tutup = Material::create(['business_id' => $this->business->id, 'code' => 'TUTP', 'name' => 'Tutup', 'unit_id' => $this->unit->id]);

        InventoryStock::create(['business_id' => $this->business->id, 'location_id' => $this->locA->id, 'material_id' => $panci->id, 'quantity' => 10]);
        InventoryStock::create(['business_id' => $this->business->id, 'location_id' => $this->locA->id, 'material_id' => $tutup->id, 'quantity' => 6]);

        $cat = ProductCategory::create(['business_id' => $this->business->id, 'name' => 'Kategori']);
        $this->product = Product::create(['business_id' => $this->business->id, 'category_id' => $cat->id, 'output_unit_id' => $this->unit->id, 'code' => 'PRD-EF', 'name' => 'Panci Premium', 'selling_price' => 150000, 'base_cost' => 100000, 'is_active' => true]);

        $cm = CostModel::create(['business_id' => $this->business->id, 'product_id' => $this->product->id, 'name' => 'HPP', 'method' => CostModel::METHOD_RECIPE_BOM, 'is_active' => true]);
        $bh = BomHeader::create(['cost_model_id' => $cm->id, 'name' => 'Resep', 'type' => BomHeader::TYPE_RECIPE, 'level' => 1]);
        BomItem::create(['bom_header_id' => $bh->id, 'material_id' => $panci->id, 'quantity' => 1, 'unit_id' => $this->unit->id, 'waste_percentage' => 0]);
        BomItem::create(['bom_header_id' => $bh->id, 'material_id' => $tutup->id, 'quantity' => 1, 'unit_id' => $this->unit->id, 'waste_percentage' => 0]);
    }

    public function test_pos_shows_bottleneck_material_effective_stock(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $response = $this->get(route('pos.terminal', ['location_id' => $this->locA->id]));
        $response->assertStatus(200);

        // Product BOM butuh 1 panci + 1 tutup => bottleneck = 6
        $response->assertSee('PRD-EF');

        $product = $this->product->fresh();
        $this->assertSame(6.0, $product->calculateEffectiveStock($this->locA->id));
    }

    public function test_effective_stock_is_location_aware(): void
    {
        // WH-A memiliki stok, WH-B tidak.
        $this->assertSame(6.0, $this->product->calculateEffectiveStock($this->locA->id));
        $this->assertSame(0.0, $this->product->calculateEffectiveStock($this->locB->id));
    }

    public function test_stock_changes_immediately_after_material_receipt(): void
    {
        $stock = new \App\Domain\Inventory\StockService();
        $stock->recordMovement(
            businessId: $this->business->id,
            locationId: $this->locA->id,
            materialId: \App\Models\Material::where('code', 'TUTP')->first()->id,
            productId: null,
            movementType: \App\Models\StockMovement::TYPE_GOODS_RECEIPT,
            quantityChange: 4,
            unitCost: 20000,
            referenceId: 'GR-X',
            referenceNumber: 'GR-X'
        );

        // Tutup 6 + 4 = 10, Panci 10 => bottleneck tetap 10 (panci = tutup = 10)
        $this->assertSame(10.0, $this->product->calculateEffectiveStock($this->locA->id));
    }
}