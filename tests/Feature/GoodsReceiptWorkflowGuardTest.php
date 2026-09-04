<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Location;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * BUG-002: Goods Receipt hanya boleh dilakukan pada PO yang sudah dikonfirmasi.
 * Draft / Cancelled / Completed dilarang; tidak ada mutasi stok saat ditolak.
 */
final class GoodsReceiptWorkflowGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Supplier $supplier;
    private Unit $unit;
    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Owner', 'email' => 'owner_gr@example.com', 'password' => 'password123']);
        $this->business = Business::create(['name' => 'Bisnis GR Guard']);
        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create(['business_id' => $this->business->id, 'name' => 'WH-A', 'type' => 'warehouse', 'is_active' => true]);
        $this->supplier = Supplier::create(['business_id' => $this->business->id, 'name' => 'Supplier Test']);
        $this->unit = Unit::create(['business_id' => $this->business->id, 'code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs', 'category' => 'quantity']);
        $this->material = Material::create(['business_id' => $this->business->id, 'code' => 'MAT-GR', 'name' => 'Bahan GR', 'unit_id' => $this->unit->id]);
    }

    private function makePo(string $status): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-' . strtoupper(Str::random(6)),
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => $status,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_type' => 'material',
            'material_id' => $this->material->id,
            'item_name' => $this->material->name,
            'unit_id' => $this->unit->id,
            'quantity' => 100,
            'unit_price' => 1000,
            'subtotal' => 100000,
        ]);
        return $po;
    }

    private function payload(PurchaseOrder $po): array
    {
        return [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-' . strtoupper(Str::random(6)),
            'receipt_date' => now()->toDateString(),
            'items' => [
                ['product_id' => '', 'material_id' => $this->material->id, 'item_name' => $this->material->name, 'quantity' => 10, 'unit_cost' => 1000],
            ],
        ];
    }

    public function test_draft_po_cannot_receive_and_stock_unchanged(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $po = $this->makePo(PurchaseOrder::STATUS_DRAFT);

        $this->post(route('purchasing.receipts.store', $po), $this->payload($po))->assertStatus(403);

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseCount('goods_receipt_items', 0);
        $this->assertSame(0, StockMovement::where('business_id', $this->business->id)->count());
    }

    public function test_confirmed_po_can_receive(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $po = $this->makePo(PurchaseOrder::STATUS_CONFIRMED);

        $this->post(route('purchasing.receipts.store', $po), $this->payload($po))
            ->assertRedirect(route('purchase-orders.show', $po));

        $this->assertDatabaseCount('goods_receipts', 1);
    }

    public function test_cancelled_po_cannot_receive(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $po = $this->makePo(PurchaseOrder::STATUS_CANCELLED);

        $this->post(route('purchasing.receipts.store', $po), $this->payload($po))->assertStatus(403);

        $this->assertDatabaseCount('goods_receipts', 0);
    }

    public function test_completed_po_cannot_receive_additional_quantity(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $po = $this->makePo(PurchaseOrder::STATUS_COMPLETED);

        $this->post(route('purchasing.receipts.store', $po), $this->payload($po))->assertStatus(403);

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertSame(0, StockMovement::where('business_id', $this->business->id)->count());
    }
}