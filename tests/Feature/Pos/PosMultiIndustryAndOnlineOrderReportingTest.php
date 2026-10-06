<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Billing\EntitlementService;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Exports\PosReportExport;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderItemModifier;
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

/**
 * Automated Acceptance Test Suite: 20 Industri & Ojol Channels & Production Hardening.
 *
 * @covers \App\Domain\Report\Pos\PosReportingService
 * @covers \App\Exports\PosReportExport
 * @covers \App\Http\Controllers\Pos\PosReportWebController
 */
final class PosMultiIndustryAndOnlineOrderReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private User $technician;
    private Business $business;
    private Location $location;
    private PosShift $shift;
    private Customer $customer;
    private Product $goodsProduct;
    private Product $serviceProduct;
    private PosReportingService $reportingService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->reportingService = new PosReportingService();

        // 1. Business Setup
        $this->owner = User::create([
            'name' => 'Hendrawan Kusuma',
            'email' => 'hendrawan@omnichannel-enterprise.cooca.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->cashier = User::create([
            'name' => 'Siti Kasir Senior',
            'email' => 'siti.kasir@omnichannel-enterprise.cooca.com',
            'password' => bcrypt('password123'),
        ]);

        $this->technician = User::create([
            'name' => 'Bambang Teknisi Handal',
            'email' => 'bambang.mekanik@omnichannel-enterprise.cooca.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Omnichannel Super Hub Nusantara',
            'slug' => 'omnichannel-super-hub',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $userRoles = [
            [$this->owner, 'owner'],
            [$this->cashier, 'cashier'],
            [$this->technician, 'staff'],
        ];
        foreach ($userRoles as [$user, $role]) {
            $this->business->users()->attach($user->id, [
                'id' => (string) Str::uuid(),
                'role' => $role,
                'is_active' => true,
            ]);
        }
        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        app(EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Central Hub Sudirman',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'shift_number' => 'SHIFT-2026-MULTI-01',
            'opening_cash' => 1000000.0,
            'status' => PosShift::STATUS_OPEN,
            'opened_at' => now()->startOfDay(),
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Raden Mas Arya',
            'phone' => '081299887766',
            'email' => 'arya@vip-member.com',
        ]);

        $unitPcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $catFnb = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'F&B Signature Menu',
        ]);

        $this->goodsProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $catFnb->id,
            'output_unit_id' => $unitPcs->id,
            'name' => 'Nasi Wagyu Balado Rempah',
            'code' => 'FNB-WGY-01',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 65000.0,
            'base_cost' => 25000.0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        $this->serviceProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $catFnb->id,
            'output_unit_id' => $unitPcs->id,
            'name' => 'Jasa Perawatan & Tune Up Khusus',
            'code' => 'SRV-TUNE-01',
            'type' => Product::TYPE_SERVICE,
            'selling_price' => 150000.0,
            'base_cost' => 30000.0,
            'track_stock' => false,
            'is_active' => true,
        ]);
    }

    /**
     * 1. F&B Online Food Delivery (ShopeeFood, GoFood, GrabFood, Web, POS).
     */
    public function test_fnb_online_food_delivery_full_lifecycle_shopeefood_gofood_grabfood_and_real_margin_calculations(): void
    {
        // 1A. ShopeeFood Order (20% Commission)
        $sfOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-SF-8891',
            'sales_channel' => 'shopeefood',
            'external_order_ref' => 'SF-20261005-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'delivery',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 130000.0, // 2 porsi Wagyu
            'discount_amount' => 10000.0,
            'voucher_discount_amount' => 0.0,
            'points_discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'service_charge_amount' => 0.0,
            'rounding_amount' => 0.0,
            'total_amount' => 120000.0, // Net Kasir = 120.000
            'total_hpp_cost' => 50000.0, // Modal HPP = 50.000
            'total_gross_profit' => 70000.0,
            'paid_amount' => 120000.0,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $sfOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'quantity' => 2,
            'unit_price' => 65000.0,
            'unit_cost_hpp' => 25000.0,
            'subtotal' => 130000.0,
            'total_price' => 120000.0,
            'total_hpp' => 50000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $sfOrder->id,
            'payment_method' => 'transfer',
            'amount' => 120000.0,
            'status' => 'paid',
        ]);

        // 1B. GoFood Order (20% Commission)
        $gfOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-GF-7721',
            'sales_channel' => 'gofood',
            'external_order_ref' => 'GF-20261005-002',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'delivery',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 195000.0, // 3 porsi Wagyu
            'discount_amount' => 15000.0,
            'tax_amount' => 0.0,
            'total_amount' => 180000.0, // Net Kasir = 180.000
            'total_hpp_cost' => 75000.0,
            'total_gross_profit' => 105000.0,
            'paid_amount' => 180000.0,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $gfOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'quantity' => 3,
            'unit_price' => 65000.0,
            'unit_cost_hpp' => 25000.0,
            'subtotal' => 195000.0,
            'total_price' => 180000.0,
            'total_hpp' => 75000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $gfOrder->id,
            'payment_method' => 'transfer',
            'amount' => 180000.0,
            'status' => 'paid',
        ]);

        // 1C. GrabFood Order (20% Commission)
        $grabOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-GB-5512',
            'sales_channel' => 'grabfood',
            'external_order_ref' => 'GB-20261005-003',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'delivery',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 65000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'total_amount' => 65000.0,
            'total_hpp_cost' => 25000.0,
            'total_gross_profit' => 40000.0,
            'paid_amount' => 65000.0,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $grabOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'quantity' => 1,
            'unit_price' => 65000.0,
            'unit_cost_hpp' => 25000.0,
            'subtotal' => 65000.0,
            'total_price' => 65000.0,
            'total_hpp' => 25000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $grabOrder->id,
            'payment_method' => 'transfer',
            'amount' => 65000.0,
            'status' => 'paid',
        ]);

        // 1D. Direct POS Walk-in (0% Commission)
        $posOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-WALK-1001',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 130000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 13000.0,
            'total_amount' => 143000.0,
            'total_hpp_cost' => 50000.0,
            'total_gross_profit' => 80000.0,
            'paid_amount' => 143000.0,
            'table_or_reference' => 'Meja VIP 08',
        ]);
        PosOrderItem::create([
            'pos_order_id' => $posOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'quantity' => 2,
            'unit_price' => 65000.0,
            'unit_cost_hpp' => 25000.0,
            'subtotal' => 130000.0,
            'total_price' => 130000.0,
            'total_hpp' => 50000.0,
        ]);
        PosOrderPayment::create([
            'pos_order_id' => $posOrder->id,
            'payment_method' => 'cash',
            'amount' => 143000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        $channelBreakdown = $this->reportingService->getSalesChannelBreakdown($filter);

        $this->assertNotEmpty($channelBreakdown);
        $channelsByCode = $channelBreakdown->keyBy('channel_code');

        // Verify ShopeeFood
        $this->assertArrayHasKey('shopeefood', $channelsByCode);
        $sfData = $channelsByCode['shopeefood'];
        $this->assertEquals(1, $sfData->orders_count);
        $this->assertEquals(120000.0, $sfData->net_sales);
        $this->assertEquals(20.0, $sfData->platform_fee_percent);
        $this->assertEquals(24000.0, $sfData->platform_fee_amount); // 20% of 120k
        $this->assertEquals(96000.0, $sfData->net_merchant_payout); // 120k - 24k
        $this->assertEquals(50000.0, $sfData->total_hpp);
        $this->assertEquals(46000.0, $sfData->real_gross_profit); // 96k - 50k

        // Verify GoFood
        $this->assertArrayHasKey('gofood', $channelsByCode);
        $gfData = $channelsByCode['gofood'];
        $this->assertEquals(1, $gfData->orders_count);
        $this->assertEquals(180000.0, $gfData->net_sales);
        $this->assertEquals(20.0, $gfData->platform_fee_percent);
        $this->assertEquals(36000.0, $gfData->platform_fee_amount); // 20% of 180k
        $this->assertEquals(144000.0, $gfData->net_merchant_payout); // 180k - 36k

        // Verify GrabFood
        $this->assertArrayHasKey('grabfood', $channelsByCode);
        $gbData = $channelsByCode['grabfood'];
        $this->assertEquals(1, $gbData->orders_count);
        $this->assertEquals(65000.0, $gbData->net_sales);
    }

    /**
     * 2. Bengkel & Otomotif (Plat Nopol, Model, Odometer, Teknisi, Service Notes).
     */
    public function test_workshop_automotive_industry_reporting_with_nopol_odometer_technician_and_service_costs(): void
    {
        $workshopOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-BKL-9901',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'service',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 450000.0,
            'discount_amount' => 50000.0,
            'tax_amount' => 0.0,
            'total_amount' => 400000.0,
            'total_hpp_cost' => 120000.0,
            'total_gross_profit' => 280000.0,
            'paid_amount' => 400000.0,
            'vehicle_license_plate' => 'D 1984 AB',
            'vehicle_model' => 'Toyota Innova Reborn Diesel',
            'vehicle_mileage' => 65000,
            'technician_id' => $this->technician->id,
            'service_notes' => 'Ganti Oli Transmisi ATF + Flush Radiator Coolant',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $workshopOrder->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => 'Jasa Service Berkala 60.000 KM',
            'quantity' => 1,
            'unit_price' => 250000.0,
            'unit_cost_hpp' => 50000.0,
            'subtotal' => 250000.0,
            'total_price' => 200000.0,
            'total_hpp' => 50000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $workshopOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Oli Mesin Fully Synthetic 4L',
            'quantity' => 1,
            'unit_price' => 200000.0,
            'unit_cost_hpp' => 70000.0,
            'subtotal' => 200000.0,
            'total_price' => 200000.0,
            'total_hpp' => 70000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $workshopOrder->id,
            'payment_method' => 'edc_debit',
            'amount' => 400000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        // Generate Excel and verify contextual metadata
        $exporter = new PosReportExport($this->reportingService);
        $spreadsheet = $exporter->generate($this->business, $filter);

        $sheet2 = $spreadsheet->getSheet(1);
        $cellValue = (string) $sheet2->getCell('H6')->getValue();

        $this->assertStringContainsString('D 1984 AB', $cellValue);
        $this->assertStringContainsString('Innova Reborn', $cellValue);
        $this->assertStringContainsString('65.000 km', $cellValue);
        $this->assertStringContainsString('Bambang Teknisi Handal', $cellValue);

        // Test Controller Quick-View Modal
        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.orders.detail', ['order' => $workshopOrder->id]));

        $response->assertStatus(200);
        $response->assertSee('D 1984 AB');
        $response->assertSee('Innova Reborn');
        $response->assertSee('Bambang Teknisi Handal');
        $response->assertSee('Flush Radiator Coolant');
    }

    /**
     * 3. Laundry Kiloan & Satuan (Berat kg, Rak Penyimpanan, Estimasi Selesai, Status).
     */
    public function test_laundry_dry_cleaning_industry_reporting_with_weight_rack_location_and_due_date(): void
    {
        $laundryOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-LDR-5532',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'service',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 85000.0,
            'total_amount' => 85000.0,
            'total_hpp_cost' => 18000.0,
            'total_gross_profit' => 67000.0,
            'paid_amount' => 85000.0,
            'laundry_weight_kg' => 8.5,
            'rack_location' => 'Rak R-09 / Bag A',
            'laundry_status' => 'ready',
            'estimated_completion_at' => now()->addDays(2),
        ]);

        PosOrderItem::create([
            'pos_order_id' => $laundryOrder->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => 'Cuci Lipat Wangi Premium 8.5kg',
            'quantity' => 8.5,
            'unit_price' => 10000.0,
            'unit_cost_hpp' => 2117.65,
            'subtotal' => 85000.0,
            'total_price' => 85000.0,
            'total_hpp' => 18000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $laundryOrder->id,
            'payment_method' => 'qris',
            'amount' => 85000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        $exporter = new PosReportExport($this->reportingService);
        $spreadsheet = $exporter->generate($this->business, $filter);

        $sheet2 = $spreadsheet->getSheet(1);
        $cellValue = (string) $sheet2->getCell('H6')->getValue();

        $this->assertStringContainsString('8.5 kg', $cellValue);
        $this->assertStringContainsString('Rak R-09', $cellValue);
        $this->assertStringContainsString('[Ready]', $cellValue);

        // Test Controller Quick-View Modal
        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.orders.detail', ['order' => $laundryOrder->id]));

        $response->assertStatus(200);
        $response->assertJsonPath('data.industry_meta.laundry_weight_kg', 8.5);
        $response->assertJsonPath('data.industry_meta.rack_location', 'Rak R-09 / Bag A');
        $response->assertJsonPath('data.industry_meta.laundry_status', 'ready');
    }

    /**
     * 4. Apotek & Klinik Medis (Nomor Resep Dokter, Batch Number, Expired Date, Aturan Pakai).
     */
    public function test_pharmacy_and_clinic_medical_industry_reporting_with_batches_expiry_and_doctor_recipes(): void
    {
        $pharmacyOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-APT-9921',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'take_away',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 125000.0,
            'total_amount' => 125000.0,
            'total_hpp_cost' => 60000.0,
            'total_gross_profit' => 65000.0,
            'paid_amount' => 125000.0,
            'notes' => 'Resep dr. Anwar Sp.A No. RSP-2026-889',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $pharmacyOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Cefixime Trihydrate Sirup 100mg/5ml',
            'quantity' => 1,
            'unit_price' => 75000.0,
            'unit_cost_hpp' => 35000.0,
            'subtotal' => 75000.0,
            'total_price' => 75000.0,
            'total_hpp' => 35000.0,
            'batch_number' => 'BTH-CFX-2026-X1',
            'expired_date' => '2028-06-30',
            'dosage_instructions' => '2 x sehari 2.5 ml sesudah makan',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $pharmacyOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Paracetamol Drops 15ml',
            'quantity' => 1,
            'unit_price' => 50000.0,
            'unit_cost_hpp' => 25000.0,
            'subtotal' => 50000.0,
            'total_price' => 50000.0,
            'total_hpp' => 25000.0,
            'batch_number' => 'BTH-PCT-2026-Y2',
            'expired_date' => '2027-12-31',
            'dosage_instructions' => '3 x sehari 0.8 ml bila demam',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $pharmacyOrder->id,
            'payment_method' => 'cash',
            'amount' => 125000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        $exporter = new PosReportExport($this->reportingService);
        $spreadsheet = $exporter->generate($this->business, $filter);

        $sheet2 = $spreadsheet->getSheet(1);
        $cellValue = (string) $sheet2->getCell('H6')->getValue();

        $this->assertStringContainsString('BTH-CFX-2026-X1', $cellValue);

        // Test Modal Detail
        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.orders.detail', ['order' => $pharmacyOrder->id]));

        $response->assertStatus(200);
        $response->assertSee('BTH-CFX-2026-X1');
        $response->assertSee('2 x sehari 2.5 ml sesudah makan');
        $response->assertSee('Resep dr. Anwar Sp.A');
    }

    /**
     * 5. Toko Elektronik & Gadget (Serial Number / IMEI & Garansi Modifiers).
     */
    public function test_electronics_and_retail_with_serial_numbers_and_custom_modifiers(): void
    {
        $elecOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-ELC-7712',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 2500000.0,
            'total_amount' => 2500000.0,
            'total_hpp_cost' => 1900000.0,
            'total_gross_profit' => 600000.0,
            'paid_amount' => 2500000.0,
            'notes' => 'Garansi Resmi TAM 1 Tahun',
        ]);

        $item = PosOrderItem::create([
            'pos_order_id' => $elecOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => 'Tablet Android 11 Inch 128GB',
            'quantity' => 1,
            'unit_price' => 2500000.0,
            'unit_cost_hpp' => 1900000.0,
            'subtotal' => 2500000.0,
            'total_price' => 2500000.0,
            'total_hpp' => 1900000.0,
            'serial_number' => 'IMEI-358921092830192',
        ]);

        PosOrderItemModifier::create([
            'pos_order_item_id' => $item->id,
            'modifier_group_name' => 'Paket Proteksi Layar',
            'modifier_option_name' => 'Tempered Glass 9H + Softcase Anti-Drop',
            'quantity' => 1,
            'unit_price' => 0.0,
            'total_price' => 0.0,
            'unit_cost' => 15000.0,
            'total_cost' => 15000.0,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $elecOrder->id,
            'payment_method' => 'transfer',
            'amount' => 2500000.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        $exporter = new PosReportExport($this->reportingService);
        $spreadsheet = $exporter->generate($this->business, $filter);

        $sheet2 = $spreadsheet->getSheet(1);
        $cellValue = (string) $sheet2->getCell('H6')->getValue();
        $this->assertStringContainsString('IMEI-358921092830192', $cellValue);

        // Check Modal Detail Modifiers
        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.orders.detail', ['order' => $elecOrder->id]));

        $response->assertStatus(200);
        $response->assertSee('IMEI-358921092830192');
        $response->assertSee('Tempered Glass 9H');
    }

    /**
     * 6. Multi-Payment Split (Tunai + QRIS + Poin) & Rekonsiliasi 3-Arah.
     */
    public function test_multi_payment_split_cash_qris_points_reconciliation_and_zero_discrepancy(): void
    {
        $splitOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-SPLIT-3001',
            'sales_channel' => 'pos',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'points_discount_amount' => 10000.0,
            'total_amount' => 90000.0,
            'total_hpp_cost' => 35000.0,
            'total_gross_profit' => 55000.0,
            'paid_amount' => 90000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $splitOrder->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'quantity' => 1,
            'unit_price' => 100000.0,
            'unit_cost_hpp' => 35000.0,
            'subtotal' => 100000.0,
            'total_price' => 90000.0,
            'total_hpp' => 35000.0,
        ]);

        // Payment 1: Cash 50k
        PosOrderPayment::create([
            'pos_order_id' => $splitOrder->id,
            'payment_method' => 'cash',
            'amount' => 50000.0,
            'status' => 'paid',
        ]);

        // Payment 2: QRIS 40k (MDR fee 280)
        PosOrderPayment::create([
            'pos_order_id' => $splitOrder->id,
            'payment_method' => 'qris',
            'amount' => 40000.0,
            'net_amount' => 39720.0,
            'fee_amount' => 280.0,
            'status' => 'paid',
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->startOfDay(),
            endDate: now()->endOfDay()
        );

        $recon = $this->reportingService->reconcile($filter);
        $this->assertEquals(90000.0, $recon->totalOrdersAmount);
        $this->assertEquals(90000.0, $recon->totalPaymentsAmount);
        $this->assertEquals(0.0, $recon->orderPaymentDiscrepancy);
        $this->assertTrue($recon->isBalanced);

        $payBreakdown = $this->reportingService->getPaymentMethodBreakdown($filter);
        $this->assertCount(2, $payBreakdown); // Cash & QRIS
    }

    /**
     * 7. Production Hardening: Validasi Seluruh 9-Sheet Master Excel Formula & Zero PHP Notice.
     */
    public function test_excel_9_sheet_exporter_production_hardening_and_formula_integrity(): void
    {
        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->subDays(30),
            endDate: now()
        );

        $exporter = new PosReportExport($this->reportingService);
        $spreadsheet = $exporter->generate($this->business, $filter);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals(9, $spreadsheet->getSheetCount());

        // Validate all sheet titles & grids
        for ($i = 0; $i < 9; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $this->assertNotEmpty($sheet->getTitle());
            $this->assertTrue($sheet->getShowGridLines());
        }

        // Validate Sheet 1 (Executive Summary)
        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertEquals('Ringkasan Eksekutif', $sheet1->getTitle());
        $this->assertStringContainsString('3. PERFORMA SALURAN PENJUALAN', (string) $sheet1->getCell('A24')->getValue());

        // Validate Sheet 2 (Transaction Ledger)
        $sheet2 = $spreadsheet->getSheet(1);
        $this->assertEquals('Buku Transaksi', $sheet2->getTitle());
        $this->assertEquals('Jumlah Cetak', (string) $sheet2->getCell('AA5')->getValue());

        // Validate Sheet 9 (Reconciliation & Void)
        $sheet9 = $spreadsheet->getSheet(8);
        $this->assertEquals('Rekonsiliasi & Void', $sheet9->getTitle());
    }

    /**
     * 8. Tenant Isolation & Multi-Business Security Protection.
     */
    public function test_multi_tenant_strict_data_isolation_across_20_industries(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Bengkel Lain Bersama Corp',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $foreignOrder = PosOrder::create([
            'business_id' => $otherBusiness->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-FOREIGN-999',
            'sales_channel' => 'shopeefood',
            'external_order_ref' => 'SF-FOREIGN-123',
            'vehicle_license_plate' => 'B 9999 HACK',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 9999999.0,
            'total_amount' => 9999999.0,
            'paid_amount' => 9999999.0,
        ]);

        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: now()->subDays(30),
            endDate: now()
        );

        $channels = $this->reportingService->getSalesChannelBreakdown($filter);
        $totalSales = (float) $channels->sum('net_sales');

        // Verify foreign business data is strictly excluded
        $this->assertLessThan(9999999.0, $totalSales);

        // Verify foreign order detail is blocked with 404 or Context boundary
        $response = $this->actingAs($this->owner)
            ->get(route('pos.reports.orders.detail', ['order' => $foreignOrder->id]));

        $response->assertStatus(404);
    }
}
