<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Billing\EntitlementService;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
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
        $this->assertStringContainsString('attachment; filename="Master_Laporan_POS_', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_pos_report_export_class_generates_valid_nine_sheet_spreadsheet_with_formulas(): void
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

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: $startDate,
            endDate: $endDate
        );

        $exporter = new PosReportExport();
        $spreadsheet = $exporter->generate($this->business, $filter);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals(9, $spreadsheet->getSheetCount(), 'Excel must contain exactly 9 sheets');

        // Check sheet names
        $expectedTitles = [
            'Ringkasan Eksekutif',
            'Buku Transaksi',
            'Kinerja Produk',
            'Kontribusi Kategori',
            'Produktivitas Kasir',
            'Perbandingan Outlet',
            'Metode Pembayaran',
            'Diskon & Promosi',
            'Rekonsiliasi & Void',
        ];

        foreach ($expectedTitles as $idx => $title) {
            $this->assertEquals($title, $spreadsheet->getSheet($idx)->getTitle());
        }

        // 1. Validate Sheet 1: Ringkasan Eksekutif
        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertStringContainsString('KOPI NUSANTARA', (string) $sheet1->getCell('A2')->getValue());
        $this->assertEquals('TOTAL OMZET PENJUALAN', (string) $sheet1->getCell('A6')->getValue());
        $this->assertEquals(86000.0, (float) $sheet1->getCell('A7')->getValue()); // 56k + 30k Net Sales (excl. 3k tax)
        $this->assertEquals(2, (int) $sheet1->getCell('C7')->getValue()); // 2 Orders

        // Validate Sheet 1 Table 3: Sales Channels & Ojol
        $this->assertStringContainsString('3. PERFORMA SALURAN PENJUALAN', (string) $sheet1->getCell('A24')->getValue());
        $this->assertEquals('Saluran Penjualan / Platform', (string) $sheet1->getCell('A25')->getValue());
        $this->assertNotEmpty((string) $sheet1->getCell('A26')->getValue());
        $this->assertEquals(2, (int) $sheet1->getCell('B26')->getValue());

        // 2. Validate Sheet 2: Buku Transaksi
        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertEquals('No. Order POS', (string) $sheet2->getCell('B5')->getValue());
        $this->assertEquals('Saluran Jual', (string) $sheet2->getCell('F5')->getValue());
        $this->assertEquals('No. Ref / Ojol / Meja', (string) $sheet2->getCell('G5')->getValue());
        $this->assertEquals('Info Kontekstual 20 Industri', (string) $sheet2->getCell('H5')->getValue());
        $this->assertEquals('Komisi Platform Ojol (Rp)', (string) $sheet2->getCell('T5')->getValue());
        $this->assertEquals('Net Payout Hak Resto (Rp)', (string) $sheet2->getCell('U5')->getValue());
        $this->assertEquals('Laba Bersih Riil (Rp)', (string) $sheet2->getCell('W5')->getValue());

        $this->assertEquals('POS-2026-XLS-001', (string) $sheet2->getCell('B6')->getValue());
        $this->assertEquals('Kasir Toko (POS)', (string) $sheet2->getCell('F6')->getValue());
        $this->assertEquals('Meja 02', (string) $sheet2->getCell('G6')->getValue());
        $this->assertEquals('POS-2026-XLS-002', (string) $sheet2->getCell('B7')->getValue());
        $this->assertEquals('A6', $sheet2->getFreezePane());

        // 3. Validate Sheet 3: Kinerja Produk
        $sheet3 = $spreadsheet->getSheet(2);
        $this->assertEquals('Kode Produk', (string) $sheet3->getCell('B5')->getValue());
        $this->assertEquals('Nama Produk / Menu', (string) $sheet3->getCell('C5')->getValue());
        $this->assertGreaterThan(0, (float) $sheet3->getCell('F6')->getValue());

        // 4. Validate Sheet 7: Metode Pembayaran
        $sheet7 = $spreadsheet->getSheet(6);
        $this->assertEquals('Metode Pembayaran', (string) $sheet7->getCell('B5')->getValue());

        // 5. Validate Sheet 9: Rekonsiliasi & Void
        $sheet9 = $spreadsheet->getSheet(8);
        $this->assertStringContainsString('BAGIAN 1: REKONSILIASI', (string) $sheet9->getCell('A5')->getValue());
    }

    public function test_pos_report_export_with_ojol_channels_and_industry_metadata(): void
    {
        // Technician User for Workshop Industry
        $mechanic = User::create([
            'name' => 'Budi Mekanik Master',
            'email' => 'budi.mekanik@caferesto.cooca.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($mechanic->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        // Order A: ShopeeFood Ojol F&B Order
        $sfOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-SF-2026-001',
            'sales_channel' => 'shopeefood',
            'external_order_ref' => 'SF-99281',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'delivery',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'discount_amount' => 10000.0,
            'tax_amount' => 0.0,
            'total_amount' => 90000.0,
            'total_hpp_cost' => 35000.0,
            'total_gross_profit' => 55000.0,
            'paid_amount' => 90000.0,
            'notes' => 'Driver minta sendok garpu & plastik dobel',
        ]);
        PosOrderItem::create([
            'pos_order_id' => $sfOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Kopi Gula Aren Special (ShopeeFood)',
            'quantity' => 3,
            'unit_price' => 30000.0,
            'unit_cost_hpp' => 11000.0,
            'subtotal' => 90000.0,
            'total_price' => 90000.0,
            'total_hpp' => 33000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $sfOrder->id,
            'payment_method' => 'transfer',
            'amount' => 90000.0,
            'status' => 'paid',
        ]);

        // Order B: Workshop / Bengkel Industry Order
        $workshopOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-BKL-2026-001',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'service',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 250000.0,
            'total_amount' => 250000.0,
            'total_hpp_cost' => 80000.0,
            'total_gross_profit' => 170000.0,
            'paid_amount' => 250000.0,
            'vehicle_license_plate' => 'B 1234 XYZ',
            'vehicle_model' => 'Honda Jazz RS 2021',
            'vehicle_mileage' => 45000,
            'technician_id' => $mechanic->id,
            'service_notes' => 'Ganti Oli Mesin & Filter Udara',
        ]);
        PosOrderItem::create([
            'pos_order_id' => $workshopOrder->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => 'Paket Tune Up & Service Berkala',
            'quantity' => 1,
            'unit_price' => 250000.0,
            'unit_cost_hpp' => 80000.0,
            'subtotal' => 250000.0,
            'total_price' => 250000.0,
            'total_hpp' => 80000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $workshopOrder->id,
            'payment_method' => 'edc_debit',
            'amount' => 250000.0,
            'status' => 'paid',
        ]);

        // Order C: Laundry Industry Order
        $laundryOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-LDR-2026-001',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'service',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 60000.0,
            'total_amount' => 60000.0,
            'total_hpp_cost' => 12000.0,
            'total_gross_profit' => 48000.0,
            'paid_amount' => 60000.0,
            'laundry_weight_kg' => 6.5,
            'rack_location' => 'Rak R-12',
            'laundry_status' => 'ready',
        ]);
        PosOrderItem::create([
            'pos_order_id' => $laundryOrder->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => 'Cuci Komplit Express 1 Hari',
            'quantity' => 6.5,
            'unit_price' => 9230.77,
            'unit_cost_hpp' => 1846.15,
            'subtotal' => 60000.0,
            'total_price' => 60000.0,
            'total_hpp' => 12000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $laundryOrder->id,
            'payment_method' => 'cash',
            'amount' => 60000.0,
            'status' => 'paid',
        ]);

        // Order D: Pharmacy / Apotek Industry Order
        $pharmacyOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-APT-2026-001',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'take_away',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 85000.0,
            'total_amount' => 85000.0,
            'total_hpp_cost' => 40000.0,
            'total_gross_profit' => 45000.0,
            'paid_amount' => 85000.0,
            'notes' => 'Resep dr. Anwar Sp.PD No. R-8821',
        ]);
        PosOrderItem::create([
            'pos_order_id' => $pharmacyOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Amoxicillin Trihydrate 500mg',
            'quantity' => 2,
            'unit_price' => 42500.0,
            'unit_cost_hpp' => 20000.0,
            'subtotal' => 85000.0,
            'total_price' => 85000.0,
            'total_hpp' => 40000.0,
            'batch_number' => 'BTH-2026-AMX',
            'serial_number' => 'SN-998822',
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $pharmacyOrder->id,
            'payment_method' => 'qris',
            'amount' => 85000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->subDays(2),
            endDate: now()->addDay()
        );

        $exporter = new PosReportExport();
        $spreadsheet = $exporter->generate($this->business, $filter);

        // Verify Sheet 1: Sales Channels
        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertStringContainsString('3. PERFORMA SALURAN PENJUALAN', (string) $sheet1->getCell('A24')->getValue());

        // Verify Sheet 2: Ledger Data Rows
        $sheet2 = $spreadsheet->getSheet(1);

        // Find rows by Order Number
        $rows = [];
        for ($r = 6; $r <= 20; $r++) {
            $ordNum = (string) $sheet2->getCell("B{$r}")->getValue();
            if ($ordNum !== '') {
                $rows[$ordNum] = $r;
            }
        }

        // 1. Verify ShopeeFood Row
        $this->assertArrayHasKey('POS-SF-2026-001', $rows);
        $sfRow = $rows['POS-SF-2026-001'];
        $this->assertEquals('ShopeeFood', (string) $sheet2->getCell("F{$sfRow}")->getValue());
        $this->assertEquals('SF-99281', (string) $sheet2->getCell("G{$sfRow}")->getValue());
        $this->assertEquals(90000.0, (float) $sheet2->getCell("S{$sfRow}")->getValue()); // Omzet Kasir
        $this->assertEquals(18000.0, (float) $sheet2->getCell("T{$sfRow}")->getValue()); // 20% MDR = 18.000
        $this->assertEquals("=S{$sfRow}-T{$sfRow}", (string) $sheet2->getCell("U{$sfRow}")->getValue()); // Net Payout Formula

        // 2. Verify Workshop Row
        $this->assertArrayHasKey('POS-BKL-2026-001', $rows);
        $bklRow = $rows['POS-BKL-2026-001'];
        $bklInfo = (string) $sheet2->getCell("H{$bklRow}")->getValue();
        $this->assertStringContainsString('B 1234 XYZ', $bklInfo);
        $this->assertStringContainsString('Honda Jazz', $bklInfo);
        $this->assertStringContainsString('45.000 km', $bklInfo);
        $this->assertStringContainsString('Budi Mekanik Master', $bklInfo);

        // 3. Verify Laundry Row
        $this->assertArrayHasKey('POS-LDR-2026-001', $rows);
        $ldrRow = $rows['POS-LDR-2026-001'];
        $ldrInfo = (string) $sheet2->getCell("H{$ldrRow}")->getValue();
        $this->assertStringContainsString('6.5 kg', $ldrInfo);
        $this->assertStringContainsString('Rak R-12', $ldrInfo);
        $this->assertStringContainsString('[Ready]', $ldrInfo);

        // 4. Verify Pharmacy Row
        $this->assertArrayHasKey('POS-APT-2026-001', $rows);
        $aptRow = $rows['POS-APT-2026-001'];
        $aptInfo = (string) $sheet2->getCell("H{$aptRow}")->getValue();
        $this->assertStringContainsString('BTH-2026-AMX', $aptInfo);
        $this->assertStringContainsString('SN-998822', $aptInfo);
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

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->subDays(7),
            endDate: now()
        );

        $exporter = new PosReportExport();
        $spreadsheet = $exporter->generate($this->business, $filter);

        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertNotEquals('POS-OTHER-001', (string) $sheet2->getCell('B6')->getValue());
    }
}
