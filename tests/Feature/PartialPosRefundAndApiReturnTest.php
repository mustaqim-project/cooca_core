<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PartialPosRefundAndApiReturnTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Product $product;
    private Supplier $supplier;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->user = User::create(['name' => 'API User', 'email' => 'api-return@example.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'API Return Business']);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $this->location = Location::create(['business_id' => $this->business->id, 'name' => 'API Warehouse', 'type' => 'warehouse', 'is_active' => true]);
        $this->unit = Unit::create(['business_id' => $this->business->id, 'code' => 'PCS', 'name' => 'Pieces', 'category' => Unit::CATEGORY_QUANTITY]);
        $category = ProductCategory::create(['business_id' => $this->business->id, 'name' => 'API', 'slug' => 'api']);
        $this->product = Product::create(['business_id' => $this->business->id, 'category_id' => $category->id, 'output_unit_id' => $this->unit->id, 'code' => 'API-01', 'name' => 'API Product', 'base_cost' => 10, 'selling_price' => 20, 'is_active' => true]);
        $this->supplier = Supplier::create(['business_id' => $this->business->id, 'name' => 'API Supplier']);
    }

    public function test_api_partial_pos_refund_restores_only_requested_quantity(): void
    {
        InventoryStock::create(['business_id' => $this->business->id, 'location_id' => $this->location->id, 'product_id' => $this->product->id, 'quantity' => 8, 'last_cost' => 10, 'avg_purchase_cost' => 10]);
        $order = PosOrder::create(['business_id' => $this->business->id, 'location_id' => $this->location->id, 'order_number' => 'POS-API-01', 'order_date' => '2026-09-04', 'status' => PosOrder::STATUS_COMPLETED, 'subtotal' => 80, 'total_amount' => 80, 'paid_amount' => 80, 'total_hpp_cost' => 40, 'total_gross_profit' => 40, 'user_id' => $this->user->id]);
        $item = PosOrderItem::create(['pos_order_id' => $order->id, 'product_id' => $this->product->id, 'product_name' => $this->product->name, 'unit_price' => 20, 'unit_cost_hpp' => 10, 'quantity' => 4, 'subtotal' => 80, 'total_price' => 80, 'total_hpp' => 40]);

        $response = $this->postJson('/api/v1/pos/orders/' . $order->id . '/refund', ['reason' => 'Rusak sebagian', 'items' => [['pos_order_item_id' => $item->id, 'quantity' => 1]]]);
        $response->assertOk()->assertJsonPath('order.status', PosOrder::STATUS_PARTIAL_REFUND);
        $this->assertSame(9.0, (float) InventoryStock::firstOrFail()->quantity);
        $this->assertDatabaseHas('journal_entries', ['reference_type' => 'pos_refund']);
    }

    public function test_api_purchase_return_routes_create_and_complete(): void
    {
        $receipt = GoodsReceipt::create(['business_id' => $this->business->id, 'location_id' => $this->location->id, 'supplier_id' => $this->supplier->id, 'receipt_number' => 'GR-API-01', 'receipt_date' => '2026-09-04', 'status' => 'completed']);
        $item = GoodsReceiptItem::create(['goods_receipt_id' => $receipt->id, 'product_id' => $this->product->id, 'quantity' => 3, 'unit_cost' => 10]);
        SupplierInvoice::create(['business_id' => $this->business->id, 'supplier_id' => $this->supplier->id, 'goods_receipt_id' => $receipt->id, 'invoice_number' => 'AP-API-01', 'invoice_date' => '2026-09-04', 'total_amount' => 30, 'balance_due' => 30, 'status' => SupplierInvoice::STATUS_UNPAID]);
        InventoryStock::create(['business_id' => $this->business->id, 'location_id' => $this->location->id, 'product_id' => $this->product->id, 'quantity' => 3, 'last_cost' => 10, 'avg_purchase_cost' => 10]);

        $create = $this->postJson('/api/v1/purchasing/returns', ['goods_receipt_id' => $receipt->id, 'reason' => 'Cacat', 'items' => [['goods_receipt_item_id' => $item->id, 'quantity' => 1]]]);
        $create->assertCreated();
        $return = PurchaseReturn::firstOrFail();
        $this->postJson('/api/v1/purchasing/returns/' . $return->id . '/approve')->assertOk();
        $this->postJson('/api/v1/purchasing/returns/' . $return->id . '/complete')->assertOk();
        $this->assertSame(2.0, (float) InventoryStock::firstOrFail()->quantity);
    }
}
