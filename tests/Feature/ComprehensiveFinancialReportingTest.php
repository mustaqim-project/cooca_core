<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Report\FinancialReportService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\CashAccount;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ComprehensiveFinancialReportingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Customer $customer;
    private Supplier $supplier;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Laporan',
            'email' => 'owner.report@cooca.id',
            'password' => bcrypt('secret123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Maju',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $unit = Unit::create(['business_id' => $this->business->id, 'name' => 'Pcs', 'code' => 'pcs', 'category' => Unit::CATEGORY_QUANTITY]);
        $this->location = Location::create(['business_id' => $this->business->id, 'name' => 'Gudang Pusat', 'type' => 'warehouse']);

        $this->customer = Customer::create(['business_id' => $this->business->id, 'name' => 'Budi Client', 'phone' => '0812345678']);
        $this->supplier = Supplier::create(['business_id' => $this->business->id, 'name' => 'Vendor Bahan']);

        $this->productA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika 250g',
            'sku' => 'KOP-ARB-250',
            'output_unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
            'is_active' => true,
        ]);

        $this->productB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Teh Melati Premium',
            'sku' => 'TEH-MEL-100',
            'output_unit_id' => $unit->id,
            'selling_price' => 30000,
            'purchase_price' => 15000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->productA->id,
            'quantity' => 100,
            'avg_purchase_cost' => 30000,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->productB->id,
            'quantity' => 50,
            'avg_purchase_cost' => 15000,
        ]);
    }

    public function test_income_statement_calculation_with_pos_invoice_returns_and_expenses(): void
    {
        $today = Carbon::today();

        // 1. POS Order: 2 unit Product A = 100.000, Discount 10.000 -> Net: 90.000, HPP: 2 * 30.000 = 60.000
        $posOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'POS-2026-001',
            'order_date' => $today->toDateTimeString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'total_amount' => 90000,
            'total_hpp_cost' => 60000,
            'total_gross_profit' => 30000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $posOrder->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'unit_price' => 50000,
            'unit_cost_hpp' => 30000,
            'quantity' => 2,
            'subtotal' => 100000,
            'total_price' => 90000,
            'total_hpp' => 60000,
        ]);

        // 2. Invoice: 4 unit Product B = 120.000, HPP: 4 * 15.000 = 60.000
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2026-001',
            'invoice_date' => $today->toDateString(),
            'due_date' => $today->copy()->addDays(14)->toDateString(),
            'status' => Invoice::STATUS_PAID,
            'subtotal' => 120000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 120000,
            'paid_amount' => 120000,
            'balance_due' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $this->productB->id,
            'unit_id' => $this->productB->output_unit_id,
            'item_name' => $this->productB->name,
            'unit_price' => 30000,
            'unit_hpp' => 15000,
            'quantity' => 4,
            'subtotal' => 120000,
            'total_hpp' => 60000,
            'gross_profit' => 60000,
        ]);

        // 3. Sales Return: 1 unit Product B = 30.000 returned, HPP Recovery: 15.000
        $return = SalesReturn::create([
            'business_id' => $this->business->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'location_id' => $this->location->id,
            'return_number' => 'SR-2026-001',
            'return_date' => $today->toDateString(),
            'reason' => 'Salah ukuran',
            'status' => SalesReturn::STATUS_COMPLETED,
            'total_amount' => 30000,
            'refund_method' => SalesReturn::REFUND_CASH,
        ]);

        SalesReturnItem::create([
            'sales_return_id' => $return->id,
            'product_id' => $this->productB->id,
            'item_name' => $this->productB->name,
            'quantity' => 1,
            'unit_price' => 30000,
            'unit_hpp' => 15000,
            'subtotal' => 30000,
        ]);

        // 4. Expense: Listrik & Internet = 25.000
        Expense::create([
            'business_id' => $this->business->id,
            'expense_number' => 'EXP-001',
            'expense_date' => $today->toDateString(),
            'category' => 'Utilitas & Listrik',
            'amount' => 25000,
            'description' => 'Bayar token PLN',
        ]);

        $service = new FinancialReportService();
        $report = $service->getIncomeStatement($this->business, $today, $today);

        // Assertions:
        // Gross Sales = 100.000 (POS) + 120.000 (INV) = 220.000
        // Discounts = 10.000
        // Returns = 30.000
        // Net Sales = 220.000 - 10.000 - 30.000 = 180.000
        $this->assertEquals(220000, $report['revenues']['total_gross_sales']);
        $this->assertEquals(30000, $report['revenues']['sales_returns']);
        $this->assertEquals(180000, $report['revenues']['net_sales']);

        // COGS = 60.000 (POS) + 60.000 (INV) - 15.000 (Return Recovery) = 105.000
        $this->assertEquals(105000, $report['cogs']['total_cogs']);

        // Gross Profit = 180.000 - 105.000 = 75.000
        $this->assertEquals(75000, $report['gross_profit']['amount']);

        // Expenses = 25.000
        $this->assertEquals(25000, $report['expenses']['total']);

        // Net Profit = 75.000 - 25.000 = 50.000
        $this->assertEquals(50000, $report['net_profit']['amount']);
    }

    public function test_cash_flow_statement_inflow_and_outflow(): void
    {
        $today = Carbon::today();

        $account = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'BCA Rekening Utama',
            'type' => CashAccount::TYPE_BANK,
            'current_balance' => 5000000,
            'is_active' => true,
        ]);

        $posOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'POS-CF-01',
            'order_date' => $today->toDateTimeString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $posOrder->id,
            'payment_method' => 'cash',
            'amount' => 200000,
        ]);

        // Invoice Payment
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-CF-01',
            'invoice_date' => $today->toDateString(),
            'due_date' => $today->copy()->addDays(14)->toDateString(),
            'status' => Invoice::STATUS_PAID,
            'total_amount' => 300000,
            'paid_amount' => 300000,
            'balance_due' => 0,
        ]);

        InvoicePayment::create([
            'business_id' => $this->business->id,
            'invoice_id' => $invoice->id,
            'payment_number' => 'PAY-INV-001',
            'payment_method' => 'bank_transfer',
            'amount' => 300000,
            'payment_date' => $today->toDateString(),
        ]);

        $gr = \App\Models\GoodsReceipt::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
            'receipt_number' => 'GR-001',
            'receipt_date' => $today->toDateString(),
            'status' => 'confirmed',
        ]);

        // Supplier Payment Outflow
        $bill = SupplierInvoice::create([
            'business_id' => $this->business->id,
            'goods_receipt_id' => $gr->id,
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'BILL-CF-01',
            'invoice_date' => $today->toDateString(),
            'total_amount' => 150000,
            'paid_amount' => 150000,
            'balance_due' => 0,
            'status' => 'paid',
        ]);

        SupplierPayment::create([
            'business_id' => $this->business->id,
            'supplier_invoice_id' => $bill->id,
            'payment_number' => 'PAY-V-001',
            'payment_date' => $today->toDateString(),
            'amount' => 150000,
        ]);

        $service = new FinancialReportService();
        $cf = $service->getCashFlowStatement($this->business, $today, $today);

        // Inflow: 200.000 (POS) + 300.000 (INV) = 500.000
        $this->assertEquals(500000, $cf['inflows']['total']);

        // Outflow: 150.000 (Supplier Payment)
        $this->assertEquals(150000, $cf['outflows']['total']);

        // Net Cash Flow: 500.000 - 150.000 = 350.000
        $this->assertEquals(350000, $cf['net_cash_flow']);
