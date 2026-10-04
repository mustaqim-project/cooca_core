<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Billing\EntitlementService;
use App\Exports\PosReportExport;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

final class PosReportExcelExportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private PosShift $shift;
    private Product $goodsProduct;
    private Product $serviceProduct;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Cafe & Resto',
            'email' => 'owner@caferesto.cooca.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Kopi Nusantara & Bistro',
            'slug' => 'kopi-nusantara-bistro',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        // Entitlement Core to allow report export
        app(EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Senopati',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'shift_number' => 'SHIFT-EXP-01',
            'opening_cash' => 500000,
            'status' => PosShift::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $portion = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'is_standard' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Signature Coffee',
        ]);

        $this->goodsProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $portion->id,
            'name' => 'Kopi Gula Aren Special',
            'code' => 'KOP-001',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 28000.0,
            'base_cost' => 11000.0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        $this->serviceProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $portion->id,
            'name' => 'Jasa Cuci Cangkir Keramik',
            'code' => 'SRV-001',
            'type' => Product::TYPE_SERVICE,
            'selling_price' => 15000.0,
            'base_cost' => 2000.0,
            'track_stock' => false,
            'is_active' => true,
        ]);
    }

    public function test_pos_report_excel_export_returns_streamed_xlsx_file(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-EXP-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 56000.0,
            'discount_amount' => 6000.0,
            'tax_amount' => 5500.0,
            'service_charge_amount' => 2500.0,
            'rounding_amount' => 0.0,
            'total_amount' => 58000.0,
            'total_hpp_cost' => 22000.0,
            'total_gross_profit' => 28000.0,
            'paid_amount' => 58000.0,
            'change_amount' => 0.0,
            'table_or_reference' => 'Meja 01',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'product_code' => $this->goodsProduct->code,
            'quantity' => 2,
            'unit_price' => 28000.0,
            'unit_cost_hpp' => 11000.0,
            'subtotal' => 56000.0,
            'total_price' => 50000.0,
            'total_hpp' => 22000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 58000.0,
            'net_amount' => 57500.0,
            'fee_amount' => 500.0,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.export-excel', [
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="Laporan_POS_', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_pos_report_export_class_generates_valid_two_sheet_spreadsheet_with_formulas(): void
    {
        $startDate = now()->subDays(5)->startOfDay();
        $endDate = now()->endOfDay();

        // Order 1: Goods (Paid Cash)
        $order1 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-XLS-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 56000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'service_charge_amount' => 0.0,
            'total_amount' => 56000.0,
            'total_hpp_cost' => 22000.0,
            'total_gross_profit' => 34000.0,
            'paid_amount' => 56000.0,
            'table_or_reference' => 'Meja 02',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'product_code' => $this->goodsProduct->code,
            'quantity' => 2,
            'unit_price' => 28000.0,
            'unit_cost_hpp' => 11000.0,
            'subtotal' => 56000.0,
            'total_price' => 56000.0,
            'total_hpp' => 22000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => 'cash',
            'amount' => 56000.0,
            'net_amount' => 56000.0,
            'status' => 'paid',
        ]);

        // Order 2: Service & Custom (Paid QRIS)
        $order2 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-XLS-002',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'take_away',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 30000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 3000.0,
            'total_amount' => 33000.0,
            'total_hpp_cost' => 4000.0,
            'total_gross_profit' => 26000.0,
            'paid_amount' => 33000.0,
            'table_or_reference' => 'Takeaway-01',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order2->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => $this->serviceProduct->name,
            'product_code' => $this->serviceProduct->code,
            'quantity' => 2,
            'unit_price' => 15000.0,
            'unit_cost_hpp' => 2000.0,
            'subtotal' => 30000.0,
            'total_price' => 30000.0,
            'total_hpp' => 4000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order2->id,
            'payment_method' => 'qris',
            'amount' => 33000.0,
            'net_amount' => 32600.0,
            'fee_amount' => 400.0,
            'status' => 'paid',
        ]);

        $orders = PosOrder::where('business_id', $this->business->id)
            ->with(['customer', 'user', 'location', 'payments', 'items.product'])
            ->get();

        $exporter = new PosReportExport();
        $spreadsheet = $exporter->generate($this->business, $orders, $startDate, $endDate);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals(2, $spreadsheet->getSheetCount(), 'Excel must contain exactly 2 sheets');

        // 1. Validate Sheet 1: Ringkasan Eksekutif
        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertEquals('Ringkasan Eksekutif', $sheet1->getTitle());
        $this->assertStringContainsString('KOPI NUSANTARA', (string) $sheet1->getCell('A2')->getValue());
        $this->assertEquals('TOTAL OMZET PENJUALAN', (string) $sheet1->getCell('A6')->getValue());
        $this->assertEquals(89000.0, (float) $sheet1->getCell('A7')->getValue()); // 56k + 33k
        $this->assertEquals(2, (int) $sheet1->getCell('C7')->getValue()); // 2 Orders

        // Formula assertions in Sheet 1
        $this->assertEquals('=SUM(B13:B15)', (string) $sheet1->getCell('B16')->getValue());
        $this->assertEquals('=SUM(C13:C15)', (string) $sheet1->getCell('C16')->getValue());

        // 2. Validate Sheet 2: Rincian Transaksi
        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertEquals('Rincian Transaksi', $sheet2->getTitle());
        $this->assertEquals('RINCIAN TRANSAKSI PENJUALAN KASIR (TRANSACTION LEDGER)', (string) $sheet2->getCell('A2')->getValue());
        $this->assertEquals('No. Order POS', (string) $sheet2->getCell('B5')->getValue());
        $this->assertEquals('POS-2026-XLS-001', (string) $sheet2->getCell('B6')->getValue());
        $this->assertEquals('POS-2026-XLS-002', (string) $sheet2->getCell('B7')->getValue());

        // Freeze panes check
        $this->assertEquals('A6', $sheet2->getFreezePane());

        // Formula checks in Sheet 2 Total row (Row 8)
        $this->assertEquals('=SUM(J6:J7)', (string) $sheet2->getCell('J8')->getValue());
        $this->assertEquals('=SUM(O6:O7)', (string) $sheet2->getCell('O8')->getValue());
        $this->assertEquals('=SUM(P6:P7)', (string) $sheet2->getCell('P8')->getValue());
        $this->assertEquals('=SUM(Q6:Q7)', (string) $sheet2->getCell('Q8')->getValue());
    }

    public function test_pos_report_export_respects_tenant_isolation(): void
    {
        // Another business with another order
        $otherBusiness = Business::create([
            'name' => 'Other Tenant Resto',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        PosOrder::create([
            'business_id' => $otherBusiness->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-OTHER-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 999000.0,
            'total_amount' => 999000.0,
            'paid_amount' => 999000.0,
        ]);

        $orders = PosOrder::where('business_id', $this->business->id)
            ->with(['customer', 'user', 'location', 'payments', 'items.product'])
            ->get();

        $exporter = new PosReportExport();
        $spreadsheet = $exporter->generate($this->business, $orders, now()->subDays(7), now());

        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertNotEquals('POS-OTHER-001', (string) $sheet2->getCell('B6')->getValue());
    }
}
