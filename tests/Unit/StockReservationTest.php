<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Location $location;
    private Unit $unit;
    private StockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Kopi Sejahtera',
            'slug' => 'kopi-sejahtera',
            'currency' => 'IDR',
            'allow_negative_stock' => false,
        ]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'slug' => 'outlet-pusat',
            'type' => 'store',
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'cup',
            'name' => 'Cup',
            'category' => 'quantity',
        ]);

        $this->service = new StockService();
    }

    protected function tearDown(): void
    {
        Context::flush();
        parent::tearDown();
    }

    public function test_it_reserves_stock_and_updates_reserved_quantity(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Single',
            'code' => 'ESP-01',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'base_cost' => 8000,
            'selling_price' => 18000,
        ]);

        // Add 10 initial stock
        $this->service->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 8000
        );

        $this->assertEquals(10.0, $product->calculateEffectiveStock($this->location->id));
        $this->assertEquals(10.0, $product->calculateEffectiveAvailableStock($this->location->id));

        // Reserve 3 items for customer checkout
        $stock = $this->service->reserveStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 3.0
        );

        $this->assertEquals(10.0, (float) $stock->quantity);
        $this->assertEquals(3.0, (float) $stock->reserved_quantity);
        $this->assertEquals(7.0, (float) $stock->available_quantity);
        $this->assertEquals(7.0, $product->calculateEffectiveAvailableStock($this->location->id));
    }

    public function test_it_throws_insufficient_stock_exception_when_reservation_exceeds_available_stock(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Cappuccino',
            'code' => 'CAP-01',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'base_cost' => 10000,
            'selling_price' => 25000,
        ]);

        // Initial 5 stock
        $this->service->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 5.0,
            unitCost: 10000
        );

        // Reserve 3 stock
        $this->service->reserveStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 3.0
        );

        // Available is now 2. Trying to reserve 3 more should fail
        $this->expectException(InsufficientStockException::class);
        $this->service->reserveStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 3.0
        );
    }

    public function test_it_releases_reserved_stock_back_to_available_pool(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Cold Brew',
            'code' => 'CB-01',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'base_cost' => 12000,
            'selling_price' => 28000,
        ]);

        $this->service->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 12000
        );

        // Reserve 4
        $this->service->reserveStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 4.0
        );
        $this->assertEquals(6.0, $product->calculateEffectiveAvailableStock($this->location->id));

        // Order expired: release 4
        $released = $this->service->releaseReservedStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 4.0
        );

        $this->assertEquals(0.0, (float) $released->reserved_quantity);
        $this->assertEquals(10.0, (float) $released->available_quantity);
        $this->assertEquals(10.0, $product->calculateEffectiveAvailableStock($this->location->id));
    }

    public function test_it_commits_reserved_stock_and_records_sales_movement(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Latte Macchiato',
            'code' => 'LM-01',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $this->unit->id,
            'base_cost' => 11000,
            'selling_price' => 27000,
        ]);

        $this->service->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 15.0,
            unitCost: 11000
        );

        // 1. Reserve 5
        $this->service->reserveStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 5.0
        );

        // 2. Commit 5 on payment verification
        $movement = $this->service->commitReservedStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            quantity: 5.0,
            unitCost: 11000,
            referenceId: 'test-order-123',
            referenceNumber: 'ORD-20260915-0001',
            movementType: StockMovement::TYPE_ONLINE_SALE
        );

        $this->assertNotNull($movement);
        $this->assertEquals(-5.0, (float) $movement->quantity_change);
        $this->assertEquals(10.0, (float) $movement->balance_after);
        $this->assertEquals(StockMovement::TYPE_ONLINE_SALE, $movement->movement_type);

        $stock = InventoryStock::where('product_id', $product->id)->first();
        $this->assertEquals(10.0, (float) $stock->quantity);
        $this->assertEquals(0.0, (float) $stock->reserved_quantity);
        $this->assertEquals(10.0, (float) $stock->available_quantity);
    }

    public function test_product_with_direct_material_reservation(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'code' => 'MAT-ARA-01',
            'unit_id' => $this->unit->id,
            'cost_per_unit' => 20000,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Americano Arabica',
            'code' => 'AME-01',
            'type' => Product::TYPE_GOODS,
            'direct_material_id' => $material->id,
            'output_unit_id' => $this->unit->id,
            'base_cost' => 20000,
            'selling_price' => 35000,
        ]);

        // Add 20 material stock
        $this->service->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            materialId: $material->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 20.0,
            unitCost: 20000
        );

        $this->assertEquals(20.0, $product->calculateEffectiveAvailableStock($this->location->id));

        // Reserve 6 via reserveProductStock
        $reserved = $this->service->reserveProductStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $product,
            productQuantity: 6.0
        );

        $this->assertCount(1, $reserved);
        $this->assertEquals(14.0, $product->calculateEffectiveAvailableStock($this->location->id));

        // Commit 6 via commitProductReservedStock
        $movements = $this->service->commitProductReservedStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $product,
            productQuantity: 6.0,
            unitCost: 20000,
            referenceId: 'test-order-999',
            referenceNumber: 'ORD-20260915-0099'
        );

        $this->assertCount(1, $movements);
        $this->assertEquals(14.0, $product->calculateEffectiveStock($this->location->id));
        $this->assertEquals(14.0, $product->calculateEffectiveAvailableStock($this->location->id));
    }
}
