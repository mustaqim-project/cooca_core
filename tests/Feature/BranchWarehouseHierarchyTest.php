<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Domain\Pos\PosOrderService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchWarehouseHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Unit $pieceUnit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Juragan Retail',
            'email' => 'juragan@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567899',
        ]);

        $this->business = Business::create([
            'name' => 'PT Retail Nusantara',
            'slug' => 'pt-retail-nusantara',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->pieceUnit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);
    }

    public function test_can_create_central_warehouse_without_parent_via_http(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('warehouse.store'), [
                'name' => 'Gudang Pusat Cikarang (DC)',
                'type' => 'warehouse',
                'code' => 'DC-CKR-01',
                'address' => 'Kawasan Industri Cikarang Blok B2',
                'parent_id' => null,
            ]);

        $response->assertRedirect(route('warehouse.index'));
        $response->assertSessionHas('success');

        $dc = Location::where('business_id', $this->business->id)
            ->where('code', 'DC-CKR-01')
            ->first();

        $this->assertNotNull($dc);
        $this->assertNull($dc->parent_id);
        $this->assertTrue($dc->isRoot());
        $this->assertTrue($dc->isCentralWarehouse());
        $this->assertFalse($dc->isSubWarehouse());
    }

    public function test_can_create_branch_outlet_and_multiple_sub_warehouses(): void
    {
        // 1. Create Branch Outlet
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Mall Senayan',
            'slug' => 'outlet-mall-senayan',
            'type' => 'outlet',
            'code' => 'OUT-SNY',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($outlet->isRoot());
        $this->assertFalse($outlet->isSubWarehouse());

        // 2. Create Sub-Warehouse 1: Etalase Depan via HTTP
        $resp1 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('warehouse.store'), [
                'name' => 'Etalase Depan Senayan',
                'type' => 'warehouse',
                'code' => 'SNY-DISP',
                'parent_id' => $outlet->id,
                'address' => 'Lantai 1 Mall Senayan',
            ]);

        $resp1->assertRedirect(route('warehouse.index'));

        // 3. Create Sub-Warehouse 2: Gudang Belakang via HTTP
        $resp2 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('warehouse.store'), [
                'name' => 'Gudang Belakang Senayan (Storage)',
                'type' => 'warehouse',
                'code' => 'SNY-STORE',
                'parent_id' => $outlet->id,
                'address' => 'Basement 1 Mall Senayan',
            ]);

        $resp2->assertRedirect(route('warehouse.index'));

        $subDisplay = Location::where('code', 'SNY-DISP')->first();
        $subStorage = Location::where('code', 'SNY-STORE')->first();

        $this->assertNotNull($subDisplay);
        $this->assertNotNull($subStorage);

        $this->assertTrue($subDisplay->isSubWarehouse());
        $this->assertSame($outlet->id, $subDisplay->parent_id);
        $this->assertSame('Outlet Mall Senayan', $subDisplay->parent->name);

        $this->assertTrue($subStorage->isSubWarehouse());
        $this->assertSame($outlet->id, $subStorage->parent_id);

        // Verify parent has 2 children
        $outlet->refresh();
        $this->assertCount(2, $outlet->children);
        $this->assertCount(2, $outlet->childWarehouses);
    }

    public function test_tenant_isolation_cannot_assign_parent_from_another_business(): void
    {
        // Business B with another outlet
        $businessB = Business::create([
            'name' => 'Toko Sebelah Ltd',
            'slug' => 'toko-sebelah-ltd',
        ]);
        $otherOutlet = Location::create([
            'business_id' => $businessB->id,
            'name' => 'Cabang Toko Sebelah',
            'slug' => 'cabang-toko-sebelah',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        // Attempt to create sub-warehouse in Business A pointing to Business B's outlet
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('warehouse.store'), [
                'name' => 'Gudang Ilegal Lintas Tenant',
                'type' => 'warehouse',
                'parent_id' => $otherOutlet->id,
            ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('locations', [
            'name' => 'Gudang Ilegal Lintas Tenant',
        ]);
    }

    public function test_anti_circular_validation_location_cannot_be_its_own_parent(): void
    {
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Grand Indonesia',
            'slug' => 'outlet-grand-indonesia',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        // Try to update outlet setting parent_id to itself
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('warehouse.update', $outlet->id), [
                'name' => 'Outlet Grand Indonesia',
                'type' => 'outlet',
                'parent_id' => $outlet->id,
            ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_anti_circular_validation_cannot_set_descendant_as_parent(): void
    {
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Tebet',
            'slug' => 'outlet-tebet',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subWarehouse = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Sub Gudang Tebet',
            'slug' => 'sub-gudang-tebet',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        // Try to set outlet's parent as its child
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('warehouse.update', $outlet->id), [
                'name' => 'Outlet Tebet',
                'type' => 'outlet',
                'parent_id' => $subWarehouse->id,
            ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_effective_stock_aggregates_across_branch_child_warehouses(): void
    {
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Bandung',
            'slug' => 'cabang-bandung',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $frontDisplay = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Etalase Depan Bandung',
            'slug' => 'etalase-depan-bandung',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        $backStorage = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Belakang Bandung',
            'slug' => 'gudang-belakang-bandung',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika Java 250g',
            'slug' => 'kopi-arabika-java-250g',
            'type' => Product::TYPE_GOODS,
            'price' => 85000,
            'base_cost' => 45000,
            'output_unit_id' => $this->pieceUnit->id,
            'is_active' => true,
        ]);

        // Put 12 units in front display shelf
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $frontDisplay->id,
            'product_id' => $product->id,
            'quantity' => 12.0,
            'reserved_quantity' => 0.0,
            'last_cost' => 45000,
        ]);

        // Put 28 units in back storage
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $backStorage->id,
            'product_id' => $product->id,
            'quantity' => 28.0,
            'reserved_quantity' => 0.0,
            'last_cost' => 45000,
        ]);

        // 1. Querying front display specifically returns 12
        $this->assertEquals(12.0, $product->calculateEffectiveStock($frontDisplay->id));
        $this->assertEquals(12.0, $product->calculateEffectiveAvailableStock($frontDisplay->id));

        // 2. Querying back storage specifically returns 28
        $this->assertEquals(28.0, $product->calculateEffectiveStock($backStorage->id));
        $this->assertEquals(28.0, $product->calculateEffectiveAvailableStock($backStorage->id));

        // 3. Querying parent outlet aggregates both children (12 + 28 = 40)
        $this->assertEquals(40.0, $product->calculateEffectiveStock($outlet->id));
        $this->assertEquals(40.0, $product->calculateEffectiveAvailableStock($outlet->id));
    }

    public function test_pos_checkout_at_branch_deducts_from_sub_warehouse_with_available_stock(): void
    {
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Malioboro',
            'slug' => 'outlet-malioboro',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $displayShelf = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Etalase Kasir Malioboro',
            'slug' => 'etalase-kasir-malioboro',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Bakpia Kukus Premium',
            'slug' => 'bakpia-kukus-premium',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 50000,
            'base_cost' => 30000,
            'output_unit_id' => $this->pieceUnit->id,
            'is_active' => true,
        ]);

        // Stock is physically in display shelf
        $stock = InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $displayShelf->id,
            'product_id' => $product->id,
            'quantity' => 15.0,
            'reserved_quantity' => 0.0,
            'last_cost' => 30000,
        ]);

        $register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $outlet->id,
            'name' => 'Kasir 1 Malioboro',
            'code' => 'REG-MLB-01',
            'is_active' => true,
        ]);

        $shift = PosShift::create([
            'business_id' => $this->business->id,
            'pos_register_id' => $register->id,
            'location_id' => $outlet->id,
            'user_id' => $this->owner->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        $orderService = app(PosOrderService::class);

        // Cashier checks out 3 boxes at the branch outlet ($outlet->id)
        $order = $orderService->checkout(
            business: $this->business,
            cashier: $this->owner,
            itemsData: [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 50000,
                    'quantity' => 3,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => 'cash',
                    'amount' => 150000,
                ],
            ],
            attributes: [
                'location_id' => $outlet->id,
                'pos_register_id' => $register->id,
            ],
            shift: $shift
        );

        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->status);
        $this->assertEquals(150000, $order->total_amount);

        // Stock in displayShelf should be reduced from 15 to 12
        $stock->refresh();
        $this->assertEquals(12.0, (float) $stock->quantity);

        // Remaining aggregated stock for the outlet should be 12
        $this->assertEquals(12.0, $product->calculateEffectiveStock($outlet->id));
    }

    public function test_internal_stock_transfer_between_storage_and_display_under_same_branch(): void
    {
        $outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Surabaya Town Square',
            'slug' => 'outlet-sutos',
            'type' => 'outlet',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $storage = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Belakang Sutos',
            'slug' => 'gudang-belakang-sutos',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        $display = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Etalase Toko Sutos',
            'slug' => 'etalase-toko-sutos',
            'type' => 'warehouse',
            'parent_id' => $outlet->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Botol Minum Termos 500ml',
            'slug' => 'botol-minum-termos-500ml',
            'type' => Product::TYPE_GOODS,
            'price' => 120000,
            'base_cost' => 60000,
            'output_unit_id' => $this->pieceUnit->id,
            'is_active' => true,
        ]);

        // Initial stock: 50 in storage, 5 in display
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $storage->id,
            'product_id' => $product->id,
            'quantity' => 50.0,
            'reserved_quantity' => 0.0,
            'last_cost' => 60000,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $display->id,
            'product_id' => $product->id,
            'quantity' => 5.0,
            'reserved_quantity' => 0.0,
            'last_cost' => 60000,
        ]);

        // Create internal stock transfer: 20 units from storage to display
        $transfer = StockTransfer::create([
            'business_id' => $this->business->id,
            'source_location_id' => $storage->id,
            'destination_location_id' => $display->id,
            'transfer_number' => 'TRF-INT-001',
            'transfer_date' => now()->toDateString(),
            'status' => StockTransfer::STATUS_PENDING,
            'created_by' => $this->owner->id,
            'notes' => 'Restock etalase depan dari gudang belakang',
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $product->id,
            'quantity' => 20.0,
            'unit_cost' => 60000,
        ]);

        // Complete the transfer
        $stockService = app(StockService::class);
        $stockService->completeStockTransfer($transfer, $this->owner);

        // Verify storage is now 30 and display is 25
        $storageStock = InventoryStock::where('location_id', $storage->id)->where('product_id', $product->id)->first();
        $displayStock = InventoryStock::where('location_id', $display->id)->where('product_id', $product->id)->first();

        $this->assertEquals(30.0, (float) $storageStock->quantity);
        $this->assertEquals(25.0, (float) $displayStock->quantity);

        // Total branch stock remains 55
        $this->assertEquals(55.0, $product->calculateEffectiveStock($outlet->id));
    }
}
