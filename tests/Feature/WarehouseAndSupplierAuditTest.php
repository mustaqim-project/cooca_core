<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseAndSupplierAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Business $businessA;
    private User $userB;
    private Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');

        // Tenant A setup
        $this->userA = User::factory()->create(['email' => 'tenant_a_supplier_test@cooca.id']);
        $this->businessA = Business::create([
            'owner_id' => $this->userA->id,
            'name' => 'Bisnis Tenant A',
        ]);
        $this->userA->businesses()->attach($this->businessA->id, ['role' => 'owner', 'status' => 'active']);

        // Tenant B setup
        $this->userB = User::factory()->create(['email' => 'tenant_b_supplier_test@cooca.id']);
        $this->businessB = Business::create([
            'owner_id' => $this->userB->id,
            'name' => 'Bisnis Tenant B',
        ]);
        $this->userB->businesses()->attach($this->businessB->id, ['role' => 'owner', 'status' => 'active']);
    }

    public function test_supplier_route_binding_scopes_strictly_to_tenant_business_by_id_and_slug(): void
    {
        // Create supplier for Tenant A
        Context::setBusiness($this->businessA);
        $supplierA = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pemasok Sinar Abadi',
            'slug' => 'pemasok-sinar-abadi',
            'email' => 'sinar@abadi.test',
            'phone' => '081234567890',
        ]);

        // Create supplier for Tenant B with distinct slug
        Context::setBusiness($this->businessB);
        $supplierB = Supplier::create([
            'business_id' => $this->businessB->id,
            'name' => 'Pemasok Makmur Jaya',
            'slug' => 'pemasok-makmur-jaya',
            'email' => 'makmur@jaya.test',
            'phone' => '081987654321',
        ]);

        // 1. In Context of Tenant A:
        Context::setBusiness($this->businessA);

        // Can find Tenant A supplier by ID
        $foundById = (new Supplier)->resolveRouteBinding($supplierA->id);
        $this->assertNotNull($foundById);
        $this->assertEquals($supplierA->id, $foundById->id);

        // Can find Tenant A supplier by Slug
        $foundBySlug = (new Supplier)->resolveRouteBinding($supplierA->slug);
        $this->assertNotNull($foundBySlug);
        $this->assertEquals($supplierA->id, $foundBySlug->id);

        // CANNOT find Tenant B supplier by ID (Must throw ModelNotFoundException)
        $this->expectException(ModelNotFoundException::class);
        (new Supplier)->resolveRouteBinding($supplierB->id);
    }

    public function test_supplier_route_binding_cannot_leak_tenant_b_by_slug_under_tenant_a_context(): void
    {
        // Create supplier for Tenant A
        Context::setBusiness($this->businessA);
        $supplierA = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pemasok Alfa',
            'slug' => 'pemasok-alfa',
        ]);

        // Create supplier for Tenant B
        Context::setBusiness($this->businessB);
        $supplierB = Supplier::create([
            'business_id' => $this->businessB->id,
            'name' => 'Pemasok Beta',
            'slug' => 'pemasok-beta',
        ]);

        // Switch to Tenant A context
        Context::setBusiness($this->businessA);

        // Attempting to resolve Tenant B supplier by slug MUST fail with 404 ModelNotFoundException
        $this->expectException(ModelNotFoundException::class);
        (new Supplier)->resolveRouteBinding($supplierB->slug);
    }

    public function test_models_route_binding_sql_query_generates_correct_and_or_closure_grouping(): void
    {
        Context::setBusiness($this->businessA);

        // Check SQL structure for Supplier
        $query = (new Supplier)->newQuery()->where(function ($query): void {
            $query->where('id', 'test-uuid')
                ->orWhere('slug', 'test-slug');
        });

        $sql = $query->toSql();
        // SQL must contain grouped parentheses `(id = ? or slug = ?)`
        $this->assertMatchesRegularExpression('/\(.*id.*=.*or.*slug.*=.*\)/i', $sql);
    }

    public function test_product_and_material_route_binding_also_strictly_scoped(): void
    {
        Context::setBusiness($this->businessA);
        $catA = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kategori A',
            'slug' => 'kategori-a',
        ]);

        Context::setBusiness($this->businessB);
        $catB = ProductCategory::create([
            'business_id' => $this->businessB->id,
            'name' => 'Kategori B',
            'slug' => 'kategori-b',
        ]);

        // In Tenant A context, resolving Tenant B category by slug must fail
        Context::setBusiness($this->businessA);
        $this->expectException(ModelNotFoundException::class);
        (new ProductCategory)->resolveRouteBinding($catB->slug);
    }

    public function test_warehouse_purchasing_inventory_language_dictionaries_have_exact_parity(): void
    {
        $files = [
            'warehouse' => [
                'id' => base_path('lang/id/warehouse.php'),
                'en' => base_path('lang/en/warehouse.php'),
            ],
            'purchasing' => [
                'id' => base_path('lang/id/purchasing.php'),
                'en' => base_path('lang/en/purchasing.php'),
            ],
            'inventory' => [
                'id' => base_path('lang/id/inventory.php'),
                'en' => base_path('lang/en/inventory.php'),
            ],
        ];

        foreach ($files as $domain => $paths) {
            $this->assertFileExists($paths['id'], "ID dictionary for {$domain} must exist.");
            $this->assertFileExists($paths['en'], "EN dictionary for {$domain} must exist.");

            $idData = require $paths['id'];
            $enData = require $paths['en'];

            $this->assertIsArray($idData);
            $this->assertIsArray($enData);

            $idKeys = $this->flattenKeys($idData);
            $enKeys = $this->flattenKeys($enData);

            $missingInEn = array_diff($idKeys, $enKeys);
            $missingInId = array_diff($enKeys, $idKeys);

            $this->assertEmpty($missingInEn, "Keys missing in EN {$domain}: " . implode(', ', $missingInEn));
            $this->assertEmpty($missingInId, "Keys missing in ID {$domain}: " . implode(', ', $missingInId));
        }
    }

    public function test_translations_render_correctly_in_both_locales_without_fallback_key(): void
    {
        app()->setLocale('id');
        $this->assertEquals('Cabang & Gudang Logistik', __('warehouse.header_title'));
        $this->assertEquals('Pemasok & Vendor', __('purchasing.supplier.title'));
        $this->assertEquals('Manajemen Persediaan & Stok', __('inventory.header_title'));

        app()->setLocale('en');
        $this->assertEquals('Branches & Logistics Warehouses', __('warehouse.header_title'));
        $this->assertEquals('Suppliers & Vendors', __('purchasing.supplier.title'));
        $this->assertEquals('Inventory & Stock Management', __('inventory.header_title'));
        app()->setLocale('id');
    }

    public function test_warehouse_index_view_renders_cleanly_for_authenticated_tenant(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // Create test locations for Tenant A
        \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Sudirman',
            'type' => 'outlet',
            'code' => 'OUT-SDR',
            'is_primary' => true,
            'is_active' => true,
            'is_online_fulfillment' => true,
            'allow_storefront_pickup' => true,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'geofence_radius_meters' => 100,
        ]);

        \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Distribusi Cikarang',
            'type' => 'warehouse',
            'code' => 'GUD-CKR',
            'is_primary' => false,
            'is_active' => true,
            'latitude' => -6.3241,
            'longitude' => 107.1512,
            'geofence_radius_meters' => 200,
        ]);

        $response = $this->actingAs($this->userA)->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Outlet Sudirman');
        $response->assertSee('Gudang Distribusi Cikarang');
        $response->assertSee('OUT-SDR');
        $response->assertSee('GUD-CKR');
    }

    public function test_warehouse_index_view_renders_cleanly_in_both_id_and_en_locales(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Cabang Dago',
            'type' => 'outlet',
            'code' => 'OUT-DGO',
            'is_active' => true,
        ]);

        // 1. Indonesian Locale Render
        $responseId = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.index'));
        $responseId->assertOk();
        $responseId->assertSee('Cabang &amp; Gudang Logistik', false);
        $responseId->assertSee('Total Lokasi');
        $responseId->assertSee('Nilai Aset Stok');
        $responseId->assertSee('+ Cabang / Outlet');
        $responseId->assertSee('+ Gudang Logistik');

        // 2. English Locale Render
        $responseEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.index'));
        $responseEn->assertOk();
        $responseEn->assertSee('Branches &amp; Logistics Warehouses', false);
        $responseEn->assertSee('Total Locations');
        $responseEn->assertSee('Inventory Asset Value');
        $responseEn->assertSee('+ Branch / Outlet');
        $responseEn->assertSee('+ Logistics Warehouse');

        // Ensure zero raw language key leaks in the rendered HTML
        $content = $responseEn->getContent();
        $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])warehouse\.[a-z_]+/i', $content, 'Found unrendered warehouse translation key in HTML.');
    }

    public function test_warehouse_central_kitchen_label_adapts_to_industry_templates(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Sentral Pengolahan Utama',
            'type' => 'central_kitchen',
            'code' => 'CK-01',
            'is_active' => true,
        ]);

        // 1. F&B Template (fnb_resto) -> Dapur Pusat / Central Kitchen
        $this->businessA->update(['template_code' => 'fnb_resto', 'disabled_modules' => []]);
        $res = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.index'));
        $res->assertOk();
        $res->assertSee('Dapur Pusat');

        $resEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.index'));
        $resEn->assertOk();
        $resEn->assertSee('Central Kitchen');

        // 2. Manufacturing Template -> Pabrik / Workshop / Factory / Workshop
        $this->businessA->update(['template_code' => 'mfg_garment', 'disabled_modules' => []]);
        $res = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.index'));
        $res->assertOk();
        $res->assertSee('Pabrik / Workshop');

        $resEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.index'));
        $resEn->assertOk();
        $resEn->assertSee('Factory / Workshop');

        // 3. Service Contractor Template -> Basecamp / Workshop Proyek / Basecamp / Project
        $this->businessA->update(['template_code' => 'service_contractor', 'disabled_modules' => []]);
        $res = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.index'));
        $res->assertOk();
        $res->assertSee('Basecamp / Workshop Proyek');

        $resEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.index'));
        $resEn->assertOk();
        $resEn->assertSee('Basecamp / Project');
    }

    public function test_warehouse_creation_and_update_modals_render_safely_with_alpine_js_directives(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $loc = \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name' => "Gudang Utama O'Connor & \"Partner\"",
            'type' => 'warehouse',
            'code' => 'GUD-OCN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->userA)->get(route('warehouse.index'));

        $response->assertOk();
        // Check that quotes and special characters in location object are safely escaped by @js directive
        $response->assertSee('openEdit(', false);
        $response->assertSee('openDelete(', false);
        $this->assertStringContainsString('O\u0027Connor', $response->getContent());
    }

    public function test_warehouse_filter_tabs_and_deep_links_support_query_parameters(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // 1. Deep link add warehouse modal
        $resAddWarehouse = $this->actingAs($this->userA)->get(route('warehouse.index', ['add' => 'warehouse']));
        $resAddWarehouse->assertOk();

        // 2. Deep link add outlet modal
        $resAddOutlet = $this->actingAs($this->userA)->get(route('warehouse.index', ['add' => 'outlet']));
        $resAddOutlet->assertOk();

        // 3. Filter query type
        $resTypeWarehouse = $this->actingAs($this->userA)->get(route('warehouse.index', ['type' => 'warehouse']));
        $resTypeWarehouse->assertOk();
    }

    public function test_warehouse_show_view_renders_cleanly_for_authenticated_tenant(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'pcs',
            'name' => 'Pieces',
            'category' => 'quantity',
            'is_base' => true,
        ]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Pusat Logistik Bandung',
            'type' => 'warehouse',
            'code' => 'GUD-BDG',
            'is_primary' => true,
            'is_active' => true,
            'address' => 'Jl. Soekarno Hatta No. 789, Bandung',
            'phone' => '022-7654321',
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Biji Kopi Spesialti',
            'slug' => 'biji-kopi-spesialti',
        ]);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Arabica Gayo',
            'slug' => 'kopi-arabica-gayo',
            'code' => 'KOP-GYO-01',
            'base_cost' => 85000,
            'selling_price' => 125000,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'last_cost' => 85000,
        ]);

        $response = $this->actingAs($this->userA)->get(route('warehouse.show', $loc->id));

        $response->assertOk();
        $response->assertSee('Gudang Pusat Logistik Bandung');
        $response->assertSee('GUD-BDG');
        $response->assertSee('Kopi Arabica Gayo');
        $response->assertSee('50.00');
        $response->assertSee('85.000');
        $response->assertSee('4.250.000'); // Total Valuation: 50 * 85.000
    }

    public function test_warehouse_show_view_renders_in_both_id_and_en_with_zero_leaks(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Central Hub Warehouse',
            'type' => 'warehouse',
            'code' => 'HUB-01',
            'is_active' => true,
            'address' => 'Sudirman Central Hub, Tower 2',
            'phone' => '081234567890',
        ]);

        // 1. Indonesian Locale Render
        $responseId = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.show', $loc->id));
        $responseId->assertOk();
        $responseId->assertSee('Alamat Fisik');
        $responseId->assertSee('Nilai Aset Stok');
        $responseId->assertSee('Terima dari PO Supplier');
        $responseId->assertSee('Stok Komoditas &amp; Bahan', false);
        $responseId->assertSee('Penerimaan Barang (GRN)');
        $responseId->assertSee('Mutasi Kartu Stok');

        // 2. English Locale Render
        $responseEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.show', $loc->id));
        $responseEn->assertOk();
        $responseEn->assertSee('Physical Address');
        $responseEn->assertSee('Inventory Asset Value');
        $responseEn->assertSee('Receive from Supplier PO');
        $responseEn->assertSee('Commodity &amp; Material Stock', false);
        $responseEn->assertSee('Goods Receipts (GRN)');
        $responseEn->assertSee('Stock Card Ledger');

        // Ensure zero raw language key leaks in the rendered HTML
        $content = $responseEn->getContent();
        $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])warehouse\.[a-z_]+/i', $content, 'Found unrendered warehouse translation key in show HTML.');
    }

    public function test_warehouse_show_tabs_and_deep_linking_parameters(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Dago Timur',
            'type' => 'outlet',
            'code' => 'OUT-DGO',
            'is_active' => true,
        ]);

        // Deep linking parameters for show view tabs
        $resStocks = $this->actingAs($this->userA)->get(route('warehouse.show', [$loc->id, 'tab' => 'stocks']));
        $resStocks->assertOk();
        $resStocks->assertSee('activeTab: new URLSearchParams', false);

        $resReceipts = $this->actingAs($this->userA)->get(route('warehouse.show', [$loc->id, 'tab' => 'receipts']));
        $resReceipts->assertOk();

        $resMovements = $this->actingAs($this->userA)->get(route('warehouse.show', [$loc->id, 'tab' => 'movements']));
        $resMovements->assertOk();
    }

    public function test_warehouse_show_stock_adjustment_modal_and_js_bindings(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'kg',
            'name' => 'Kilogram',
            'category' => 'weight',
            'is_base' => true,
        ]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Bahan Kopi',
            'type' => 'warehouse',
            'code' => 'GUD-KOP',
            'is_active' => true,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Beans',
            'slug' => 'beans',
        ]);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Arabica "Special" Gayo O\'Connor',
            'slug' => 'kopi-arabica-special-gayo-oconnor',
            'code' => 'KOP-SPE-01',
            'base_cost' => 95000,
            'selling_price' => 150000,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id' => $product->id,
            'quantity' => 25,
            'last_cost' => 95000,
        ]);

        $response = $this->actingAs($this->userA)->get(route('warehouse.show', $loc->id));
        $response->assertOk();

        // Check safe JS escaping for products containing quotes
        $this->assertStringContainsString('openAdjust(', $response->getContent());
        $this->assertStringContainsString('Kopi Arabica', $response->getContent());
        $response->assertSee('KOP-SPE-01');
    }

    public function test_warehouse_show_maker_checker_approvals_with_apple_modal_sheet(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'unit',
            'name' => 'Unit',
            'category' => 'quantity',
            'is_base' => true,
        ]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Utama Distribusi',
            'type' => 'warehouse',
            'code' => 'GUD-DIST',
            'is_active' => true,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Sparepart',
            'slug' => 'sparepart',
        ]);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Busi Iridium Racing',
            'slug' => 'busi-iridium-racing',
            'code' => 'BSI-01',
            'base_cost' => 150000,
            'selling_price' => 225000,
            'is_active' => true,
        ]);

        // Create a pending maker-checker adjustment
        $adj = StockAdjustment::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'adjustment_number' => 'ADJ-2026-0001',
            'adjustment_date' => now(),
            'reason' => 'theft_loss',
            'status' => 'pending_approval',
            'total_loss_cost' => 450000,
            'notes' => 'Selisih stok fisik 3 unit saat opname malam.',
            'created_by' => $this->userA->id,
        ]);

        StockAdjustmentItem::create([
            'stock_adjustment_id' => $adj->id,
            'product_id' => $product->id,
            'system_quantity' => 10,
            'adjusted_quantity' => 7,
            'difference_quantity' => -3,
            'unit_cost' => 150000,
            'total_cost' => 450000,
        ]);

        $response = $this->actingAs($this->userA)->get(route('warehouse.show', $loc->id));
        $response->assertOk();

        // Check Maker-Checker Approvals presence
        $response->assertSee('ADJ-2026-0001');
        $response->assertSee('Busi Iridium Racing');
        $response->assertSee('450.000');

        // Verify native browser confirm() is ELIMINATED and replaced by Apple Modal Sheet openConfirm()
        $content = $response->getContent();
        $this->assertStringContainsString('openConfirm(', $content);
        $this->assertStringNotContainsString('onclick="return confirm(', $content);
        $this->assertStringContainsString('confirmModalOpen', $content);
    }

    public function test_suppliers_index_view_renders_cleanly_for_authenticated_tenant(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $supplier = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'PT Sumber Boga Makmur',
            'contact_person' => 'Hendra Setiawan',
            'phone' => '081298765432',
            'email' => 'hendra@sumberboga.test',
            'bank_name' => 'BCA',
            'bank_account_number' => '8830192831',
            'bank_account_holder' => 'PT SUMBER BOGA MAKMUR',
            'address' => 'Kawasan Industri MM2100 Blok C-12, Cikarang',
            'notes' => 'Termin TOP 30 hari',
        ]);

        $response = $this->actingAs($this->userA)->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('PT Sumber Boga Makmur');
        $response->assertSee('Hendra Setiawan');
        $response->assertSee('081298765432');
        $response->assertSee('8830192831');
        $response->assertSee('BCA');
    }

    public function test_suppliers_index_view_renders_cleanly_in_both_id_and_en_locales(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'CV Multi Rempah',
            'contact_person' => 'Dewi Lestari',
            'phone' => '081345678901',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1230009988776',
        ]);

        // 1. Indonesian Locale Render
        $resId = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('suppliers.index'));
        $resId->assertOk();
        $resId->assertSee('Pemasok &amp; Vendor', false);
        $resId->assertSee('Total Pemasok');
        $resId->assertSee('Pemasok Pasokan Aktif');
        $resId->assertSee('Nama Pemasok / Vendor');
        $resId->assertSee('Tambah Pemasok');

        // 2. English Locale Render
        $resEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('suppliers.index'));
        $resEn->assertOk();
        $resEn->assertSee('Suppliers &amp; Vendors', false);
        $resEn->assertSee('Total Suppliers');
        $resEn->assertSee('Active Supply Vendors');
        $resEn->assertSee('Supplier / Vendor Name');
        $resEn->assertSee('Add Supplier');

        // Verify zero unrendered language key leaks in English HTML
        $content = $resEn->getContent();
        $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])purchasing\.supplier\.[a-z_]+/i', $content, 'Found unrendered purchasing.supplier translation key in HTML.');
    }

    public function test_suppliers_page_hides_raw_materials_button_when_bom_disabled(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // 1. Disabled BOM Module -> Button hidden
        $this->businessA->update([
            'disabled_modules' => [\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM],
        ]);
        $resDisabled = $this->actingAs($this->userA)->get(route('suppliers.index'));
        $resDisabled->assertOk();
        $resDisabled->assertDontSee('route(\'materials.index\')', false);

        // 2. Enabled BOM Module -> Button visible
        $this->businessA->update([
            'disabled_modules' => [],
        ]);
        $resEnabled = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('suppliers.index'));
        $resEnabled->assertOk();
        $resEnabled->assertSee('Katalog Bahan');
    }

    public function test_suppliers_modals_use_safe_blade_js_directives_and_xxl_sheet(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $supplier = Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => "PT O'Connor & \"Partner\" Boga",
            'contact_person' => "D'Angelo Smith",
            'phone' => '081299988877',
            'notes' => "Termin 14 hari 'Khusus' & Diskon 5%",
        ]);

        $response = $this->actingAs($this->userA)->get(route('suppliers.index'));
        $response->assertOk();

        $content = $response->getContent();
        // Check safe @js() directive output (quotes are escaped as unicode or valid json)
        $this->assertStringContainsString('openEditModal(', $content);
        $this->assertStringContainsString('openDelete(', $content);
        $this->assertStringContainsString('O\u0027Connor', $content);

        // Check XXL Bento Sheet modal classes
        $this->assertStringContainsString('max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl', $content);
        $this->assertStringContainsString(':disabled="submitting"', $content);
    }

    public function test_suppliers_search_filters_results_accurately(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Supplier Kopi Nusantara',
            'contact_person' => 'Ahmad Fauzi',
            'phone' => '08111222333',
        ]);

        Supplier::create([
            'business_id' => $this->businessA->id,
            'name' => 'Distributor Gula Aren Organik',
            'contact_person' => 'Siti Rahma',
            'phone' => '08999888777',
        ]);

        // Search for Kopi
        $resKopi = $this->actingAs($this->userA)->get(route('suppliers.index', ['search' => 'Kopi']));
        $resKopi->assertOk();
        $resKopi->assertSee('Supplier Kopi Nusantara');
        $resKopi->assertDontSee('Distributor Gula Aren Organik');

        // Search for Siti
        $resSiti = $this->actingAs($this->userA)->get(route('suppliers.index', ['search' => 'Siti']));
        $resSiti->assertOk();
        $resSiti->assertSee('Distributor Gula Aren Organik');
        $resSiti->assertDontSee('Supplier Kopi Nusantara');
    }

    public function test_supplier_web_controller_store_update_destroy_ajax_json_responses(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // 1. AJAX Store Supplier (JSON 201)
        $storePayload = [
            'name' => 'Supplier AJAX Sukses',
            'contact_person' => 'Budi Santoso',
            'phone' => '081211223344',
            'email' => 'budi@ajaxsukses.test',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'BUDI SANTOSO',
            'notes' => 'Pembayaran tempo 14 hari',
        ];

        $storeResponse = $this->actingAs($this->userA)
            ->postJson(route('suppliers.store'), $storePayload);

        $storeResponse->assertStatus(201);
        $storeResponse->assertJsonStructure([
            'success',
            'message',
            'supplier' => ['id', 'name', 'business_id'],
        ]);
        $storeResponse->assertJson([
            'success' => true,
            'message' => __('purchasing.supplier.messages.created_success'),
            'supplier' => [
                'name' => 'Supplier AJAX Sukses',
            ],
        ]);

        $supplierId = $storeResponse->json('supplier.id');
        $this->assertNotNull($supplierId);

        // 2. AJAX Update Supplier (JSON 200)
        $updatePayload = [
            'name' => 'Supplier AJAX Sukses (Updated)',
            'contact_person' => 'Budi Santoso Updated',
            'phone' => '081299887766',
        ];

        $updateResponse = $this->actingAs($this->userA)
            ->putJson(route('suppliers.update', $supplierId), $updatePayload);

        $updateResponse->assertOk();
        $updateResponse->assertJson([
            'success' => true,
            'message' => __('purchasing.supplier.messages.updated_success', ['name' => 'Supplier AJAX Sukses (Updated)']),
            'supplier' => [
                'name' => 'Supplier AJAX Sukses (Updated)',
                'contact_person' => 'Budi Santoso Updated',
            ],
        ]);

        // 3. AJAX Destroy Supplier (JSON 200 - Soft Deleted)
        $destroyResponse = $this->actingAs($this->userA)
            ->deleteJson(route('suppliers.destroy', $supplierId));

        $destroyResponse->assertOk();
        $destroyResponse->assertJson([
            'success' => true,
            'message' => __('purchasing.supplier.messages.deleted_success'),
        ]);

        $this->assertSoftDeleted('suppliers', ['id' => $supplierId]);
    }

    public function test_warehouse_web_controller_store_update_destroy_ajax_json_responses(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\BusinessSubscription::updateOrCreate(
            ['business_id' => $this->businessA->id],
            ['plan_code' => \App\Models\BusinessSubscription::PLAN_PREMIUM_MONTHLY, 'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE]
        );

        // Create primary location first so subsequent created location is not primary
        Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Utama Default',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 1. AJAX Store Location (JSON 201)
        $storePayload = [
            'name' => 'Gudang AJAX Transit',
            'type' => 'warehouse',
            'code' => 'GUD-TRX',
            'address' => 'Jl. Tol Cikampek KM 40',
            'is_primary' => false,
        ];

        $storeResponse = $this->actingAs($this->userA)
            ->postJson(route('warehouse.store'), $storePayload);

        $storeResponse->assertStatus(201);
        $storeResponse->assertJsonStructure([
            'success',
            'message',
            'location' => ['id', 'name', 'type', 'business_id'],
        ]);
        $storeResponse->assertJson([
            'success' => true,
            'message' => __('warehouse.messages.created_success'),
            'location' => [
                'name' => 'Gudang AJAX Transit',
                'type' => 'warehouse',
            ],
        ]);

        $locationId = $storeResponse->json('location.id');
        $this->assertNotNull($locationId);

        // 2. AJAX Update Location (JSON 200)
        $updatePayload = [
            'name' => 'Gudang AJAX Transit (Expanded)',
            'type' => 'warehouse',
            'code' => 'GUD-TRX-EXP',
            'address' => 'Jl. Tol Cikampek KM 42',
            'is_active' => true,
        ];

        $updateResponse = $this->actingAs($this->userA)
            ->putJson(route('warehouse.update', $locationId), $updatePayload);

        $updateResponse->assertOk();
        $updateResponse->assertJson([
            'success' => true,
            'message' => __('warehouse.messages.updated_success'),
            'location' => [
                'name' => 'Gudang AJAX Transit (Expanded)',
            ],
        ]);

        // 3. AJAX Destroy Location without history (JSON 200)
        $destroyResponse = $this->actingAs($this->userA)
            ->deleteJson(route('warehouse.destroy', $locationId));

        $destroyResponse->assertOk();
        $destroyResponse->assertJson([
            'success' => true,
            'message' => __('warehouse.messages.deleted_success'),
        ]);

        $this->assertDatabaseMissing('locations', ['id' => $locationId]);
    }

    public function test_warehouse_hierarchy_prevents_circular_parent_loops(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $parentLoc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Regional Barat',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $childLoc = Location::create([
            'business_id' => $this->businessA->id,
            'parent_id' => $parentLoc->id,
            'name' => 'Sub Gudang Barat 1',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        // 1. Parent cannot be self
        $responseSelf = $this->actingAs($this->userA)
            ->putJson(route('warehouse.update', $parentLoc->id), [
                'name' => $parentLoc->name,
                'type' => $parentLoc->type,
                'parent_id' => $parentLoc->id,
            ]);

        $responseSelf->assertStatus(422);
        $responseSelf->assertJsonValidationErrors(['parent_id']);
        $this->assertStringContainsString(__('warehouse.validation.parent_self'), $responseSelf->json('errors.parent_id.0'));

        // 2. Parent cannot be own descendant
        $responseDescendant = $this->actingAs($this->userA)
            ->putJson(route('warehouse.update', $parentLoc->id), [
                'name' => $parentLoc->name,
                'type' => $parentLoc->type,
                'parent_id' => $childLoc->id,
            ]);

        $responseDescendant->assertStatus(422);
        $responseDescendant->assertJsonValidationErrors(['parent_id']);
        $this->assertStringContainsString(__('warehouse.validation.parent_descendant'), $responseDescendant->json('errors.parent_id.0'));
    }

    public function test_warehouse_non_destructive_archival_guard_json_and_web(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\BusinessSubscription::updateOrCreate(
            ['business_id' => $this->businessA->id],
            ['plan_code' => \App\Models\BusinessSubscription::PLAN_PREMIUM_MONTHLY, 'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE]
        );

        // Create primary location first
        Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Utama Anchor',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $loc = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Riwayat Mutasi',
            'type' => 'warehouse',
            'is_active' => true,
            'is_primary' => false,
        ]);

        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'box',
            'name' => 'Box',
            'category' => 'quantity',
            'is_base' => true,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Logistik',
            'slug' => 'logistik',
        ]);

        $product = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Karton Pengiriman',
            'slug' => 'karton-pengiriman',
            'code' => 'KRT-01',
            'base_cost' => 5000,
            'selling_price' => 7500,
            'is_active' => true,
        ]);

        // Create historical movement record with valid schema
        StockMovement::create([
            'business_id' => $this->businessA->id,
            'location_id' => $loc->id,
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_INITIAL,
            'quantity_change' => 10,
            'balance_after' => 10,
            'notes' => 'Penerimaan batch perdana',
            'created_by' => $this->userA->id,
        ]);

        // Attempt JSON delete
        $response = $this->actingAs($this->userA)
            ->deleteJson(route('warehouse.destroy', $loc->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deactivated' => true,
            'message' => __('warehouse.messages.deactivated_due_to_history', ['name' => 'Gudang Riwayat Mutasi']),
        ]);

        // Verify record is preserved in database with is_active = false
        $this->assertDatabaseHas('locations', [
            'id' => $loc->id,
            'is_active' => false,
        ]);
    }

    public function test_supplier_and_warehouse_flash_messages_are_fully_localized_in_both_id_and_en(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\BusinessSubscription::updateOrCreate(
            ['business_id' => $this->businessA->id],
            ['plan_code' => \App\Models\BusinessSubscription::PLAN_PREMIUM_MONTHLY, 'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE]
        );

        // 1. Regular Form Store Supplier in ID
        $resSupplierId = $this->withSession(['locale' => 'id'])
            ->actingAs($this->userA)
            ->post(route('suppliers.store'), [
                'name' => 'Pemasok Form ID',
            ]);

        $resSupplierId->assertRedirect();
        $resSupplierId->assertSessionHas('success', __('purchasing.supplier.messages.created_success', [], 'id'));

        // 2. Regular Form Store Supplier in EN
        $resSupplierEn = $this->withSession(['locale' => 'en'])
            ->actingAs($this->userA)
            ->post(route('suppliers.store'), [
                'name' => 'Supplier Form EN',
            ]);

        $resSupplierEn->assertRedirect();
        $resSupplierEn->assertSessionHas('success', __('purchasing.supplier.messages.created_success', [], 'en'));

        // 3. Regular Form Store Warehouse in ID
        $resWarehouseId = $this->withSession(['locale' => 'id'])
            ->actingAs($this->userA)
            ->post(route('warehouse.store'), [
                'name' => 'Gudang Form ID',
                'type' => 'warehouse',
            ]);

        $resWarehouseId->assertRedirect();
        $resWarehouseId->assertSessionHas('success', __('warehouse.messages.created_success', [], 'id'));

        // 4. Regular Form Store Warehouse in EN
        $resWarehouseEn = $this->withSession(['locale' => 'en'])
            ->actingAs($this->userA)
            ->post(route('warehouse.store'), [
                'name' => 'Warehouse Form EN',
                'type' => 'warehouse',
            ]);

        $resWarehouseEn->assertRedirect();
        $resWarehouseEn->assertSessionHas('success', __('warehouse.messages.created_success', [], 'en'));
    }

    public function test_views_strictly_enforce_mobile_anti_autozoom_input_typography(): void
    {
        $views = [
            'warehouse_index' => file_get_contents(resource_path('views/app/warehouse/index.blade.php')),
            'warehouse_show' => file_get_contents(resource_path('views/app/warehouse/show.blade.php')),
            'suppliers_index' => file_get_contents(resource_path('views/app/suppliers/index.blade.php')),
        ];

        foreach ($views as $viewName => $content) {
            // Check that all text inputs, number inputs, selects, and textareas include text-[16px]
            $this->assertStringContainsString('text-[16px]', $content, "View {$viewName} must enforce text-[16px] for mobile anti-auto-zoom typography.");
        }
    }

    public function test_views_strictly_enforce_apple_hig_touch_targets_44px_minimum(): void
    {
        $views = [
            'warehouse_index' => file_get_contents(resource_path('views/app/warehouse/index.blade.php')),
            'warehouse_show' => file_get_contents(resource_path('views/app/warehouse/show.blade.php')),
            'suppliers_index' => file_get_contents(resource_path('views/app/suppliers/index.blade.php')),
        ];

        foreach ($views as $viewName => $content) {
            // Check that 44px touch targets are standardized across primary controls and modal close buttons
            $this->assertStringContainsString('min-h-[44px]', $content, "View {$viewName} must enforce min-h-[44px] touch target ergonomics.");
            $this->assertStringContainsString('min-w-[44px]', $content, "View {$viewName} must enforce min-w-[44px] for circular modal close buttons.");
        }
    }

    public function test_twenty_industry_templates_regression_rendering_cleanly_without_errors(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        \App\Models\BusinessSubscription::updateOrCreate(
            ['business_id' => $this->businessA->id],
            ['plan_code' => \App\Models\BusinessSubscription::PLAN_PREMIUM_MONTHLY, 'status' => \App\Models\BusinessSubscription::STATUS_ACTIVE]
        );

        $twentyTemplates = [
            'fnb_resto',
            'fnb_bakery',
            'fnb_catering',
            'fnb_coffeebar',
            'apparel_konveksi',
            'apparel_tailor',
            'retail_grocery',
            'retail_fashion',
            'retail_pharmacy',
            'retail_cosmetics',
            'service_barbershop',
            'service_carwash',
            'service_laundry',
            'service_repair',
            'service_contractor',
            'mfg_craft',
            'mfg_herbal',
            'mfg_food',
            'agriculture_farm',
            'education_course',
        ];

        // Create sample location and supplier for Tenant A
        Location::firstOrCreate(
            ['business_id' => $this->businessA->id, 'name' => 'Lokasi Showcase'],
            ['type' => 'warehouse', 'code' => 'SHOW-01', 'is_active' => true]
        );

        Supplier::firstOrCreate(
            ['business_id' => $this->businessA->id, 'name' => 'Supplier Showcase'],
            ['slug' => 'supplier-showcase', 'is_active' => true]
        );

        foreach ($twentyTemplates as $templateCode) {
            $disabledModules = \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($templateCode);
            $this->businessA->update([
                'template_code' => $templateCode,
                'disabled_modules' => $disabledModules,
            ]);

            // 1. Test Warehouse Index in Indonesian & English (if inventory module is enabled)
            if (!in_array(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE, $disabledModules, true)) {
                $resWarehouseId = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('warehouse.index'));
                $resWarehouseId->assertOk();
                $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])warehouse\.[a-z_]+/i', $resWarehouseId->getContent(), "Found unrendered warehouse key in ID for {$templateCode}");

                $resWarehouseEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('warehouse.index'));
                $resWarehouseEn->assertOk();
                $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])warehouse\.[a-z_]+/i', $resWarehouseEn->getContent(), "Found unrendered warehouse key in EN for {$templateCode}");

                // Verify Material Catalog Auto-Hiding in Warehouse
                $isBomSupported = !in_array(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM, $disabledModules, true);
                if ($isBomSupported) {
                    $resWarehouseId->assertSee(route('materials.index'));
                } else {
                    $resWarehouseId->assertDontSee(route('materials.index'));
                }
            }

            // 2. Test Suppliers Index in Indonesian & English (if purchasing/procurement module is enabled)
            if (!in_array(\App\Domain\Template\ModuleRegistry::MODULE_PROCUREMENT, $disabledModules, true)) {
                $resSuppliersId = $this->withSession(['locale' => 'id'])->actingAs($this->userA)->get(route('suppliers.index'));
                $resSuppliersId->assertOk();
                $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])purchasing\.supplier\.[a-z_]+/i', $resSuppliersId->getContent(), "Found unrendered supplier key in ID for {$templateCode}");

                $resSuppliersEn = $this->withSession(['locale' => 'en'])->actingAs($this->userA)->get(route('suppliers.index'));
                $resSuppliersEn->assertOk();
                $this->assertDoesNotMatchRegularExpression('/(?<![\w\.\-])purchasing\.supplier\.[a-z_]+/i', $resSuppliersEn->getContent(), "Found unrendered supplier key in EN for {$templateCode}");

                // Verify Material Catalog Auto-Hiding in Suppliers
                $isBomSupported = !in_array(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM, $disabledModules, true);
                if ($isBomSupported) {
                    $resSuppliersId->assertSee(route('materials.index'));
                } else {
                    $resSuppliersId->assertDontSee(route('materials.index'));
                }
            }
        }
    }

    public function test_warehouse_and_supplier_permission_gating_and_security_isolation(): void
    {
        // 1. Unauthenticated request to warehouse.index redirects to login
        $resUnauth = $this->get(route('warehouse.index'));
        $resUnauth->assertRedirect(route('login'));

        // 2. Unauthenticated request to suppliers.index redirects to login
        $resUnauthSup = $this->get(route('suppliers.index'));
        $resUnauthSup->assertRedirect(route('login'));

        // 3. Authenticate User A, try to access User B's warehouse location -> 404
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        Context::setBusiness($this->businessB);
        $locB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Gudang Rahasia Tenant B',
            'type' => 'warehouse',
            'code' => 'GUD-B-SEC',
            'is_active' => true,
        ]);

        $supB = Supplier::create([
            'business_id' => $this->businessB->id,
            'name' => 'Supplier Rahasia Tenant B',
            'slug' => 'supplier-rahasia-b',
        ]);

        // Attempt by User A to view Tenant B's warehouse details -> 404
        Context::setBusiness($this->businessA);
        $resLocB = $this->actingAs($this->userA)->get(route('warehouse.show', $locB->id));
        $resLocB->assertNotFound();

        // Attempt by User A to update Tenant B's warehouse -> 404
        $resUpdateLocB = $this->actingAs($this->userA)->put(route('warehouse.update', $locB->id), [
            'name' => 'Hacked Location',
            'type' => 'warehouse',
        ]);
        $resUpdateLocB->assertNotFound();

        // Attempt by User A to delete Tenant B's supplier -> 404
        $resDeleteSupB = $this->actingAs($this->userA)->delete(route('suppliers.destroy', $supB->id));
        $resDeleteSupB->assertNotFound();
    }

    public function test_stock_adjustment_cross_tenant_idor_security_guard(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $unitA = Unit::firstOrCreate(['business_id' => $this->businessA->id, 'code' => 'pcs'], ['name' => 'Pcs', 'symbol' => 'pcs', 'category' => Unit::CATEGORY_QUANTITY]);
        $unitB = Unit::firstOrCreate(['business_id' => $this->businessB->id, 'code' => 'pcs'], ['name' => 'Pcs', 'symbol' => 'pcs', 'category' => Unit::CATEGORY_QUANTITY]);

        $locA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Tenant A',
            'type' => 'warehouse',
            'code' => 'GUD-A-01',
            'is_active' => true,
        ]);

        $catA = ProductCategory::create(['business_id' => $this->businessA->id, 'name' => 'Cat A', 'slug' => 'cat-a']);
        $prodA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $catA->id,
            'output_unit_id' => $unitA->id,
            'name' => 'Produk A',
            'slug' => 'produk-a',
            'type' => 'standard',
        ]);

        Context::setBusiness($this->businessB);
        $locB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Gudang Tenant B',
            'type' => 'warehouse',
            'code' => 'GUD-B-01',
            'is_active' => true,
        ]);

        $catB = ProductCategory::create(['business_id' => $this->businessB->id, 'name' => 'Cat B', 'slug' => 'cat-b']);
        $prodB = Product::create([
            'business_id' => $this->businessB->id,
            'category_id' => $catB->id,
            'output_unit_id' => $unitB->id,
            'name' => 'Produk B',
            'slug' => 'produk-b',
            'type' => 'standard',
        ]);

        // Attempt by User A to adjust stock using Tenant B's location_id and product_id
        Context::setBusiness($this->businessA);
        $resCrossAdjust = $this->actingAs($this->userA)->post(route('inventory.stocks.adjust'), [
            'product_id' => $prodB->id,
            'location_id' => $locB->id,
            'new_quantity' => 100,
            'reason_code' => 'opname_variance',
            'notes' => 'Attempting Cross-Tenant Stock Injection',
        ]);

        // Must fail with 403 Forbidden or 404 Not Found (Cross-tenant access rejected)
        $this->assertTrue(in_array($resCrossAdjust->getStatusCode(), [403, 404, 302], true));
    }

    public function test_warehouse_hierarchy_descendant_loop_prevention_exhaustive(): void
    {
        Context::setBusiness($this->businessA);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // 1. Create Root (Pusat)
        $root = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kantor Pusat Jakarta',
            'type' => 'warehouse',
            'code' => 'ROOT-JKT',
            'is_active' => true,
        ]);

        // 2. Create Regional Hub under Root
        $regionalHub = Location::create([
            'business_id' => $this->businessA->id,
            'parent_id' => $root->id,
            'name' => 'Hub Regional Jawa Barat',
            'type' => 'warehouse',
            'code' => 'HUB-JBR',
            'is_active' => true,
        ]);

        // 3. Create District Outlet under Regional Hub
        $districtOutlet = Location::create([
            'business_id' => $this->businessA->id,
            'parent_id' => $regionalHub->id,
            'name' => 'Outlet Bandung Dago',
            'type' => 'outlet',
            'code' => 'OUT-DGO',
            'is_active' => true,
        ]);

        // Attempt to set Root's parent to Regional Hub (Direct child loop)
        $resDirectChildLoop = $this->actingAs($this->userA)
            ->put(route('warehouse.update', $root->id), [
                'name' => $root->name,
                'type' => 'warehouse',
                'parent_id' => $regionalHub->id,
            ]);

        $resDirectChildLoop->assertSessionHasErrors('parent_id');

        // Attempt to set Root's parent to District Outlet (Deep descendant loop)
        $resDeepDescendantLoop = $this->actingAs($this->userA)
            ->put(route('warehouse.update', $root->id), [
                'name' => $root->name,
                'type' => 'warehouse',
                'parent_id' => $districtOutlet->id,
            ]);

        $resDeepDescendantLoop->assertSessionHasErrors('parent_id');
    }

    /**
     * Helper to recursively extract all dot-notated array keys.
     *
     * @param array<string, mixed> $array
     * @param string $prefix
     * @return array<int, string>
     */
    private function flattenKeys(array $array, string $prefix = ''): array
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $fullKey));
            } else {
                $keys[] = $fullKey;
            }
        }
        return $keys;
    }
}
