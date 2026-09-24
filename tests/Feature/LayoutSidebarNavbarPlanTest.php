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
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $ownerRole = \App\Models\Role::where('slug', 'owner')->first();

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
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
            'role_id' => $ownerRole?->id,
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        $membership = BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->user->id)->first();
        Context::setBusiness($this->business, $membership);
    }

    public function test_sidebar_renders_clean_logical_groups(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check 8-pillar Bento Apple HIG groups for Owner
        $response->assertSee('Ringkasan & Dashboard');
        $response->assertSee('Kasir & Penjualan');
        $response->assertSee('Produk & Persediaan');
        $response->assertSee('Pembelian & Supplier');
        $response->assertSee('Pelanggan & Pemasaran');
        $response->assertSee('Keuangan & Biaya');
        $response->assertSee('Laporan & Analitik');
        $response->assertSee('Pengaturan Usaha');

        // Check Overview list (Clean Bento Apple HIG)
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Asisten Cerdas AI');

        // Check Reports Center list (Clean 7 pure report items)
        $response->assertSee('Pusat Laporan');
        $response->assertSee('Analitik Bisnis & Tren');
        $response->assertSee('Laba Rugi (Profit & Loss)');
        $response->assertSee('Arus Kas (Cash Flow)');
        $response->assertSee('Laporan Penjualan Kasir');
        $response->assertSee('Valuasi & Perputaran Stok');
        $response->assertSee('Ringkasan Laporan Pajak');

        // Check key menu routes & tour IDs
        $response->assertSee('id="tour-nav-dashboard"', false);
        $response->assertSee('id="tour-nav-pos-terminal"', false);
        $response->assertSee('id="tour-nav-calculator"', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);
        $response->assertSee('id="tour-nav-labor-machines"', false);
        $response->assertSee('id="tour-nav-simulator"', false);
        $response->assertSee('id="tour-nav-profitability"', false);
        $response->assertSee('id="tour-nav-reports"', false);
        $response->assertSee('id="tour-nav-settings"', false);

        // Check Topbar Spotlight Search (Ctrl+K)
        $response->assertSee('spotlightOpen');
        $response->assertSee('Cari menu, modul, transaksi...');
        $response->assertSee('Ctrl K');

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

        // Cashier sees: Ringkasan & Dashboard, Kasir & Penjualan, Buka Kasir POS, Transaksi Kasir, Asisten AI
        $response->assertSee('Ringkasan & Dashboard');
        $response->assertSee('Kasir & Penjualan');
        $response->assertSee('Buka Kasir POS');
        $response->assertSee('Transaksi Kasir & Shift');
        $response->assertSee('Asisten Cerdas AI');

        // Cashier MUST NOT see: Keuangan & Biaya, Laporan & Analitik, Pembelian, Pengaturan
        $response->assertDontSee('Keuangan & Biaya');
        $response->assertDontSee('Laporan & Analitik');
        $response->assertDontSee('Kas & Rekening Bank');
        $response->assertDontSee('Pembelian & Supplier');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Hak Akses & Peran Staf');
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
        $response->assertSee('Produk & Persediaan');
        $response->assertSee('Katalog Produk & Menu');
        $response->assertSee('Bahan Baku & Resep');
        $response->assertSee('Lokasi Gudang');
        $response->assertSee('Stok Gudang');
        $response->assertSee('Transfer Stok Gudang');
        $response->assertSee('Opname Stok Fisik');
        $response->assertSee('Pembelian & Supplier');

        // Warehouse MUST NOT see: Buka Kasir POS, Keuangan, Laporan, Pengaturan
        $response->assertDontSee('Buka Kasir POS');
        $response->assertDontSee('Keuangan & Biaya');
        $response->assertDontSee('Laporan & Analitik');
        $response->assertDontSee('Kas & Rekening Bank');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Hak Akses & Peran Staf');
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

        // Finance sees: Keuangan, Laporan & Analitik
        $response->assertSee('Keuangan & Biaya');
        $response->assertSee('Laporan & Analitik');
        $response->assertSee('Kas & Rekening Bank');
        $response->assertSee('Buku Besar Akun');
        $response->assertSee('Pengeluaran Operasional');
        $response->assertSee('Daftar Piutang Usaha');
        $response->assertSee('Daftar Utang Usaha');
        $response->assertSee('Buku Jurnal Keuangan');
        $response->assertSee('Pusat Laporan');

        // Finance MUST NOT see: POS terminal, Stock Opname, Transfer Gudang, Pengaturan Toko
        $response->assertDontSee('Buka Kasir POS');
        $response->assertDontSee('Opname Stok Fisik');
        $response->assertDontSee('Transfer Stok Gudang');
        $response->assertDontSee('Pengaturan Usaha');
        $response->assertDontSee('Hak Akses & Peran Staf');
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
            ->withSession(['active_business_id' => $this->business->id])
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
        $response->assertSee('Tingkatkan ke Cooca');
    }

    public function test_navbar_renders_core_plan_when_subscribed(): void
    {
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $entitlement->upgradeToCore($this->business, 'monthly');

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Cooca');
        $response->assertSee('∞ Unlimited', false);
        $response->assertSee('Token AI (Top-up):');
    }

    public function test_sidebar_modular_auto_hide_when_modules_disabled(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        // Disable B2B sales and recipe BOM modules on this business
        $this->business->update([
            'disabled_modules' => [
                \App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES,
                \App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM,
                \App\Domain\Template\ModuleRegistry::MODULE_RESERVATION,
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Enabled modules remain visible
        $response->assertSee('Buka Kasir POS');
        $response->assertSee('Katalog Produk & Menu');
        $response->assertSee('Kas & Rekening Bank');

        // Disabled modules auto-hide cleanly
        $response->assertDontSee('Pesanan Penjualan (Sales Orders)');
        $response->assertDontSee('Surat Penawaran (Quotations)');
        $response->assertDontSee('Bahan Baku & Resep (BOM)');
        $response->assertDontSee('Reservasi Meja');
    }

    public function test_topbar_spotlight_search_contains_all_categories_and_routes(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check 8-pillar category chips in spotlight search
        $response->assertSee("{ id: 'all', label: 'Semua' }", false);
        $response->assertSee("{ id: 'Dashboard', label: 'Dashboard' }", false);
        $response->assertSee("{ id: 'Kasir & Penjualan', label: 'Kasir & Penjualan' }", false);
        $response->assertSee("{ id: 'Produk & Stok', label: 'Produk & Stok' }", false);
        $response->assertSee("{ id: 'Pembelian & Supplier', label: 'Pembelian & Supplier' }", false);
        $response->assertSee("{ id: 'Pelanggan & Pemasaran', label: 'Pelanggan & Pemasaran' }", false);
        $response->assertSee("{ id: 'Keuangan & Biaya', label: 'Keuangan & Biaya' }", false);
        $response->assertSee("{ id: 'Laporan & Analitik', label: 'Laporan & Analitik' }", false);
        $response->assertSee("{ id: 'Pengaturan Usaha', label: 'Pengaturan Usaha' }", false);

        // Check newly indexed routes in spotlight
        $response->assertSee('Desain Halaman Toko (Mini-Site)', false);
        $response->assertSee('Pengaturan Toko Online & Pembayaran', false);
        $response->assertSee('Pengaturan Ongkos Kirim', false);
    }

    public function test_settings_module_tab_renders_5_thematic_bento_clusters(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertStatus(200);

        // Verify 5 Bento Clusters in Tab 4
        $response->assertSee('Kelola Modul &amp; Penataan Menu Sidebar', false);
        $response->assertSee('1. Operasional Kasir POS &amp; Restoran', false);
        $response->assertSee('2. Penjualan B2B, Penawaran &amp; Invoice', false);
        $response->assertSee('3. Produksi HPP, Resep BOM &amp; Pergudangan', false);
        $response->assertSee('4. CRM, Loyalitas Pelanggan &amp; WhatsApp Marketing', false);
        $response->assertSee('5. Toko Online, Reservasi &amp; Pemesanan Mandiri', false);

        // Verify Live Impact Badges
        $response->assertSee('Sidebar:</strong> Buka Kasir POS, Transaksi Kasir &amp; Shift, Laporan Kasir', false);
        $response->assertSee('Sidebar:</strong> Pesanan Penjualan (SO), Penawaran Harga, Faktur Tagihan (Invoice), Retur Penjualan', false);
        $response->assertSee('Sidebar:</strong> Bahan Baku &amp; Resep (BOM), Kategori Bahan Baku', false);
    }

    public function test_settings_module_update_by_owner_persists_and_restricts_non_owner(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        // 1. Owner updates enabled modules
        $enabledModules = [
            \App\Domain\Template\ModuleRegistry::MODULE_POS_RETAIL,
            \App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('settings.modules.update'), [
                'enabled_modules' => $enabledModules,
            ]);

        $response->assertRedirect();
        $this->business->refresh();

        $this->assertTrue($this->business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_RETAIL));
        $this->assertTrue($this->business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE));
        $this->assertFalse($this->business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES));
        $this->assertFalse($this->business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM));

        // 2. Non-owner (cashier) attempts to update modules -> 403 Forbidden
        $cashierRole = \App\Models\Role::where('slug', 'cashier')->firstOrFail();
        $cashier = User::create([
            'name' => 'Doni Kasir',
            'email' => 'doni@cooca.id',
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

        $forbiddenResponse = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('settings.modules.update'), [
                'enabled_modules' => [\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES],
            ]);

        $forbiddenResponse->assertStatus(403);
    }
}
