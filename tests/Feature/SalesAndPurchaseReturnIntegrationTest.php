<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\SalesReturnService;
use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\PurchaseReturnService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\Location;
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

class SalesAndPurchaseReturnIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Product $product;
    private Unit $unit;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->user = User::create(['name' => 'Return Owner', 'email' => 'return@example.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Return Business']);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $this->location = Location::create(['business_id' => $this->business->id, 'name' => 'Return Warehouse', 'type' => 'warehouse', 'is_primary' => true, 'is_active' => true]);
        $this->supplier = Supplier::create(['business_id' => $this->business->id, 'name' => 'Return Supplier']);
        $this->unit = Unit::create(['business_id' => $this->business->id, 'code' => 'PCS', 'name' => 'Pieces', 'category' => Unit::CATEGORY_QUANTITY]);
        $category = ProductCategory::create(['business_id' => $this->business->id, 'name' => 'Return', 'slug' => 'return']);
        $this->product = Product::create(['business_id' => $this->business->id, 'category_id' => $category->id, 'output_unit_id' => $this->unit->id, 'code' => 'RET-01', 'name' => 'Return Product', 'base_cost' => 30, 'selling_price' => 50, 'is_active' => true]);
    }

    public function test_sales_return_restores_stock_and_posts_balanced_reversal(): void
    {
        $customer = Customer::create(['business_id' => $this->business->id, 'name' => 'Return Customer']);
        $invoice = Invoice::create(['business_id' => $this->business->id, 'customer_id' => $customer->id, 'location_id' => $this->location->id, 'invoice_number' => 'INV-RET-01', 'invoice_date' => '2026-09-04', 'due_date' => '2026-10-04', 'status' => Invoice::STATUS_UNPAID, 'discount_type' => 'fixed', 'discount_value' => 0, 'tax_percentage' => 0, 'shipping_cost' => 0, 'created_by' => $this->user->id]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $this->product->id, 'item_name' => $this->product->name, 'quantity' => 4, 'unit_id' => $this->unit->id, 'unit_price' => 50, 'unit_hpp' => 30]);
        $return = (new SalesReturnService)->createFromInvoice($invoice, [['invoice_item_id' => $item->id, 'quantity' => 2]], ['reason' => 'Rusak', 'created_by' => $this->user->id]);
        (new SalesReturnService)->approve($return, $this->user->id);
        (new SalesReturnService)->complete($return, $this->user->id);

        $this->assertSame(2.0, InventoryStock::firstOrFail()->quantity);
        $journal = JournalEntry::where('reference_type', JournalEntry::REF_SALES_RETURN)->where('reference_id', $return->id)->firstOrFail();
        $this->assertSame($journal->total_debit, $journal->total_credit);
        $this->assertSame(1, JournalEntry::where('reference_type', JournalEntry::REF_SALES_RETURN)->count());
        $this->expectException(\InvalidArgumentException::class);
        (new SalesReturnService)->createFromInvoice($invoice, [['invoice_item_id' => $item->id, 'quantity' => 3]]);
    }

    public function test_purchase_return_deducts_stock_reduces_supplier_balance_and_is_idempotent(): void
    {
        $receipt = GoodsReceipt::create(['business_id' => $this->business->id, 'location_id' => $this->location->id, 'supplier_id' => $this->supplier->id, 'receipt_number' => 'GR-RET-01', 'receipt_date' => '2026-09-04', 'status' => 'completed', 'received_by' => $this->user->id]);
        $item = GoodsReceiptItem::create(['goods_receipt_id' => $receipt->id, 'product_id' => $this->product->id, 'quantity' => 10, 'unit_cost' => 30]);
        (new StockService)->recordMovement($this->business->id, $this->location->id, $this->product->id, 'goods_receipt', 10, 30, $receipt->id, $receipt->receipt_number);
        $bill = SupplierInvoice::create(['business_id' => $this->business->id, 'supplier_id' => $this->supplier->id, 'goods_receipt_id' => $receipt->id, 'invoice_number' => 'AP-RET-01', 'invoice_date' => '2026-09-04', 'total_amount' => 300, 'paid_amount' => 0, 'balance_due' => 300, 'status' => SupplierInvoice::STATUS_UNPAID]);
        $return = (new \App\Domain\Purchasing\PurchaseReturnService)->createFromGoodsReceipt($receipt, [['goods_receipt_item_id' => $item->id, 'quantity' => 3]], ['created_by' => $this->user->id]);
        (new PurchaseReturnService)->approve($return, $this->user->id);
        (new PurchaseReturnService)->complete($return, $this->user->id);
        (new PurchaseReturnService)->complete($return, $this->user->id);

        $this->assertSame(7.0, InventoryStock::firstOrFail()->quantity);
        $this->assertSame(210.0, $bill->refresh()->balance_due);
        $this->assertSame(1, JournalEntry::where('reference_type', JournalEntry::REF_PURCHASE_RETURN)->where('reference_id', $return->id)->count());
    }
}