// Total Saldo Kas & Bank Terkini berasal dari CashAccount tenant (bukan PaymentAccount platform)
        $this->assertEquals(1, count($cf['accounts']['cash_accounts']));
        $this->assertEquals(5000000.0, $cf['accounts']['total_balance']);
    }

    public function test_ar_and_ap_aging_summary_buckets(): void
    {
        $today = Carbon::today();

        // 1. Current Invoice (due in 5 days) -> 100.000
        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-CURR',
            'invoice_date' => $today->toDateString(),
            'due_date' => $today->copy()->addDays(5)->toDateString(),
            'status' => Invoice::STATUS_UNPAID,
            'total_amount' => 100000,
            'balance_due' => 100000,
        ]);

        // 2. Overdue 40 days (bucket 31-60) -> 250.000
        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-OVER40',
            'invoice_date' => $today->copy()->subDays(50)->toDateString(),
            'due_date' => $today->copy()->subDays(40)->toDateString(),
            'status' => Invoice::STATUS_OVERDUE,
            'total_amount' => 250000,
            'balance_due' => 250000,
        ]);

        $gr = \App\Models\GoodsReceipt::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
            'receipt_number' => 'GR-002',
            'receipt_date' => $today->toDateString(),
            'status' => 'confirmed',
        ]);

        // 3. Supplier Bill Overdue 15 days (bucket 1-30) -> 500.000
        SupplierInvoice::create([
            'business_id' => $this->business->id,
            'goods_receipt_id' => $gr->id,
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'BILL-OVER15',
            'invoice_date' => $today->copy()->subDays(25)->toDateString(),
            'due_date' => $today->copy()->subDays(15)->toDateString(),
            'total_amount' => 500000,
            'balance_due' => 500000,
            'status' => 'unpaid',
        ]);

        $service = new FinancialReportService();
        $aging = $service->getAgingSummary($this->business);

        $this->assertEquals(100000, $aging['ar']['buckets']['current']);
        $this->assertEquals(250000, $aging['ar']['buckets']['31_60']);
        $this->assertEquals(350000, $aging['ar']['total_balance']);

        $this->assertEquals(500000, $aging['ap']['buckets']['1_30']);
        $this->assertEquals(500000, $aging['ap']['total_balance']);
    }

    public function test_stock_valuation_and_velocity(): void
    {
        $service = new FinancialReportService();
        $stockReport = $service->getStockValuationAndTurnover($this->business);

        // Product A: 100 unit * 30.000 = 3.000.000
        // Product B: 50 unit * 15.000 = 750.000
        // Total Valuation = 3.750.000
        $this->assertEquals(3750000, $stockReport['summary']['total_valuation']);
        $this->assertEquals(150, $stockReport['summary']['total_physical_units']);
    }

    public function test_web_reports_page_and_csv_export(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Laporan &amp; Analitik Finansial', false);
        $response->assertSee('Laba Rugi (P&amp;L)', false);
        $response->assertSee('Arus Kas (Cash Flow)', false);
        $response->assertSee('Umur Piutang &amp; Hutang (AR/AP)', false);
        $response->assertSee('Valuasi &amp; Perputaran Stok', false);

        // Test Export CSV - khusus paket Core; aktifkan plan dulu.
        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($this->business, 'monthly');
        $exportResponse = $this->actingAs($this->user)->get(route('reports.export-excel', ['type' => 'income_statement']));
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
