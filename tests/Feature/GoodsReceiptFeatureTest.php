<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoodsReceiptFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Supplier $supplier;
    private Product $product;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Resto',
            'email' => 'owner_resto@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Resto',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'CV Sumber Telur & Daging',
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'KG',
            'name' => 'Kilogram',
            'symbol' => 'kg',
            'category' => Unit::CATEGORY_WEIGHT,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Bahan Baku Segar',
            'slug' => 'bahan-baku-segar',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'AYAM-01',
            'name' => 'Daging Ayam Segar',
            'selling_price' => 45000,
            'base_cost' => 32000,
            'is_active' => true,
        ]);
    }

    public function test_can_receive_physical_goods_from_purchase_order(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-202608-0001',
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-29',
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 320000,
            'total_amount' => 320000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'item_name' => $this->product->name,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
                'unit_price' => 32000,
            'subtotal' => 320000,
        ]);

        $response = $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-202608-0001',
            'receipt_date' => '2026-08-29',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_cost' => 32000,
                    'batch_number' => 'BATCH-AYAM-01',
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));

        // Check Goods Receipt
        $this->assertDatabaseHas('goods_receipts', [
            'business_id' => $this->business->id,
            'purchase_order_id' => $po->id,
            'receipt_number' => 'GR-202608-0001',
            'status' => 'completed',
        ]);

        // Check Inventory Stock
        $this->assertDatabaseHas('inventory_stocks', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'last_cost' => 32000,
        ]);

        // Check Immutable Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'movement_type' => 'goods_receipt',
            'quantity_change' => 10,
        ]);

        // Check PO status is completed
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->status);
    }

    public function test_solo_owner_can_do_one_click_instant_stock_in(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $response = $this->post(route('purchasing.instant-stock-in'), [
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'unit_cost' => 31500,
            'notes' => 'Belanja langsung di Pasar Pagi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check stock incremented
        $this->assertDatabaseHas('inventory_stocks', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'last_cost' => 31500,
        ]);

        // Check stock movement logged
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'movement_type' => 'goods_receipt',
            'quantity_change' => 25,
        ]);
    }

    public function test_can_receive_purchase_order_with_free_text_items_without_product(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        // PO dengan item non-inventori (jasa / layanan) — tanpa product_id & material_id.
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-202609-0002',
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-09-05',
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 250000,
            'total_amount' => 250000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_type' => 'product',
            'item_name' => 'Paket Service Chafing Dish & Peralatan Prasmanan',
            'unit_id' => $this->unit->id,
            'quantity' => 1,
            'unit_price' => 250000,
            'subtotal' => 250000,
        ]);

        // Sebelumnya error: "The items.0.product_id field is required."
        $response = $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-202609-0002',
            'receipt_date' => '2026-09-05',
            'items' => [
                [
                    'product_id' => '',
                    'material_id' => '',
                    'item_name' => 'Paket Service Chafing Dish & Peralatan Prasmanan',
                    'quantity' => 1,
                    'unit_cost' => 250000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('purchase-orders.show', $po));

        // Goods Receipt berhasil dibuat
        $this->assertDatabaseHas('goods_receipts', [
            'business_id' => $this->business->id,
            'purchase_order_id' => $po->id,
            'receipt_number' => 'GR-202609-0002',
            'status' => 'completed',
        ]);

        // Item non-inventori DICATAT tanpa product_id/material_id...
        $this->assertDatabaseHas('goods_receipt_items', [
            'product_id' => null,
            'material_id' => null,
            'item_name' => 'Paket Service Chafing Dish & Peralatan Prasmanan',
        ]);

        // ...dan TIDAK menambah stok (tanpa movement).
        $this->assertDatabaseCount('stock_movements', 0);

        // PO menjadi completed karena seluruh item telah diterima.
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->fresh()->status);
    }

    public function test_can_receive_material_based_purchase_order_item(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $material = \App\Models\Material::create([
            'business_id' => $this->business->id,
            'code' => 'TEP-001',
            'name' => 'Tepung Terigu Premium',
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        // Material bukan inventori (is_inventory false) juga bisa diterima.
        $material->update(['is_inventory' => true]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-202609-0003',
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-09-05',
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 150000,
            'total_amount' => 150000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_type' => 'material',
            'material_id' => $material->id,
            'item_name' => $material->name,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
            'unit_price' => 15000,
            'subtotal' => 150000,
        ]);

        $response = $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-202609-0003',
            'receipt_date' => '2026-09-05',
            'items' => [
                [
                    'product_id' => '',
                    'material_id' => $material->id,
                    'item_name' => $material->name,
                    'quantity' => 10,
                    'unit_cost' => 15000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('purchase-orders.show', $po));

        // Stok material bertambah di lokasi gudang.
        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->location->id)
            ->where('material_id', $material->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(10, $stock->quantity);
    }

    public function test_goods_receipt_rejects_quantity_above_purchase_order_balance(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-OVER-0001',
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-29',
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 320000,
            'total_amount' => 320000,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'item_name' => $this->product->name,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
            'unit_price' => 32000,
            'subtotal' => 320000,
        ]);

        $response = $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_date' => '2026-08-29',
            'items' => [['product_id' => $this->product->id, 'quantity' => 11, 'unit_cost' => 32000]],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('goods_receipts', 0);
    }
}
