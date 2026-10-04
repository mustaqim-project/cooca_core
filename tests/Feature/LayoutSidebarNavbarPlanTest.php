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
        app()->setLocale('id');
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

        // Check 4 Standard Clusters of COOCA for Owner
        $response->assertSee('Operasional Harian');
        $response->assertSee('Master Data &amp; Katalog', false);
        $response->assertSee('Laporan &amp; Keuangan', false);
        $response->assertSee('Pusat Pengaturan');

        // Check Overview list (Clean Bento Apple HIG)
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Asisten Cerdas AI');

        // Check Reports Center list
        $response->assertSee('Pusat Laporan');
        $response->assertSee('Analitik Bisnis &amp; Tren', false);
        $response->assertSee('Laba Rugi (Profit &amp; Loss)', false);
        $response->assertSee('Arus Kas (Cash Flow)');
        $response->assertSee('Laporan Penjualan Kasir');
        $response->assertSee('Laporan Pajak &amp; Kepatuhan', false);

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

        // Cashier sees Daily Ops -> Dashboard & POS Terminal
        $response->assertSee('Operasional Harian');
        $response->assertSee('id="tour-nav-pos-terminal"', false);
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Asisten Cerdas AI');

        // Cashier does not see Settings Hub management in DOM
        $response->assertDontSee('id="tour-nav-settings"', false);
        $response->assertDontSee('id="tour-nav-audit-logs"', false);
        $response->assertDontSee('id="tour-nav-approval-rules"', false);
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

        // Warehouse sees: Master Data -> Produk & Materials
        $response->assertSee('Master Data &amp; Katalog', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);

        // Warehouse MUST NOT see: Settings Hub, Audit Logs, MAR Rules
        $response->assertDontSee('id="tour-nav-settings"', false);
        $response->assertDontSee('id="tour-nav-audit-logs"', false);
        $response->assertDontSee('id="tour-nav-approval-rules"', false);
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

        // Finance sees: Laporan & Keuangan
        $response->assertSee('Laporan &amp; Keuangan', false);
        $response->assertSee('Buku Kas &amp; Bank', false);
        $response->assertSee('Pusat Laporan');

        // Finance MUST NOT see: POS Terminal, Settings Hub
        $response->assertDontSee('id="tour-nav-pos-terminal"', false);
        $response->assertDontSee('id="tour-nav-settings"', false);
        $response->assertDontSee('id="tour-nav-audit-logs"', false);
        $response->assertDontSee('id="tour-nav-approval-rules"', false);
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
        $response->assertSee('id="tour-nav-pos-terminal"', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('Buku Kas &amp; Bank', false);

        // Disabled modules auto-hide cleanly
        $response->assertDontSee('id="tour-nav-sales-orders"', false);
        $response->assertDontSee('id="tour-nav-quotations"', false);
        $response->assertDontSee('id="tour-nav-materials"', false);
    }

    public function test_topbar_spotlight_search_contains_all_categories_and_routes(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check category chips in spotlight search
        $response->assertSee("{ id: 'all', label: 'Semua' }", false);
        $response->assertSee("{ id: 'Dashboard', label: 'Operasional Harian' }", false);
        $posLabel = ($this->business->isFoodIndustry()) ? e(__('navigation.pos_resto')) : e(__('navigation.pos_terminal'));
        $response->assertSee("{ id: 'Kasir & Penjualan', label: '{$posLabel}' }", false);
        $response->assertSee("{ id: 'Produk & Stok', label: 'Master Data &amp; Katalog' }", false);
        $response->assertSee("{ id: 'Pembelian & Supplier', label: '" . e(__('navigation.suppliers')) . "' }", false);
        $response->assertSee("{ id: 'Pelanggan & Pemasaran', label: '" . e(__('navigation.crm_customers')) . "' }", false);
        $response->assertSee("{ id: 'Keuangan & Biaya', label: 'Laporan &amp; Keuangan' }", false);
        $response->assertSee("{ id: 'Laporan & Analitik', label: '" . e(__('navigation.sales_report')) . "' }", false);
        $response->assertSee("{ id: 'Pengaturan Usaha', label: 'Pusat Pengaturan' }", false);

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

        // Verify Bento Clusters in Tab 4
        $response->assertSee('Kelola Modul &amp; Penataan Menu Sidebar', false);
        $response->assertSee('1. Operasional Kasir POS &amp; Restoran', false);
        $response->assertSee('2. Penjualan B2B, Penawaran &amp; Invoice', false);
        $response->assertSee('3. Produksi HPP, Resep BOM &amp; Pergudangan', false);
        $response->assertSee('4. CRM, Loyalitas Pelanggan &amp; WhatsApp Marketing', false);
        $response->assertSee('5. Toko Online, Reservasi &amp; Pemesanan Mandiri', false);

        // Verify Live Impact Badges
        $response->assertSee('Sidebar:</strong> Kasir &amp; POS Resto: Buka Kasir POS, Transaksi Kasir &amp; Shift, Laporan Kasir', false);
        $response->assertSee('Sidebar:</strong> Penjualan B2B &amp; Faktur: Pesanan Penjualan (SO), Surat Penawaran, Faktur Tagihan (Invoice), Retur Penjualan', false);
        $response->assertSee('Sidebar:</strong> Produk &amp; Logistik: Bahan Baku &amp; Resep (BOM), Kategori Bahan, Satuan Ukur', false);
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
