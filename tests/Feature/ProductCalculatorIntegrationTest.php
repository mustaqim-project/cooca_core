<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CostModel;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductCalculatorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Owner Produk', 'email' => 'produk@cooca.id', 'password' => 'password123']);
        $this->business = Business::create(['name' => 'Toko Produk Maju']);
        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);
    }

    private function makeProduct(string $name = 'Kopi Susu', ?float $cost = null, ?float $price = null): Product
    {
        return Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'SKU-' . strtoupper(Str::random(4)),
            'name' => $name,
            'base_cost' => $cost ?? 7000,
            'selling_price' => $price ?? 15000,
            'is_active' => true,
        ]);
    }

    public function test_products_page_has_link_to_calculator_for_each_product(): void
    {
        $product = $this->makeProduct();

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Kalkulasi HPP');
        $response->assertSee(route('calculator.index', ['product_id' => $product->id, 'tab' => 'advanced']));
    }

    public function test_calculator_preselects_product_from_url(): void
    {
        $product = $this->makeProduct('Espresso Martini', 12000, 32000);
        CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'name' => 'Model Utama',
            'method' => CostModel::METHOD_SIMPLE,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('calculator.index', ['product_id' => $product->id, 'tab' => 'advanced']));

        $response->assertStatus(200);
        $response->assertSee("selectedProductId: '{$product->id}'", false);
        $response->assertSee("activeTab: 'advanced'", false);
    }    public function test_calculator_ignores_unknown_product_id(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calculator.index', ['product_id' => 'unknown-id', 'tab' => 'advanced']));

        $response->assertStatus(200);
        $response->assertSee("selectedProductId: ''", false);
    }

    public function test_products_page_opens_edit_modal_from_calculator_link(): void
    {
        $product = $this->makeProduct('Roti Bakar Coklat', 5000, 12000);

        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['edit' => $product->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('showEditModal: true', $response->getContent());
        $this->assertStringContainsString($product->name, $response->getContent());
    }

    public function test_products_edit_modal_still_editable_after_calculator_integration(): void
    {
        $product = $this->makeProduct('Ayam Geprek', 10000, 22000);

        $response = $this->actingAs($this->user)
            ->put(route('products.update', $product->slug), [
                'name' => 'Ayam Geprek Level 3',
                'category_id' => '',
                'output_unit_id' => $this->unit->id,
                'sku' => $product->code,
                'base_cost' => 11000,
                'selling_price' => 25000,
                'min_stock' => 0,
                'description' => 'Pedas level 3',
            ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Ayam Geprek Level 3', $product->name);
        $this->assertEquals(11000.0, $product->base_cost);
        $this->assertEquals(25000.0, $product->selling_price);
    }

    public function test_product_hpp_and_pricing_flow_roundtrip(): void
    {
        $product = $this->makeProduct('Matcha Latte', 8000, 20000);

        // 1. From products → calculator (advanced) with product preselected.
        $calcResponse = $this->actingAs($this->user)
            ->get(route('calculator.index', ['product_id' => $product->id, 'tab' => 'advanced']));
        $calcResponse->assertStatus(200);
        $this->assertStringContainsString("selectedProductId: '{$product->id}'", $calcResponse->getContent());

        // 2. Back to products with edit modal auto-open.
        $editResponse = $this->actingAs($this->user)
            ->get(route('products.index', ['edit' => $product->id]));
        $editResponse->assertStatus(200);
        $this->assertStringContainsString('showEditModal: true', $editResponse->getContent());

        // 3. Edit stays functional.
        $this->actingAs($this->user)
            ->put(route('products.update', $product->slug), [
                'name' => 'Matcha Latte',
                'category_id' => '',
                'output_unit_id' => $this->unit->id,
                'sku' => $product->code,
                'base_cost' => 8500,
                'selling_price' => 21000,
                'min_stock' => 0,
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertEquals(8500.0, $product->base_cost);
        $this->assertEquals(21000.0, $product->selling_price);
    }
}
