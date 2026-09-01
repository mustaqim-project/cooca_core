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

class SimplifiedHppCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Kedai Kopi',
            'email' => 'kopi@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create(['name' => 'Kedai Kopi Bahagia']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

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

    public function test_calculator_page_renders_with_simplified_mode_and_presets(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calculator.index'));

        $response->assertStatus(200);
        $response->assertSee('Mode Cepat (3 Pilar HPP)');
        $response->assertSee('Biaya Bahan Baku');
        $response->assertSee('Biaya Upah');
        $response->assertSee('Biaya Operasional');
        $response->assertSee('Jual di Ojek Online?');
        $response->assertSee('Target Balik Modal (BEP)');
    }

    public function test_quick_create_product_from_calculator_creates_product_and_simple_cost_model(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('calculator.quick-create-product'), [
                'name' => 'Kopi Pandan Wangi',
                'material_cost' => 5000,
                'labor_cost' => 1500,
                'overhead_cost' => 500,
                'selling_price' => 12000,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'product' => [
                'name' => 'Kopi Pandan Wangi',
                'base_cost' => 7000,
                'selling_price' => 12000,
            ],
        ]);

        $product = Product::where('business_id', $this->business->id)
            ->where('name', 'Kopi Pandan Wangi')
            ->first();

        $this->assertNotNull($product);
        $this->assertEquals(7000, $product->base_cost);
        $this->assertEquals(12000, $product->selling_price);

        // Check that a CostModel was automatically created
        $costModel = CostModel::where('product_id', $product->id)->first();
        $this->assertNotNull($costModel);
        $this->assertEquals(CostModel::METHOD_SIMPLE, $costModel->method);
        $this->assertEquals(CostModel::BASIS_SELLABLE, $costModel->output_basis);
        $this->assertIsArray($costModel->formula_definition);
        $this->assertGreaterThan(0, $costModel->formula_definition['margin_pct']);
    }

    public function test_quick_create_product_validation_fails_if_name_or_selling_price_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('calculator.quick-create-product'), [
                'name' => '',
                'material_cost' => 5000,
                'selling_price' => null,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'selling_price']);
    }
}
