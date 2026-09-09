<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutSidebarNavbarPlanTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap Jiwa',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'rounding_strategy' => 'ROUND_100',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_sidebar_renders_clean_logical_groups(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check 10-pillar consolidated module groups for Owner
        $response->assertSee('Kasir & Penjualan');
        $response->assertSee('Pembelian & Vendor');
        $response->assertSee('Produk & Inventori');
        $response->assertSee('HPP & Produksi');
        $response->assertSee('Keuangan & Kas');
        $response->assertSee('Laporan & Analitik');
        $response->assertSee('Pengaturan Usaha');
        $response->assertSee('Kontrol Akses & Role');
        $response->assertSee('Paket & Kuota');

        // Check key sub-menus from Phases 1-5
        $response->assertSee('Retur Penjualan');
        $response->assertSee('Retur Pembelian');
        $response->assertSee('Tagihan & Hutang Supplier');
        $response->assertSee('Kas & Rekening Bank');
        $response->assertSee('Buku Kas & Ledger');
        $response->assertSee('Piutang Usaha (AR Aging)');
        $response->assertSee('Hutang Usaha (AP Aging)');

        // Check key menu routes & tour IDs
        $response->assertSee('id="tour-nav-dashboard"', false);
        $response->assertSee('id="tour-nav-calculator"', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);
        $response->assertSee('id="tour-nav-labor-machines"', false);
        $response->assertSee('id="tour-nav-simulator"', false);
        $response->assertSee('id="tour-nav-profitability"', false);
        $response->assertSee('id="tour-nav-reports"', false);
        $response->assertSee('id="tour-nav-settings"', false);

        // Check Mobile Bottom Navigation App Bar
        $response->assertSee('Aksi Cepat Instan');
        $response->assertSee('Home');
        $response->assertSee('Kasir');
        $response->assertSee('Menu');
    }

    public function test_sidebar_role_cashier_only_sees_cashier_and_sales_menus(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $cashierRole = \App\Models\Role::where('slug', 'cashier')->firstOrFail();

        $cashier = User::create([
            'name' => 'Siti Kasir',
            'email' => 'siti@cooca.id',
            'password' => 'password123',
        ]);

        $this->business->users()->attach($cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);
        $cashier->update(['active_business_id' => $this->business->id]);

        Context::flush();

        $membership = BusinessMembership::where('business_id', $this->business->id)->where('user_id', $cashier->id)->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Cashier sees: POS, Faktur, Pelanggan, AI
        $response->assertSee('Kasir & Penjualan');
        $response->assertSee('Faktur & Piutang');
        $response->assertSee('Pelanggan');
        $response->assertSee('AI Assistant');

        // Cashier MUST NOT see: HPP, Keuangan, Pembelian, Pengaturan
        $response->assertDontSee('HPP & Produksi');
        $response->assertDontSee('Kalkulator HPP 3-Pilar');
        $response->assertDontSee('Keuangan & Kas');
        $response->assertDontSee('Kas & Rekening Bank');
        $response->assertDontSee('Buku Kas & Ledger');
        $response->assertDontSee('Pembelian & Vendor');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Kontrol Akses & Role');
        $response->assertDontSee('Paket & Kuota');
    }

    public function test_sidebar_role_warehouse_only_sees_inventory_and_purchasing(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $warehouseRole = \App\Models\Role::where('slug', 'warehouse')->firstOrFail();

        $warehouseStaff = User::create([
            'name' => 'Agus Gudang',
            'email' => 'agus@cooca.id',
            'password' => 'password123',
        ]);

        $this->business->users()->attach($warehouseStaff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'warehouse',
            'role_id' => $warehouseRole->id,
        ]);
        $warehouseStaff->update(['active_business_id' => $this->business->id]);

        $membership = BusinessMembership::where('business_id', $this->business->id)->where('user_id', $warehouseStaff->id)->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($warehouseStaff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Warehouse sees: Produk, Bahan, Gudang, Stok, Mutasi, Transfer, Opname, PO, Supplier
        $response->assertSee('Produk & Inventori');
        $response->assertSee('Katalog Produk & Resep');
        $response->assertSee('Bahan Baku & Harga');
        $response->assertSee('Gudang & Lokasi');
        $response->assertSee('Stok Real-Time');
        $response->assertSee('Transfer Stok Gudang');
        $response->assertSee('Stock Opname Fisik');
        $response->assertSee('Pembelian & Vendor');

        // Warehouse MUST NOT see: Kasir & Penjualan, Faktur, Keuangan, HPP margin formula, Pengaturan
        $response->assertDontSee('Kasir & Penjualan');
        $response->assertDontSee('Faktur & Piutang');
        $response->assertDontSee('Keuangan & Kas');
        $response->assertDontSee('Kas & Rekening Bank');
        $response->assertDontSee('Buku Kas & Ledger');
        $response->assertDontSee('HPP & Produksi');
        $response->assertDontSee('Kalkulator HPP 3-Pilar');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Kontrol Akses & Role');
        $response->assertDontSee('Paket & Kuota');
    }

    public function test_sidebar_role_finance_only_sees_finance_and_reports(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $financeRole = \App\Models\Role::where('slug', 'finance')->firstOrFail();

        $financeStaff = User::create([
            'name' => 'Dewi Keuangan',
            'email' => 'dewi@cooca.id',
            'password' => 'password123',
        ]);

        $this->business->users()->attach($financeStaff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'finance',
            'role_id' => $financeRole->id,
        ]);
        $financeStaff->update(['active_business_id' => $this->business->id]);

        $membership = BusinessMembership::where('business_id', $this->business->id)->where('user_id', $financeStaff->id)->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($financeStaff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Finance sees: Keuangan (Kas & Bank, Ledger, Beban, Piutang, Hutang, Jurnal), Laporan
        $response->assertSee('Keuangan & Kas');
        $response->assertSee('Kas & Rekening Bank');
        $response->assertSee('Buku Kas & Ledger');
        $response->assertSee('Beban Operasional');
        $response->assertSee('Piutang Usaha (AR Aging)');
        $response->assertSee('Hutang Usaha (AP Aging)');
        $response->assertSee('Jurnal Akuntansi Otomatis');
        $response->assertSee('Laporan & Analitik');

        // Finance MUST NOT see: POS terminal, Stock Opname, Transfer Gudang, Pengaturan Toko
        $response->assertDontSee('Terminal Kasir POS');
        $response->assertDontSee('Riwayat Transaksi & Shift');
        $response->assertDontSee('Stock Opname Fisik');
        $response->assertDontSee('Transfer Stok Gudang');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Kontrol Akses & Role');
        $response->assertDontSee('Paket & Kuota');
    }

    public function test_navbar_renders_free_plan_tracker_with_usage_progress(): void
    {
        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        // Create 2 products
        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Single',
            'sku' => 'ESP-001',
            'output_unit_id' => $unit->id,
            'base_cost' => 3000,
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Paket Free (Solo)');
        $response->assertSee('Upgrade');
        $response->assertSee('Katalog Produk:');
        $response->assertSee('/ 50');
        $response->assertSee('Resep / BOM:');
        $response->assertSee('/ 20');
        $response->assertSee('Invoice Bulan Ini:');
        $response->assertSee('/ 10');
        $response->assertSee('Tingkatkan ke Cooca UMKM');
    }

    public function test_navbar_renders_core_plan_when_subscribed(): void
    {
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $entitlement->upgradeToCore($this->business, 'monthly');

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Cooca UMKM');
        $response->assertSee('∞ Unlimited', false);
        $response->assertSee('Token AI (Top-up):');
    }
}
