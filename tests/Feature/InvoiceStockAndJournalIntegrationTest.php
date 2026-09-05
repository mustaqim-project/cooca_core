<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\InvoiceService;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 2 — Integration Test
 *
 * Verifikasi alur end-to-end:
 *   Invoice Draft  →  Konfirmasi Rilis  →  Stok Berkurang  →  Jurnal Piutang & HPP Terbentuk
 *   →  Void  →  Stok Dikembalikan.
 */
class InvoiceStockAndJournalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Location $location;

    private Unit $unit;

    private Product $product;

    private Customer $customer;

    private InvoiceService $invoiceService;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name'                 => 'Cooca UMKM Test',
            'slug'                 => 'cooca-test',
            'currency'             => 'IDR',
            'currency_code'        => 'IDR',
            'currency_symbol'      => 'Rp',
            'allow_negative_stock' => false,
        ]);

        \App\Support\Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Utama',
            'slug'        => 'gudang-utama',
            'type'        => 'warehouse',
            'is_primary'  => true,
            'is_active'   => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code'        => 'pcs',
            'name'        => 'Pcs',
            'category'    => 'quantity',
        ]);

        $this->product = Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Kopi Robusta 250g',
            'code'           => 'KOP-001',
            'output_unit_id' => $this->unit->id,
            'base_cost'      => 15000,
            'selling_price'  => 30000,
            'is_active'      => true,
        ]);

        $this->customer = Customer::create([
            'business_id'        => $this->business->id,
            'name'               => 'Toko Sejahtera',
            'payment_terms_days' => 30,
            'is_active'          => true,
        ]);

        $this->invoiceService = new InvoiceService;
        $this->stockService   = new StockService;
    }

    /**
     * Seed initial stock via StockService::recordMovement.
     */
    private function seedStock(float $qty): void
    {
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $this->product->id,
            movementType: StockMovement::TYPE_ADJUSTMENT,
            quantityChange: $qty,
            referenceNumber: 'INITIAL-SEED',
        );
    }

    /**
     * Helper: buat invoice draft dengan 1 item produk.
     */
    private function makeDraftInvoice(float $qty): Invoice
    {
        return $this->invoiceService->createFromProducts(
            business: $this->business,
            customer: $this->customer,
            itemsData: [[
                'product_id' => $this->product->id,
                'item_name'  => $this->product->name,
                'sku'        => $this->product->code,
                'quantity'   => $qty,
                'unit_id'    => $this->unit->id,
                'unit_price' => 30000,
                'unit_hpp'   => 15000,
            ]],
            attributes: [
                'status'      => Invoice::STATUS_DRAFT,
                'location_id' => $this->location->id,
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Test 1: Invoice dibuat sebagai DRAFT — stok TIDAK dipotong
    // -------------------------------------------------------------------------

    public function test_draft_invoice_does_not_deduct_stock(): void
    {
        $this->seedStock(100);

        $this->makeDraftInvoice(qty: 10);

        $stock = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->first();

        // Stok tidak berubah karena masih draft
        $this->assertEquals(100, (float) ($stock?->quantity ?? 100));
    }

    // -------------------------------------------------------------------------
    // Test 2: Konfirmasi & Rilis Invoice → Stok HARUS terpotong
    // -------------------------------------------------------------------------

    public function test_confirming_invoice_deducts_stock_from_location(): void
    {
        $this->seedStock(50);

        $invoice = $this->makeDraftInvoice(qty: 5);
        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice->status);

        // Konfirmasi & rilis
        $released = $this->invoiceService->confirmAndRelease($invoice, $this->location->id);
        $this->assertEquals(Invoice::STATUS_UNPAID, $released->status);

        // Verifikasi stok berkurang dari 50 → 45
        $qty = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->value('quantity');

        $this->assertEquals(45, (float) $qty, 'Stock should be reduced by 5 after invoice release');

        // Verifikasi ada stock movement bertipe invoice_sale
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('movement_type', StockMovement::TYPE_INVOICE_SALE)
            ->first();

        $this->assertNotNull($movement, 'StockMovement of type invoice_sale should be recorded');
        $this->assertEquals(-5, (float) $movement->quantity_change);
    }

    // -------------------------------------------------------------------------
    // Test 3: Konfirmasi → Jurnal Piutang & HPP HARUS terbentuk
    // -------------------------------------------------------------------------

    public function test_confirmed_invoice_generates_receivable_and_cogs_journal(): void
    {
        $this->seedStock(20);

        $invoice = $this->makeDraftInvoice(qty: 2);
        $this->invoiceService->confirmAndRelease($invoice, $this->location->id);

        // Cek header jurnal terbentuk dengan reference_type = invoice
        $journalHeaders = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_INVOICE)
            ->get();

        $this->assertNotEmpty($journalHeaders, 'Journal entry headers should be created when invoice is confirmed');

        // Cek baris jurnal (debit & kredit) melalui JournalEntryLine
        $entryIds   = $journalHeaders->pluck('id');
        $debitLines = \App\Models\JournalEntryLine::whereIn('journal_entry_id', $entryIds)
            ->where('type', \App\Models\JournalEntryLine::TYPE_DEBIT)
            ->count();
        $creditLines = \App\Models\JournalEntryLine::whereIn('journal_entry_id', $entryIds)
            ->where('type', \App\Models\JournalEntryLine::TYPE_CREDIT)
            ->count();

        $this->assertGreaterThan(0, $debitLines, 'Should have at least one debit line (Piutang Pelanggan)');
        $this->assertGreaterThan(0, $creditLines, 'Should have at least one credit line (Pendapatan Penjualan)');
    }

    // -------------------------------------------------------------------------
    // Test 4: Void Invoice → Stok HARUS dikembalikan
    // -------------------------------------------------------------------------

    public function test_void_invoice_restores_stock_to_location(): void
    {
        $this->seedStock(30);

        $invoice  = $this->makeDraftInvoice(qty: 8);
        $released = $this->invoiceService->confirmAndRelease($invoice, $this->location->id);

        $qtyAfterRelease = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->value('quantity');
        $this->assertEquals(22, (float) $qtyAfterRelease);

        // Void → stok kembali ke 30
        $voided = $this->invoiceService->voidInvoice($released, 'Test pembatalan');
        $this->assertEquals(Invoice::STATUS_VOID, $voided->status);

        $qtyAfterVoid = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->value('quantity');
        $this->assertEquals(30, (float) $qtyAfterVoid, 'Stock should be fully restored after void');
    }

    // -------------------------------------------------------------------------
    // Test 5: Invoice create selalu non-posting; release menjadi mutation boundary
    // -------------------------------------------------------------------------

    public function test_invoice_created_with_sent_status_remains_draft_until_release(): void
    {
        $this->seedStock(40);

        $invoice = $this->invoiceService->createFromProducts(
            business: $this->business,
            customer: $this->customer,
            itemsData: [[
                'product_id' => $this->product->id,
                'item_name'  => $this->product->name,
                'quantity'   => 10,
                'unit_id'    => $this->unit->id,
                'unit_price' => 30000,
                'unit_hpp'   => 15000,
            ]],
            attributes: [
                'status'      => Invoice::STATUS_SENT, // Langsung aktif, bukan draft
                'location_id' => $this->location->id,
            ]
        );

        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice->status);
        $qty = InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->value('quantity');

        $this->assertEquals(40, (float) $qty, 'Invoice creation must not deduct stock');

        $this->invoiceService->confirmAndRelease($invoice, $this->location->id);

        $this->assertEquals(30, (float) InventoryStock::where('product_id', $this->product->id)
            ->where('location_id', $this->location->id)
            ->value('quantity'));
    }

    // -------------------------------------------------------------------------
    // Test 6: Invoice melebihi stok → InsufficientStockException
    // -------------------------------------------------------------------------

    public function test_invoice_release_throws_when_stock_insufficient(): void
    {
        $this->seedStock(3); // Hanya 3 pcs

        $invoice = $this->makeDraftInvoice(qty: 10); // Minta 10

        $this->expectException(InsufficientStockException::class);

        $this->invoiceService->confirmAndRelease($invoice, $this->location->id);
    }
}
