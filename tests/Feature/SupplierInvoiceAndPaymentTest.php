<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\SupplierInvoiceService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\ChartOfAccount;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class SupplierInvoiceAndPaymentTest extends TestCase
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
            'name' => 'Owner Fase 3',
            'email' => 'fase3@example.com',
            'password' => 'password123',
        ]);
        $this->business = Business::create(['name' => 'Bisnis Fase 3']);
        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Bank Utama',
            'type' => CashAccount::TYPE_BANK,
            'current_balance' => 500000,
            'is_active' => true,
        ]);
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Fase 3',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);
        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Supplier Fase 3',
        ]);
        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);
        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Fase 3',
            'slug' => 'fase-3',
        ]);
        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'F3-001',
            'name' => 'Produk Fase 3',
            'selling_price' => 50000,
            'base_cost' => 30000,
            'is_active' => true,
        ]);
    }

    private function createPurchaseOrder(float $quantity = 10, float $unitCost = 30000): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'po_number' => 'PO-F3-' . Str::upper(Str::random(6)),
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-09-03',
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => $quantity * $unitCost,
            'total_amount' => $quantity * $unitCost,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'item_name' => $this->product->name,
            'unit_id' => $this->unit->id,
            'quantity' => $quantity,
            'unit_price' => $unitCost,
            'subtotal' => $quantity * $unitCost,
        ]);

        return $po;
    }

    public function test_goods_receipt_creates_supplier_invoice_and_balanced_journal(): void
    {
        $po = $this->createPurchaseOrder();

        $response = $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-F3-0001',
            'receipt_date' => '2026-09-03',
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 10,
                'unit_cost' => 30000,
            ]],
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));
        $invoice = SupplierInvoice::where('goods_receipt_id', GoodsReceipt::firstOrFail()->id)->firstOrFail();
        $journal = JournalEntry::where('reference_type', JournalEntry::REF_GOODS_RECEIPT)
            ->where('reference_id', $invoice->goods_receipt_id)
            ->firstOrFail();

        $this->assertSame(300000.0, $invoice->total_amount);
        $this->assertSame(300000.0, $invoice->balance_due);
        $this->assertSame(SupplierInvoice::STATUS_UNPAID, $invoice->status);
        $this->assertSame($journal->total_debit, $journal->total_credit);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => ChartOfAccount::where('business_id', $this->business->id)->where('code', '2-2001')->value('id'),
            'type' => 'credit',
            'amount' => 300000,
        ]);
    }

    public function test_supplier_payment_updates_balance_and_creates_balanced_journal(): void
    {
        $po = $this->createPurchaseOrder();
        $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-F3-0002',
            'receipt_date' => '2026-09-03',
            'items' => [['product_id' => $this->product->id, 'quantity' => 10, 'unit_cost' => 30000]],
        ]);
        $invoice = SupplierInvoice::firstOrFail();

        $response = $this->post(route('purchasing.bills.payments.store', $invoice), [
            'amount' => 125000,
            'payment_date' => '2026-09-03',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('purchasing.bills.show', $invoice));
        $invoice->refresh();
        $payment = $invoice->payments()->firstOrFail();
        $journal = JournalEntry::where('reference_type', JournalEntry::REF_SUPPLIER_PAYMENT)
            ->where('reference_id', $payment->id)
            ->firstOrFail();

        $this->assertSame(125000.0, $invoice->paid_amount);
        $this->assertSame(175000.0, $invoice->balance_due);
        $this->assertSame(SupplierInvoice::STATUS_PARTIAL, $invoice->status);
        $this->assertSame($journal->total_debit, $journal->total_credit);
    }

    public function test_wac_is_calculated_per_location_and_overpayment_is_rejected(): void
    {
        $stockService = new StockService;
        $stockService->recordMovement($this->business->id, $this->location->id, $this->product->id, 'goods_receipt', 10, 30000);
        $stockService->recordMovement($this->business->id, $this->location->id, $this->product->id, 'goods_receipt', 10, 40000);

        $stock = InventoryStock::where('business_id', $this->business->id)->firstOrFail();
        $this->assertSame(35000.0, $stock->avg_purchase_cost);

        $po = $this->createPurchaseOrder();
        $this->post(route('purchasing.receipts.store', $po), [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-F3-0003',
            'receipt_date' => '2026-09-03',
            'items' => [['product_id' => $this->product->id, 'quantity' => 10, 'unit_cost' => 30000]],
        ]);
        $invoice = SupplierInvoice::latest('created_at')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        (new SupplierInvoiceService)->recordPayment($invoice, [
            'amount' => 300001,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);
    }
}
