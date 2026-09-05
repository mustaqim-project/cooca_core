<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosAndBusinessReportingAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Customer $customer;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Audit',
            'email' => 'audit.owner@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Audit Store',
            'slug' => 'cooca-audit-store',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        // Entitlement Core agar bisa export
        app(EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['business_id' => $this->business->id, 'name' => 'Pieces', 'category' => Unit::CATEGORY_QUANTITY]
        );

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Member Loyal',
            'is_active' => true,
        ]);

        $this->productA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Blend',
            'code' => 'ESP-001',
            'output_unit_id' => $unit->id,
            'base_cost' => 15000,
            'selling_price' => 30000,
            'is_active' => true,
        ]);

        $this->productB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Croissant Butter',
            'code' => 'CRS-002',
            'output_unit_id' => $unit->id,
            'base_cost' => 10000,
            'selling_price' => 25000,
            'is_active' => true,
        ]);
    }

    public function test_pos_reports_view_and_kpi_consistency(): void
    {
        $startDate = Carbon::today()->subDays(5);
        $endDate = Carbon::today();

        // 1. Order inside range - Member customer with discount, tax, service charge, rounding
        $order1 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'POS-IN-001',
            'order_date' => Carbon::today()->subDays(2)->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja 05',
            'subtotal' => 100000,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_amount' => 10000,
            'voucher_code' => 'PROMO5',
            'voucher_discount_amount' => 5000,
            'points_discount_amount' => 2000,
            'tax_percentage' => 11,
            'tax_amount' => 9130,
            'service_charge_percentage' => 5,
            'service_charge_amount' => 4150,
            'rounding_amount' => 20,
            'total_amount' => 96300,
            'paid_amount' => 100000,
            'change_amount' => 3700,
            'total_hpp_cost' => 50000,
            'total_gross_profit' => 33000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'product_code' => $this->productA->code,
            'quantity' => 2,
            'unit_price' => 30000,
            'unit_cost_hpp' => 15000,
            'discount_amount' => 0,
            'subtotal' => 60000,
            'total_price' => 60000,
            'total_hpp' => 30000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order1->id,
            'product_id' => $this->productB->id,
            'product_name' => $this->productB->name,
            'product_code' => $this->productB->code,
            'quantity' => 1.6,
            'unit_price' => 25000,
            'unit_cost_hpp' => 10000,
            'discount_amount' => 0,
            'subtotal' => 40000,
            'total_price' => 40000,
            'total_hpp' => 20000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'fee_amount' => 0,
            'net_amount' => 50000,
            'status' => 'success',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order1->id,
            'payment_method' => 'qris',
            'amount' => 46300,
            'fee_amount' => 324,
            'net_amount' => 45976,
            'status' => 'success',
            'reference_number' => 'QRIS-REF-99',
        ]);

        // 2. Order inside range - Guest customer with partial refund status
        $order2 = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'customer_id' => null,
            'customer_name_guest' => 'Tamu Kantor',
            'order_number' => 'POS-IN-002',
            'order_date' => Carbon::today()->subDay()->toDateString(),
            'status' => PosOrder::STATUS_PARTIAL_REFUND,
            'order_type' => 'takeaway',
            'subtotal' => 30000,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'service_charge_percentage' => 0,
            'service_charge_amount' => 0,
            'rounding_amount' => 0,
            'total_amount' => 30000,
            'paid_amount' => 30000,
            'change_amount' => 0,
            'total_hpp_cost' => 15000,
            'total_gross_profit' => 15000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order2->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'product_code' => $this->productA->code,
            'quantity' => 1,
            'unit_price' => 30000,
            'unit_cost_hpp' => 15000,
            'subtotal' => 30000,
            'total_price' => 30000,
            'total_hpp' => 15000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order2->id,
            'payment_method' => 'edc_debit',
            'amount' => 30000,
            'status' => 'success',
        ]);

        // 3. Order OUTSIDE date range (60 days ago)
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'POS-OUTSIDE-999',
            'order_date' => Carbon::today()->subDays(60)->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'total_hpp_cost' => 200000,
            'total_gross_profit' => 300000,
        ]);

        // Test Index Screen
        $response = $this->actingAs($this->user)->get(route('pos.reports.index', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Laporan &amp; Analitik POS', false);
        // Assert filtered revenue (96300 + 30000 = 126300)
        $response->assertSee('126.300');
        // Assert outside order is NOT summed into the KPI
        $response->assertDontSee('626.300');
    }

    public function test_pos_export_excel_honors_date_filter_and_includes_full_detail(): void
    {
        $startDate = Carbon::today()->subDays(5);
        $endDate = Carbon::today();

        // Inside Range Order
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'POS-TARGET-001',
            'order_date' => Carbon::today()->subDays(2)->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja VIP 1',
            'subtotal' => 60000,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_amount' => 6000,
            'voucher_code' => 'VOUCH10',
            'voucher_discount_amount' => 4000,
            'points_discount_amount' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 5390,
            'service_charge_percentage' => 5,
            'service_charge_amount' => 2450,
            'rounding_amount' => 10,
            'total_amount' => 56850,
            'paid_amount' => 60000,
            'change_amount' => 3150,
            'total_hpp_cost' => 30000,
            'total_gross_profit' => 19000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'product_code' => $this->productA->code,
            'quantity' => 2,
            'unit_price' => 30000,
            'unit_cost_hpp' => 15000,
            'subtotal' => 60000,
            'total_price' => 60000,
            'total_hpp' => 30000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 30000,
            'status' => 'success',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 26850,
            'fee_amount' => 188,
            'net_amount' => 26662,
            'reference_number' => 'QRIS-ABC-77',
            'status' => 'success',
        ]);

        // Outside Range Order
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'POS-IGNORED-888',
            'order_date' => Carbon::today()->subDays(45)->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 999000,
            'total_amount' => 999000,
            'paid_amount' => 999000,
            'total_hpp_cost' => 400000,
            'total_gross_profit' => 599000,
        ]);

        $response = $this->actingAs($this->user)->get(route('pos.reports.export-excel', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csvContent = $response->streamedContent();

        // 1. Verifikasi filter tanggal: Order di dalam range ADA, order di luar range TIDAK ADA
        $this->assertStringContainsString('POS-TARGET-001', $csvContent);
        $this->assertStringNotContainsString('POS-IGNORED-888', $csvContent);

        // 2. Verifikasi Header dan KPI Summary
        $this->assertStringContainsString('LAPORAN PENJUALAN KASIR POS - DETAIL TRANSAKSI, ITEM & PEMBAYARAN', $csvContent);
        $this->assertStringContainsString('Total Penjualan (Grand Total)', $csvContent);
        $this->assertStringContainsString('Total Diskon Order', $csvContent);
        $this->assertStringContainsString('Total Diskon Voucher', $csvContent);
        $this->assertStringContainsString('Total Diskon Poin', $csvContent);
        $this->assertStringContainsString('Total Pajak / PPN', $csvContent);
        $this->assertStringContainsString('Total Service Charge', $csvContent);
        $this->assertStringContainsString('Total Pembulatan (Rounding)', $csvContent);

        // 3. Verifikasi Section 1: Rincian Transaksi per Order (customer, discount type/value, tax %, service charge, rounding, payments)
        $this->assertStringContainsString('--- 1. RINCIAN TRANSAKSI (PER ORDER) ---', $csvContent);
        $this->assertStringContainsString('Member Loyal', $csvContent);
        $this->assertStringContainsString('Persentase (%)', $csvContent);
        $this->assertStringContainsString('VOUCH10', $csvContent);
        $this->assertStringContainsString('11', $csvContent); // tax percentage
        $this->assertStringContainsString('5390', $csvContent); // tax amount
        $this->assertStringContainsString('2450', $csvContent); // service charge amount
        $this->assertStringContainsString('10', $csvContent); // rounding
        $this->assertStringContainsString('TUNAI', $csvContent);
        $this->assertStringContainsString('QRIS', $csvContent);

        // 4. Verifikasi Section 2: Detail Item per Transaksi
        $this->assertStringContainsString('--- 2. DETAIL ITEM PER TRANSAKSI ---', $csvContent);
        $this->assertStringContainsString('ESP-001', $csvContent);
        $this->assertStringContainsString('Espresso Blend', $csvContent);

        // 5. Verifikasi Section 3: Rincian Pembayaran
        $this->assertStringContainsString('--- 3. RINCIAN PEMBAYARAN (PER METODE) ---', $csvContent);
        $this->assertStringContainsString('QRIS-ABC-77', $csvContent);

        // 6. Verifikasi Section 4: Ringkasan Metode Pembayaran
        $this->assertStringContainsString('--- 4. RINGKASAN METODE PEMBAYARAN ---', $csvContent);

        // 7. Verifikasi Section 5: Ringkasan Penjualan per Produk
        $this->assertStringContainsString('--- 5. RINGKASAN PENJUALAN PER PRODUK ---', $csvContent);
    }

    public function test_business_reports_export_includes_separate_tax_section_matching_screen(): void
    {
        $today = Carbon::today();

        // 1. POS order with tax
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'POS-TAX-01',
            'order_date' => $today->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000,
            'tax_percentage' => 11,
            'tax_amount' => 11000,
            'service_charge_amount' => 5000,
            'total_amount' => 116000,
            'paid_amount' => 116000,
            'total_hpp_cost' => 50000,
            'total_gross_profit' => 50000,
        ]);

        // 2. Invoice with tax
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TAX-01',
            'invoice_date' => $today->toDateString(),
            'due_date' => $today->copy()->addDays(30)->toDateString(),
            'status' => Invoice::STATUS_PAID,
            'subtotal' => 200000,
            'tax_percentage' => 11,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'paid_amount' => 222000,
            'balance_due' => 0,
            'total_gross_profit' => 100000,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $this->productA->id,
            'item_name' => $this->productA->name,
            'unit_id' => $this->productA->output_unit_id,
            'quantity' => 4,
            'unit_price' => 50000,
            'unit_hpp' => 25000,
            'subtotal' => 200000,
            'total_hpp' => 100000,
            'gross_profit' => 100000,
        ]);

        // 1. Test Web View: Section Pajak yang Dipungut exists and displays correct numbers
        $webResponse = $this->actingAs($this->user)->get(route('reports.index', [
            'start_date' => $today->startOfMonth()->toDateString(),
            'end_date' => $today->endOfMonth()->toDateString(),
        ]));

        $webResponse->assertOk();
        $webResponse->assertSee('PAJAK YANG DIPUNGUT (PPN / TAX)');
        $webResponse->assertSee('Pajak PPN Transaksi Kasir POS');
        $webResponse->assertSee('11.000');
        $webResponse->assertSee('Pajak PPN Faktur Invoice');
        $webResponse->assertSee('22.000');
        $webResponse->assertSee('TOTAL PAJAK DIPUNGUT');
        $webResponse->assertSee('33.000');

        // 2. Test CSV Export: Contains separate tax section and numbers match screen exactly
        $exportResponse = $this->actingAs($this->user)->get(route('reports.export-excel', [
            'type' => 'income_statement',
            'start_date' => $today->startOfMonth()->toDateString(),
            'end_date' => $today->endOfMonth()->toDateString(),
        ]));

        $exportResponse->assertOk();
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csvContent = $exportResponse->streamedContent();

        $this->assertStringContainsString('PAJAK YANG DIPUNGUT (PPN / TAX)', $csvContent);
        $this->assertStringContainsString('Pajak PPN Transaksi Kasir POS', $csvContent);
        $this->assertStringContainsString('11000', $csvContent);
        $this->assertStringContainsString('Pajak PPN Faktur Invoice', $csvContent);
        $this->assertStringContainsString('22000', $csvContent);
        $this->assertStringContainsString('Biaya Layanan (Service Charge) POS', $csvContent);
        $this->assertStringContainsString('5000', $csvContent);
        $this->assertStringContainsString('TOTAL PAJAK DIPUNGUT', $csvContent);
        $this->assertStringContainsString('33000', $csvContent);
    }
}
