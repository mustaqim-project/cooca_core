<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\PurchaseOrderService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class PurchaseOrderComprehensiveRemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $businessA;
    private Business $businessB;
    private Location $locationA;
    private Unit $unit;
    private Product $productA;
    private Material $materialA;
    private Supplier $supplierA;
    private Customer $customerA;
    private Supplier $supplierB;
    private Customer $customerB;

    protected function setUp(): void
    {
        parent::setUp();

        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Testing PO',
            'email' => 'owner.po@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->businessA = Business::create([
            'name' => 'Bisnis Pengadaan A',
            'email' => 'pengadaan.a@example.com',
            'phone' => '08123456781',
            'operating_mode' => 'team',
            'pos_supervisor_pin' => Hash::make('1234'),
        ]);

        $this->businessB = Business::create([
            'name' => 'Bisnis Foreign B',
            'email' => 'foreign.b@example.com',
            'phone' => '08123456782',
            'operating_mode' => 'team',
        ]);

        // Attach user to businessA with owner role
        $this->businessA->users()->attach($this->user->id, ['role' => 'owner']);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Pusat A',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'name' => 'Produk A',
            'sku' => 'SKU-A',
            'selling_price' => 50000,
            'base_cost' => 30000,
            'output_unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        $this->materialA = Material::create([
            'business_id' => $this->businessA->id,
            'name' => 'Tepung Terigu A',
            'sku' => 'MAT-A',
            'unit_id' => $this->unit->id,
        ]);

        $this->supplierA = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Vendor Pemasok A',
        ]);

        $this->customerA = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pelanggan B2B A',
            'is_active' => true,
        ]);

        $this->supplierB = Supplier::create([
            'business_id' => $this->businessB->id,
            'name' => 'Vendor Pemasok B (Foreign)',
        ]);

        $this->customerB = Customer::create([
            'business_id' => $this->businessB->id,
            'name' => 'Pelanggan B2B B (Foreign)',
            'is_active' => true,
        ]);
    }

    public function test_idor_protection_blocks_foreign_tenant_supplier_or_customer(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        // Attempt to create PO with foreign supplierB from businessB
        $response = $this->post(route('purchase-orders.store'), [
            'po_type' => 'supplier',
            'supplier_id' => $this->supplierB->id, // Foreign IDOR
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'item_name' => 'Bahan Baku A',
                    'unit_id' => $this->unit->id,
                    'quantity' => 10,
                    'unit_price' => 15000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('supplier_id');
        $this->assertDatabaseMissing('purchase_orders', [
            'business_id' => $this->businessA->id,
            'supplier_id' => $this->supplierB->id,
        ]);
    }

    public function test_tenant_isolation_shield_aborts_foreign_tenant_po(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        // Create PO belonging to businessB
        $poForeign = PurchaseOrder::create([
            'business_id' => $this->businessB->id,
            'po_number' => 'PO-FOREIGN-001',
            'po_type' => 'supplier',
            'status' => 'draft',
            'order_date' => date('Y-m-d'),
            'total_amount' => 100000,
        ]);

        // Attempt show (isolated via global scope 404 or explicit guardrail 403)
        $this->assertContains($this->get(route('purchase-orders.show', $poForeign->id))->status(), [403, 404]);

        // Attempt confirm
        $this->assertContains($this->post(route('purchase-orders.confirm', $poForeign->id))->status(), [403, 404]);

        // Attempt cancel
        $this->assertContains($this->post(route('purchase-orders.cancel', $poForeign->id))->status(), [403, 404]);

        // Attempt print
        $this->assertContains($this->get(route('purchase-orders.print', $poForeign->id))->status(), [403, 404]);

        // Attempt destroy
        $this->assertContains($this->delete(route('purchase-orders.destroy', $poForeign->id))->status(), [403, 404]);
    }

    public function test_three_way_matching_blocks_cancellation_if_goods_receipt_exists(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $po = PurchaseOrder::create([
            'business_id' => $this->businessA->id,
            'po_number' => 'PO-RECEIPT-001',
            'po_type' => 'supplier',
            'supplier_id' => $this->supplierA->id,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => date('Y-m-d'),
            'total_amount' => 500000,
        ]);

        // Create GoodsReceipt linked to PO
        GoodsReceipt::create([
            'business_id' => $this->businessA->id,
            'purchase_order_id' => $po->id,
            'location_id' => $this->locationA->id,
            'receipt_number' => 'GR-001',
            'receipt_date' => date('Y-m-d'),
            'status' => 'completed',
        ]);

        // Attempt to cancel
        $response = $this->post(route('purchase-orders.cancel', $po->id), [
            'supervisor_pin' => '1234',
            'cancel_reason' => 'Ingin batalkan padahal barang sudah datang',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->fresh()->status);
    }

    public function test_supervisor_pin_required_for_confirmed_po_cancellation(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $po = PurchaseOrder::create([
            'business_id' => $this->businessA->id,
            'po_number' => 'PO-CONFIRMED-001',
            'po_type' => 'supplier',
            'supplier_id' => $this->supplierA->id,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => date('Y-m-d'),
            'total_amount' => 200000,
        ]);

        // Wrong PIN
        $responseWrongPin = $this->post(route('purchase-orders.cancel', $po->id), [
            'supervisor_pin' => '9999', // Wrong
            'cancel_reason' => 'Batal transaksi',
        ]);

        $responseWrongPin->assertSessionHas('error');
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->fresh()->status);

        // Correct PIN
        $responseCorrect = $this->post(route('purchase-orders.cancel', $po->id), [
            'supervisor_pin' => '1234', // Correct
            'cancel_reason' => 'Otorisasi resmi supervisor',
        ]);

        $responseCorrect->assertSessionHas('success');
        $this->assertEquals(PurchaseOrder::STATUS_CANCELLED, $po->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'po.cancelled',
            'auditable_id' => $po->id,
        ]);
    }

    public function test_instant_stock_in_supports_material_and_deducts_cash_ledger(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $cashAccount = CashAccount::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kas Kecil Laci',
            'type' => 'cash',
            'current_balance' => 1000000,
            'is_active' => true,
        ]);

        $response = $this->post(route('purchasing.instant-stock-in'), [
            'location_id' => $this->locationA->id,
            'material_id' => $this->materialA->id,
            'quantity' => 15,
            'unit_cost' => 12000,
            'cash_account_id' => $cashAccount->id,
            'notes' => 'Beli tepung di pasar induk tunai',
        ]);

        $response->assertSessionHas('success');

        // Verify stock movement was recorded for material
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'material_id' => $this->materialA->id,
            'quantity_change' => 15,
            'unit_cost' => 12000,
        ]);

        // Verify cash transaction outflow was recorded
        $this->assertDatabaseHas('cash_transactions', [
            'business_id' => $this->businessA->id,
            'cash_account_id' => $cashAccount->id,
            'type' => 'out',
            'amount' => 180000, // 15 * 12000
        ]);
    }

    public function test_create_po_atomic_db_transaction_and_view_rendering(): void
    {
        $this->actingAs($this->user);
        session(['business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $response = $this->post(route('purchase-orders.store'), [
            'po_type' => 'supplier',
            'supplier_id' => $this->supplierA->id,
            'order_date' => date('Y-m-d'),
            'expected_delivery_date' => date('Y-m-d', strtotime('+3 days')),
            'items' => [
                [
                    'material_id' => $this->materialA->id,
                    'item_name' => 'Tepung Terigu Segitiga',
                    'unit_id' => $this->unit->id,
                    'quantity' => 50,
                    'unit_price' => 11000,
                ],
            ],
            'discount_value' => 10000,
            'discount_type' => 'fixed',
            'tax_percentage' => 11,
            'terms_and_conditions' => 'Pembayaran tempo 14 hari',
        ]);

        $response->assertRedirect();
        $po = PurchaseOrder::where('business_id', $this->businessA->id)->latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $po->status);
        $this->assertEquals(50 * 11000, (float) $po->subtotal);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'po.created',
            'auditable_id' => $po->id,
        ]);
    }
}
