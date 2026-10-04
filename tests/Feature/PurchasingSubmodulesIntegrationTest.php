<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchasingSubmodulesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $userA;
    private User $userB;
    private Location $locationA;
    private Supplier $supplierA;
    private Unit $unit;
    private Material $materialA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->userA = User::create([
            'name' => 'Owner Tenant A',
            'email' => 'owner_a@example.com',
            'password' => 'password',
        ]);

        $this->businessA = Business::create([
            'name' => 'Tenant A Business',
            'pos_supervisor_pin' => Hash::make('123456'),
        ]);
        $this->businessA->users()->attach($this->userA->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $this->businessB = Business::create([
            'name' => 'Tenant B Business',
        ]);
        $this->userB = User::create([
            'name' => 'Owner Tenant B',
            'email' => 'owner_b@example.com',
            'password' => 'password',
        ]);
        $this->businessB->users()->attach($this->userB->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->userB->update(['active_business_id' => $this->businessB->id]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Utama A',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->supplierA = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Supplier Segar A',
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'KG',
            'name' => 'Kilogram',
            'category' => Unit::CATEGORY_WEIGHT,
        ]);

        $matCategory = MaterialCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Bahan Baku',
            'slug' => 'bahan-baku',
        ]);

        $this->materialA = Material::create([
            'business_id' => $this->businessA->id,
            'category_id' => $matCategory->id,
            'unit_id' => $this->unit->id,
            'code' => 'MAT-FLOUR-01',
            'name' => 'Tepung Terigu Segitiga',
            'is_active' => true,
        ]);
    }

    public function test_tenant_cannot_create_purchase_return_from_other_tenant_goods_receipt(): void
    {
        // Setup GR for Tenant B
        $locationB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Gudang B',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);
        $supplierB = Supplier::create([
            'business_id' => $this->businessB->id,
            'name' => 'Supplier B',
        ]);
        $receiptB = GoodsReceipt::create([
            'business_id' => $this->businessB->id,
            'location_id' => $locationB->id,
            'supplier_id' => $supplierB->id,
            'receipt_number' => 'GR-B-001',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);
        $itemB = GoodsReceiptItem::create([
            'goods_receipt_id' => $receiptB->id,
            'item_name' => 'Barang Tenant B',
            'quantity' => 10,
            'unit_cost' => 50000,
        ]);

        // Tenant A attempts to return Tenant B's Goods Receipt
        $this->actingAs($this->userA);
        session(['active_business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $response = $this->post(route('purchase.returns.store'), [
            'goods_receipt_id' => $receiptB->id,
            'reason' => 'Mencoba IDOR Cross-Tenant',
            'items' => [
                [
                    'goods_receipt_item_id' => $itemB->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        // Expect validation error / redirect back with errors
        $response->assertSessionHasErrors(['goods_receipt_id']);
        $this->assertDatabaseMissing('purchase_returns', [
            'goods_receipt_id' => $receiptB->id,
            'business_id' => $this->businessA->id,
        ]);
    }

    public function test_purchase_return_supports_raw_materials_without_database_error(): void
    {
        $this->actingAs($this->userA);
        session(['active_business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        // Pre-populate inventory stock so return completion has stock to deduct
        app(StockService::class)->recordMovement(
            businessId: $this->businessA->id,
            locationId: $this->locationA->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 50.0,
            unitCost: 12000.0,
            materialId: $this->materialA->id
        );

        $receiptA = GoodsReceipt::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'supplier_id' => $this->supplierA->id,
            'receipt_number' => 'GR-MAT-001',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $itemA = GoodsReceiptItem::create([
            'goods_receipt_id' => $receiptA->id,
            'material_id' => $this->materialA->id,
            'product_id' => null,
            'item_name' => $this->materialA->name,
            'quantity' => 25.5,
            'unit_cost' => 12000,
        ]);

        $supplierInvoice = SupplierInvoice::create([
            'business_id' => $this->businessA->id,
            'supplier_id' => $this->supplierA->id,
            'goods_receipt_id' => $receiptA->id,
            'invoice_number' => 'AP-GR-MAT-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 306000,
            'paid_amount' => 0,
            'balance_due' => 306000,
            'status' => SupplierInvoice::STATUS_UNPAID,
        ]);

        $response = $this->post(route('purchase.returns.store'), [
            'goods_receipt_id' => $receiptA->id,
            'reason' => 'Bahan baku karung bocor basah terkena hujan',
            'items' => [
                [
                    'goods_receipt_item_id' => $itemA->id,
                    'quantity' => 5.5,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('purchase_returns', [
            'business_id' => $this->businessA->id,
            'goods_receipt_id' => $receiptA->id,
            'status' => PurchaseReturn::STATUS_DRAFT,
            'total_amount' => 66000, // 5.5 * 12000
        ]);

        $return = PurchaseReturn::where('goods_receipt_id', $receiptA->id)->firstOrFail();
        $this->assertDatabaseHas('purchase_return_items', [
            'purchase_return_id' => $return->id,
            'material_id' => $this->materialA->id,
            'product_id' => null,
            'quantity' => 5.5,
            'unit_cost' => 12000,
            'subtotal' => 66000,
        ]);

        // Now test approval with supervisor PIN
        $approveResponse = $this->post(route('purchase.returns.approve', $return), [
            'pin' => '123456',
        ]);
        $approveResponse->assertSessionHasNoErrors();
        $this->assertEquals(PurchaseReturn::STATUS_APPROVED, $return->fresh()->status);

        // Now test completion (deducting stock) with supervisor PIN
        $completeResponse = $this->post(route('purchase.returns.complete', $return), [
            'pin' => '123456',
        ]);
        $completeResponse->assertSessionHasNoErrors();
        $this->assertEquals(PurchaseReturn::STATUS_COMPLETED, $return->fresh()->status);

        // Verify that AP balance is reduced
        $this->assertEquals(240000, $supplierInvoice->fresh()->balance_due);
    }

    public function test_supplier_invoice_payment_records_cash_outflow_with_selected_account(): void
    {
        $this->actingAs($this->userA);
        session(['active_business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $cashAccount = CashAccount::create([
            'business_id' => $this->businessA->id,
            'name' => 'Rekening Operasional BCA',
            'type' => 'bank',
            'current_balance' => 10000000,
            'is_active' => true,
        ]);

        $receipt = GoodsReceipt::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'supplier_id' => $this->supplierA->id,
            'receipt_number' => 'GR-PAY-01',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $invoice = SupplierInvoice::create([
            'business_id' => $this->businessA->id,
            'supplier_id' => $this->supplierA->id,
            'goods_receipt_id' => $receipt->id,
            'invoice_number' => 'AP-TEST-PAY-01',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'total_amount' => 1000000,
            'paid_amount' => 0,
            'balance_due' => 1000000,
            'status' => SupplierInvoice::STATUS_UNPAID,
        ]);

        $response = $this->post(route('purchasing.bills.payments.store', $invoice), [
            'amount' => 400000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'cash_account_id' => $cashAccount->id,
            'reference_number' => 'TRF-BCA-88912',
            'notes' => 'Pembayaran termin 1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(400000, $invoice->fresh()->paid_amount);
        $this->assertEquals(600000, $invoice->fresh()->balance_due);
        $this->assertEquals(SupplierInvoice::STATUS_PARTIAL, $invoice->fresh()->status);

        // Verify CashAccount balance deducted
        $this->assertEquals(9600000, $cashAccount->fresh()->current_balance);
    }

    public function test_supplier_invoice_payment_requires_supervisor_pin_for_large_disbursements(): void
    {
        $this->actingAs($this->userA);
        session(['active_business_id' => $this->businessA->id]);
        Context::setBusiness($this->businessA);

        $cashAccount = CashAccount::create([
            'business_id' => $this->businessA->id,
            'name' => 'Rekening Bank Mandiri',
            'type' => 'bank',
            'current_balance' => 20000000,
            'is_active' => true,
        ]);

        $receipt = GoodsReceipt::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'supplier_id' => $this->supplierA->id,
            'receipt_number' => 'GR-PAY-02',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $invoice = SupplierInvoice::create([
            'business_id' => $this->businessA->id,
            'supplier_id' => $this->supplierA->id,
            'goods_receipt_id' => $receipt->id,
            'invoice_number' => 'AP-LARGE-01',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 8000000,
            'paid_amount' => 0,
            'balance_due' => 8000000,
            'status' => SupplierInvoice::STATUS_UNPAID,
        ]);

        // Attempting to pay >= 5,000,000 without supervisor PIN
        $response = $this->post(route('purchasing.bills.payments.store', $invoice), [
            'amount' => 6000000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'cash_account_id' => $cashAccount->id,
            'supervisor_pin' => 'wrong-pin',
        ]);

        $response->assertSessionHasErrors(['supervisor_pin']);
        $this->assertEquals(0, $invoice->fresh()->paid_amount);

        // With correct supervisor PIN
        $successResponse = $this->post(route('purchasing.bills.payments.store', $invoice), [
            'amount' => 6000000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'cash_account_id' => $cashAccount->id,
            'supervisor_pin' => '123456',
        ]);

        $successResponse->assertSessionHasNoErrors();
        $this->assertEquals(6000000, $invoice->fresh()->paid_amount);
        $this->assertEquals(2000000, $invoice->fresh()->balance_due);
    }
}
