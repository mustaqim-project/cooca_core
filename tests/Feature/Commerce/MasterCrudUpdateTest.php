<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MasterCrudUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Unit $pcs;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin_gudang@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Mandiri',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->pcs = Unit::where('code', 'pcs')->firstOrFail();
    }

    public function test_can_update_product(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kue Kering Nastar',
            'output_unit_id' => $this->pcs->id,
            'costing_method' => 'simple',
            'base_cost' => 30000,
            'selling_price' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put("/products/{$product->slug}", [
                'name' => 'Kue Kering Nastar Premium Wisman',
                'sku' => 'NSTR-002',
                'output_unit_id' => $this->pcs->id,
                'base_cost' => 45000,
                'selling_price' => 85000,
                'min_stock' => 20,
                'is_active' => '1',
            ]);

        $response->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Kue Kering Nastar Premium Wisman',
            'code' => 'NSTR-002',
            'base_cost' => 45000,
            'selling_price' => 85000,
            'min_stock' => 20,
        ]);
    }

    public function test_can_update_material(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Tepung Terigu Segitiga',
            'unit_id' => $this->pcs->id,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put("/materials/{$material->slug}", [
                'name' => 'Tepung Terigu Protein Tinggi Cakra Kembar',
                'sku' => 'TPG-002',
                'unit_id' => $this->pcs->id,
            ]);

        $response->assertRedirect('/materials');

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'name' => 'Tepung Terigu Protein Tinggi Cakra Kembar',
            'code' => 'TPG-002',
        ]);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Pemasok Lama',
            'contact_person' => 'Pak Joko',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put("/suppliers/{$supplier->id}", [
                'name' => 'PT Pemasok Bahan Baku Nasional',
                'contact_person' => 'Bapak Joko Santoso',
                'phone' => '0811223344',
            ]);

        $response->assertRedirect('/suppliers');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'PT Pemasok Bahan Baku Nasional',
            'contact_person' => 'Bapak Joko Santoso',
        ]);
    }

    public function test_can_apply_calculator_result_to_product(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'output_unit_id' => $this->pcs->id,
            'costing_method' => 'simple',
            'base_cost' => 0,
            'selling_price' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson('/calculator/apply-to-product', [
                'product_id' => $product->id,
                'base_cost' => 8500,
                'selling_price' => 18000,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals(8500, (float) $product->base_cost);
        $this->assertEquals(18000, (float) $product->selling_price);
    }
}
