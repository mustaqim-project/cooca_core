<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosOrderService;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductBundleItem;
use App\Models\ProductChannelPrice;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductBundleStockAndHppTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Manager F&B',
            'email' => 'manager_fnb@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Resto Kombo Mantap',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Resto Pusat',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->unitPcs = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);
    }

    public function test_bundle_hpp_accumulates_child_costs_correctly(): void
    {
        // Product A = 15k price, 8k cost
        $prodA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'F&B Item A (Burger)',
            'code' => 'BRG-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 15000,
            'base_cost' => 8000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        // Product B = 20k price, 4k cost
        $prodB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'F&B Item B (Milkshake)',
            'code' => 'MLK-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 20000,
            'base_cost' => 4000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        // Bundle AB = 30k price (promo combo), contains 1x A and 1x B
        $bundle = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kombo Mantap AB',
            'code' => 'KMB-AB',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 30000,
            'base_cost' => 0,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => true,
            'is_active' => true,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodA->id,
            'quantity' => 1.0,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodB->id,
            'quantity' => 1.0,
        ]);

        // HPP should be 8.000 + 4.000 = 12.000
        $this->assertEquals(12000.0, $bundle->getBundleHpp());
    }

    public function test_bundle_effective_stock_follows_bottleneck_rule(): void
    {
        $prodA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Burger Patty',
            'code' => 'PTY-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 15000,
            'base_cost' => 8000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $prodB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Kaleng',
            'code' => 'KLG-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 10000,
            'base_cost' => 5000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        // Stock for A: 10 units
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $prodA->id,
            'quantity' => 10.0,
            'reserved_quantity' => 0.0,
        ]);

        // Stock for B: 3 units (bottleneck!)
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $prodB->id,
            'quantity' => 3.0,
            'reserved_quantity' => 0.0,
        ]);

        $bundle = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Paket Hemat Burger + Kaleng',
            'code' => 'PKT-BK',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 22000,
            'base_cost' => 0,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => true,
            'is_active' => true,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodA->id,
            'quantity' => 2.0, // Needs 2 patties per bundle: 10 / 2 = 5 max
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodB->id,
            'quantity' => 1.0, // Needs 1 drink: 3 / 1 = 3 max
        ]);

        // Effective stock bottleneck should be 3
        $this->assertEquals(3.0, $bundle->calculateEffectiveStock($this->location->id));
        $this->assertEquals(3.0, $bundle->calculateEffectiveAvailableStock($this->location->id));

        // When drink B is sold out (quantity = 0)
        InventoryStock::where('product_id', $prodB->id)->update(['quantity' => 0.0]);
        $this->assertEquals(0.0, $bundle->calculateEffectiveStock($this->location->id));
        $this->assertEquals(0.0, $bundle->calculateEffectiveAvailableStock($this->location->id));
    }

    public function test_bundle_recursive_material_deductions_for_child_with_bom(): void
    {
        // Raw material
        $rawCoffee = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'code' => 'MAT-KOP',
            'unit_id' => $this->unitPcs->id,
            'cost_per_unit' => 200,
            'is_active' => true,
        ]);

        // Child Product with BOM Recipe
        $coffeeProduct = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Shot',
            'code' => 'ESP-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 18000,
            'base_cost' => 3000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $coffeeProduct->id,
            'name' => 'BOM Model',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Resep Kopi',
            'type' => BomHeader::TYPE_RECIPE,
            'level' => 1,
        ]);

        $bomHeader->items()->create([
            'material_id' => $rawCoffee->id,
            'quantity' => 15.0, // 15 grams per espresso
            'unit_id' => $this->unitPcs->id,
            'is_mandatory' => true,
        ]);

        // Bundle: 2x Espresso Shot
        $bundle = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Double Shot Combo',
            'code' => 'KMB-DBL',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 30000,
            'base_cost' => 0,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => true,
            'is_active' => true,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $coffeeProduct->id,
            'quantity' => 2.0,
        ]);

        // Calling getMaterialDeductions on bundle for 1 bundle unit:
        // 1 bundle * 2 child * 15 grams = 30 grams of raw coffee
        $deductions = $bundle->getMaterialDeductions(1.0);
        $this->assertCount(1, $deductions);
        $this->assertEquals($rawCoffee->id, $deductions[0]['material_id']);
        $this->assertEquals(30.0, $deductions[0]['quantity']);

        // For 3 bundles: 3 * 30 = 90 grams
        $deductions3 = $bundle->getMaterialDeductions(3.0);
        $this->assertEquals(90.0, $deductions3[0]['quantity']);
    }

    public function test_pos_checkout_with_bundle_deducts_child_stocks_and_records_bundle_hpp(): void
    {
        $prodA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Roti Bakar',
            'code' => 'ROT-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 15000,
            'base_cost' => 6000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $prodB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Tubruk',
            'code' => 'KOP-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 10000,
            'base_cost' => 3000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        // Initial Stocks
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $prodA->id,
            'quantity' => 20.0,
            'reserved_quantity' => 0.0,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $prodB->id,
            'quantity' => 15.0,
            'reserved_quantity' => 0.0,
        ]);

        $bundle = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Paket Sarapan Roti + Kopi',
            'code' => 'PKT-SAR',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 20000, // Discounted from 25k to 20k
            'base_cost' => 0,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => true,
            'is_active' => true,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodA->id,
            'quantity' => 1.0,
        ]);

        ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $prodB->id,
            'quantity' => 1.0,
        ]);

        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Sarapan',
            'is_active' => true,
        ]);

        $posOrderService = app(PosOrderService::class);

        // Checkout 2 units of the bundle
        $order = $posOrderService->checkout(
            business: $this->business,
            cashier: $this->user,
            itemsData: [
                [
                    'product_id' => $bundle->id,
                    'quantity' => 2,
                    'unit_price' => 20000,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => 'cash',
                    'amount' => 40000,
                ],
            ],
            attributes: [
                'location_id' => $this->location->id,
                'customer_id' => $customer->id,
                'order_type' => 'dine_in',
                'sales_channel' => 'dine_in',
            ]
        );

        $this->assertInstanceOf(PosOrder::class, $order);
        $this->assertEquals(40000.0, (float) $order->total_amount);

        // Unit HPP for bundle = 6.000 + 3.000 = 9.000
        // Total HPP for 2 bundles = 18.000
        $this->assertEquals(18000.0, (float) $order->total_hpp_cost);

        $lineItem = $order->items()->first();
        $this->assertEquals(9000.0, (float) $lineItem->unit_cost_hpp);
        $this->assertEquals(18000.0, (float) $lineItem->total_hpp);

        // Stock deduction check:
        // Prod A: 20 - 2 = 18
        $stockA = InventoryStock::where('product_id', $prodA->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(18.0, (float) $stockA->quantity);

        // Prod B: 15 - 2 = 13
        $stockB = InventoryStock::where('product_id', $prodB->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(13.0, (float) $stockB->quantity);

        // Stock movements created for each child item
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $prodA->id,
            'movement_type' => StockMovement::TYPE_POS_SALE,
            'quantity_change' => -2.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $prodB->id,
            'movement_type' => StockMovement::TYPE_POS_SALE,
            'quantity_change' => -2.0,
        ]);
    }

    public function test_product_web_controller_persists_bundle_and_channel_prices(): void
    {
        $this->actingAs($this->user);

        $prod1 = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Item 1',
            'code' => 'ITM-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 10000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $prod2 = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Item 2',
            'code' => 'ITM-02',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 12000,
            'type' => Product::TYPE_GOODS,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $postData = [
            'name' => 'Paket Duo Juara',
            'sku' => 'DUO-01',
            'output_unit_id' => $this->unitPcs->id,
            'costing_method' => 'simple',
            'selling_price' => 19000,
            'base_cost' => 0,
            'is_bundle' => '1',
            'bundle_items' => [
                ['child_product_id' => $prod1->id, 'quantity' => 1],
                ['child_product_id' => $prod2->id, 'quantity' => 1],
            ],
            'channel_prices' => [
                'dine_in' => 19000,
                'takeaway' => 20000,
                'gofood' => 24000,
                'grabfood' => 24000,
                'shopeefood' => 23000,
            ],
        ];

        $response = $this->post(route('products.store'), $postData);
        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'business_id' => $this->business->id,
            'name' => 'Paket Duo Juara',
            'code' => 'DUO-01',
            'is_bundle' => true,
        ]);

        $createdBundle = Product::where('code', 'DUO-01')->first();
        $this->assertNotNull($createdBundle);

        $this->assertDatabaseHas('product_bundle_items', [
            'parent_product_id' => $createdBundle->id,
            'child_product_id' => $prod1->id,
            'quantity' => 1.0,
        ]);

        $this->assertDatabaseHas('product_bundle_items', [
            'parent_product_id' => $createdBundle->id,
            'child_product_id' => $prod2->id,
            'quantity' => 1.0,
        ]);

        $this->assertDatabaseHas('product_channel_prices', [
            'product_id' => $createdBundle->id,
            'channel' => 'gofood',
            'price' => 24000.0,
        ]);

        // Test update
        $updateData = [
            'name' => 'Paket Duo Juara Updated',
            'sku' => 'DUO-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 21000,
            'is_bundle' => '1',
            'bundle_items' => [
                ['child_product_id' => $prod1->id, 'quantity' => 2],
            ],
            'channel_prices' => [
                'dine_in' => 21000,
                'gofood' => 26000,
            ],
        ];

        $updateResponse = $this->put(route('products.update', $createdBundle->slug), $updateData);
        $updateResponse->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $createdBundle->id,
            'name' => 'Paket Duo Juara Updated',
            'selling_price' => 21000,
        ]);

        $this->assertDatabaseHas('product_bundle_items', [
            'parent_product_id' => $createdBundle->id,
            'child_product_id' => $prod1->id,
            'quantity' => 2.0,
        ]);

        // Prod 2 was removed
        $this->assertDatabaseMissing('product_bundle_items', [
            'parent_product_id' => $createdBundle->id,
            'child_product_id' => $prod2->id,
        ]);
    }
}
