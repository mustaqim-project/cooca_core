<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Calculation\HppPropagationService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-003: Perubahan harga material harus propagasi ke HPP produk terkait (base_cost),
 * sedangkan riwayat transaksi (cost snapshot) tetap immutable.
 */
final class HppPropagationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Unit $unit;
    private Material $panci;
    private Material $tutup;
    private Product $produk;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->business = Business::create(['name' => 'Bisnis HPP']);
        $this->unit = Unit::create(['business_id' => $this->business->id, 'code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs', 'category' => 'quantity']);

        $this->panci = Material::create(['business_id' => $this->business->id, 'code' => 'PANC', 'name' => 'Panci', 'unit_id' => $this->unit->id]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $this->panci->id,
            'purchase_price' => 80000,
            'purchase_unit_id' => $this->unit->id,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
            'effective_date' => now()->toDateString(),
        ]);
        $this->tutup = Material::create(['business_id' => $this->business->id, 'code' => 'TUTP', 'name' => 'Tutup', 'unit_id' => $this->unit->id]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $this->tutup->id,
            'purchase_price' => 20000,
            'purchase_unit_id' => $this->unit->id,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
            'effective_date' => now()->toDateString(),
        ]);

        $this->produk = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'PRD-PANCI',
            'name' => 'Panci Premium',
            'selling_price' => 150000,
            'base_cost' => 0,
            'is_active' => true,
        ]);
        $cm = CostModel::create(['business_id' => $this->business->id, 'product_id' => $this->produk->id, 'name' => 'HPP Panci', 'method' => CostModel::METHOD_RECIPE_BOM, 'is_active' => true]);
        $bh = BomHeader::create(['cost_model_id' => $cm->id, 'name' => 'Resep Panci', 'type' => BomHeader::TYPE_RECIPE, 'level' => 1]);
        BomItem::create(['bom_header_id' => $bh->id, 'material_id' => $this->panci->id, 'quantity' => 1, 'unit_id' => $this->unit->id, 'waste_percentage' => 0]);
        BomItem::create(['bom_header_id' => $bh->id, 'material_id' => $this->tutup->id, 'quantity' => 1, 'unit_id' => $this->unit->id, 'waste_percentage' => 0]);
    }

    public function test_changing_material_price_updates_product_base_cost(): void
    {
        $service = new HppPropagationService();
        $updated = $service->refreshForMaterial($this->panci->id);

        $this->assertSame(1, $updated);
        $this->assertSame(100000.0, (float) $this->produk->fresh()->base_cost); // 80k + 20k

        // Ubah harga panci -> 100k => HPP baru 120k
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $this->panci->id,
            'purchase_price' => 100000,
            'purchase_unit_id' => $this->unit->id,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
            'effective_date' => now()->toDateString(),
        ]);

        $service->refreshForMaterial($this->panci->id);

        $this->assertSame(120000.0, (float) $this->produk->fresh()->base_cost);
    }

    public function test_unrelated_product_not_affected(): void
    {
        $lain = Product::create(['business_id' => $this->business->id, 'output_unit_id' => $this->unit->id, 'code' => 'PRD-LAIN', 'name' => 'Produk Lain', 'base_cost' => 5000, 'is_active' => true]);

        $service = new HppPropagationService();
        $service->refreshForMaterial($this->panci->id);

        $this->assertSame(5000.0, (float) $lain->fresh()->base_cost);
    }

    public function test_historical_transaction_snapshot_remains_immutable(): void
    {
        $service = new HppPropagationService();
        $service->refreshForMaterial($this->panci->id);
        $this->assertSame(100000.0, (float) $this->produk->fresh()->base_cost);

        // Transaksi historis: snapshot HPP 100.000 diambil saat itu
        $location = Location::create(['business_id' => $this->business->id, 'name' => 'WH-X', 'type' => 'warehouse', 'is_active' => true]);
        $user = \App\Models\User::create(['name' => 'User', 'email' => 'hp_p_@example.com', 'password' => 'password123']);
        $this->business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);
        $order = \App\Models\PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $location->id,
            'user_id' => $user->id,
            'order_number' => 'POS-HPP-1',
            'order_date' => now(),
            'status' => \App\Models\PosOrder::STATUS_COMPLETED,
            'subtotal' => 300000,
            'total_amount' => 300000,
            'total_hpp_cost' => 200000,
        ]);
        $line = \App\Models\PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->produk->id,
            'product_name' => $this->produk->name,
            'unit_price' => 150000,
            'unit_cost_hpp' => 100000, // snapshot historis
            'quantity' => 2,
            'subtotal' => 300000,
            'total_hpp' => 200000,
        ]);

        // Ubah harga material => HPP produk (base_cost) menjadi 120.000 untuk transaksi BARU
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $this->panci->id,
            'purchase_price' => 100000,
            'purchase_unit_id' => $this->unit->id,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
            'effective_date' => now()->addDay()->toDateString(),
        ]);
        $service->refreshForMaterial($this->panci->id);
        $this->assertSame(120000.0, (float) $this->produk->fresh()->base_cost);

        // Snapshot historis TIDAK boleh berubah
        $line->refresh();
        $this->assertSame(100000.0, (float) $line->unit_cost_hpp);
        $this->assertSame(200000.0, (float) $line->total_hpp);

        $order->refresh();
        $this->assertSame(200000.0, (float) $order->total_hpp_cost);
    }
}