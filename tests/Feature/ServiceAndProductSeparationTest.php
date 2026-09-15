<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosOrderService;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ServiceAndProductSeparationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private PosShift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Boomer',
            'email' => 'owner.boomer@example.com',
            'phone' => '6281299990001',
            'password' => bcrypt('secret123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Bengkel & Cuci Mobil Mantap',
            'currency' => 'IDR',
            'currency_precision' => 0,
            'rounding_strategy' => Business::ROUNDING_ROUND_100,
            'industry_category' => 'services',
            'template_code' => 'automotive_workshop',
        ]);

        $ownerRole = Role::where('business_id', $this->business->id)->where('name', 'Owner')->first()
            ?? Role::where('name', 'Owner')->first();

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Pusat',
            'code' => 'PST',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir Utama',
            'is_active' => true,
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'pos_register_id' => $register->id,
            'opening_cash' => 100000,
            'opened_at' => now(),
            'status' => PosShift::STATUS_OPEN,
        ]);

        Context::setBusiness($this->business);
    }

    public function test_product_defaults_to_goods_type_and_helpers_work(): void
    {
        $unit = Unit::first();

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Oli Mesin 1L',
            'selling_price' => 75000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->assertEquals(Product::TYPE_GOODS, $product->type);
        $this->assertTrue($product->isGoods());
        $this->assertFalse($product->isService());
    }

    public function test_service_type_and_helpers_work(): void
    {
        $unit = Unit::first();

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Ganti Oli',
            'selling_price' => 25000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->assertEquals(Product::TYPE_SERVICE, $service->type);
        $this->assertTrue($service->isService());
        $this->assertFalse($service->isGoods());
        $this->assertNull($service->calculateEffectiveStock($this->location->id));
        $this->assertEquals([], $service->getMaterialDeductions(1));
    }

    public function test_product_scopes_separate_goods_and_services(): void
    {
        $unit = Unit::first();

        // 2 Goods
        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kampas Rem Depan',
            'selling_price' => 90000,
            'output_unit_id' => $unit->id,
        ]);
        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Busi Iridium',
            'selling_price' => 45000,
            'output_unit_id' => $unit->id,
        ]);

        // 2 Services
        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Pasang Kampas Rem',
            'selling_price' => 30000,
            'output_unit_id' => $unit->id,
        ]);
        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Servis Ringan Tune Up',
            'selling_price' => 120000,
            'output_unit_id' => $unit->id,
        ]);

        $this->assertEquals(2, Product::goods()->where('business_id', $this->business->id)->count());
        $this->assertEquals(2, Product::services()->where('business_id', $this->business->id)->count());
    }

    public function test_services_web_controller_store_creates_service_with_boomer_friendly_payload(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->post(route('services.store'), [
                'name' => 'Cuci Mobil Hidrolik Salju',
                'selling_price' => 60000,
                'new_category_name' => 'Jasa Cuci Kendaraan',
                'description' => 'Cuci bersih luar dalam menggunakan busa salju',
            ]);

        $response->assertRedirect(route('services.index'));
        $response->assertSessionHas('success');

        $created = Product::services()
            ->where('business_id', $this->business->id)
            ->where('name', 'Cuci Mobil Hidrolik Salju')
            ->first();

        $this->assertNotNull($created);
        $this->assertEquals(60000, (int) $created->selling_price);
        $this->assertEquals(Product::TYPE_SERVICE, $created->type);
        $this->assertNotNull($created->category);
        $this->assertEquals('Jasa Cuci Kendaraan', $created->category->name);
    }

    public function test_services_index_and_products_index_are_isolated(): void
    {
        $unit = Unit::first();

        // 1 Barang fisik
        $goods = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Filter Udara Sakura',
            'selling_price' => 80000,
            'output_unit_id' => $unit->id,
        ]);

        // 1 Jasa
        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Spooring Balancing',
            'selling_price' => 150000,
            'output_unit_id' => $unit->id,
        ]);

        // Access /services: should contain Jasa, should NOT contain Barang Fisik
        $servicesResponse = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('services.index'));

        $servicesResponse->assertStatus(200);
        $servicesResponse->assertSee('Jasa Spooring Balancing');
        $servicesResponse->assertDontSee('Filter Udara Sakura');

        // Access /products: should contain Barang Fisik, should NOT contain Jasa
        $productsResponse = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('products.index'));

        $productsResponse->assertStatus(200);
        $productsResponse->assertSee('Filter Udara Sakura');
        $productsResponse->assertDontSee('Jasa Spooring Balancing');
    }

    public function test_service_stock_deduction_and_availability_bypassed_in_pos(): void
    {
        $unit = Unit::first();

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Ongkos Pasang Sparepart',
            'selling_price' => 50000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $orderService = new PosOrderService();

        // Place a POS order containing ONLY the service item (Zero stock, no materials)
        $order = $orderService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $service->id,
                    'product_name' => $service->name,
                    'unit_price' => 50000,
                    'quantity' => 2,
                    'discount_amount' => 0,
                    'notes' => 'Ongkos pasang',
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 100000,
                    'tendered_amount' => 100000,
                    'change_amount' => 0,
                ],
            ],
            [
                'location_id' => $this->location->id,
                'order_type' => 'takeaway',
            ],
            $this->shift
        );

        $this->assertInstanceOf(PosOrder::class, $order);
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->status);
        $this->assertEquals(100000, (int) $order->total_amount);
        $this->assertCount(1, $order->items);
        $this->assertEquals($service->id, $order->items->first()->product_id);
    }

    public function test_sales_order_and_quotation_create_views_receive_separated_goods_and_services(): void
    {
        $unit = Unit::first();

        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Barang Fisik Test',
            'selling_price' => 50000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Layanan Test',
            'selling_price' => 75000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // 1. Sales Order create
        $soResponse = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('sales.orders.create'));
        $soResponse->assertOk();
        $soResponse->assertViewHas('goodsProducts');
        $soResponse->assertViewHas('serviceProducts');
        $this->assertCount(1, $soResponse->viewData('goodsProducts'));
        $this->assertCount(1, $soResponse->viewData('serviceProducts'));

        // 2. Quotation create
        $quoResponse = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('sales.quotations.create'));
        $quoResponse->assertOk();
        $quoResponse->assertViewHas('goodsProducts');
        $quoResponse->assertViewHas('serviceProducts');
        $this->assertCount(1, $quoResponse->viewData('goodsProducts'));
        $this->assertCount(1, $quoResponse->viewData('serviceProducts'));
    }

    public function test_pos_reports_calculates_goods_and_services_revenue_breakdown(): void
    {
        $unit = Unit::first();

        $goods = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Produk Fisik A',
            'selling_price' => 50000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Berikan stok awal untuk produk fisik agar checkout POS sukses
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $goods->id,
            'quantity' => 10,
        ]);

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Layanan B',
            'selling_price' => 30000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $orderService = new PosOrderService();
        $orderService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $goods->id,
                    'product_name' => $goods->name,
                    'unit_price' => 50000,
                    'quantity' => 2,
                    'discount_amount' => 0,
                ],
                [
                    'product_id' => $service->id,
                    'product_name' => $service->name,
                    'unit_price' => 30000,
                    'quantity' => 1,
                    'discount_amount' => 0,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 130000,
                    'tendered_amount' => 130000,
                    'change_amount' => 0,
                ],
            ],
            [
                'location_id' => $this->location->id,
            ],
            $this->shift
        );

        $response = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('pos.reports.index'));

        $response->assertOk();
        $response->assertViewHas('goodsRevenue', 100000.0);
        $response->assertViewHas('goodsQty', 2.0);
        $response->assertViewHas('servicesRevenue', 30000.0);
        $response->assertViewHas('servicesQty', 1.0);

        $topProducts = $response->viewData('topProducts');
        $this->assertNotEmpty($topProducts);
        $serviceItem = $topProducts->firstWhere('product_name', 'Jasa Layanan B');
        $this->assertNotNull($serviceItem);
        $this->assertEquals('service', $serviceItem->item_type);
    }

    public function test_public_landing_page_auto_syncs_services_from_database(): void
    {
        $unit = Unit::first();

        // Create active service
        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Poles Mobil Nano Ceramic',
            'selling_price' => 500000,
            'description' => 'Paket proteksi cat kilap 3 tahun.',
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Create landing page record
        \App\Models\BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Bengkel Mantap Nomor 1',
            'is_published' => true,
            'show_pos_products' => true,
            'section_visibility' => ['services' => true, 'products' => true],
        ]);

        $response = $this->get(route('public.business.landing', Str::slug($this->business->name)));
        $response->assertOk();
        $response->assertViewHas('services');

        $servicesCollection = $response->viewData('services');
        $found = $servicesCollection->firstWhere('title', 'Poles Mobil Nano Ceramic');
        $this->assertNotNull($found);
        $this->assertStringContainsString('Poles Mobil Nano Ceramic', $response->getContent());
    }

    public function test_landing_page_edit_view_receives_service_products(): void
    {
        $unit = Unit::first();

        Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Cuci Karpet',
            'selling_price' => 35000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'current_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get(route('landing-page.edit'));
        $response->assertOk();
        $response->assertViewHas('serviceProducts');
        $this->assertCount(1, $response->viewData('serviceProducts'));
        $this->assertEquals('Jasa Cuci Karpet', $response->viewData('serviceProducts')->first()->name);
    }

    public function test_inventory_stocks_opname_and_transfers_only_contain_goods(): void
    {
        $unit = Unit::first();

        $goods = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Baut Roda Baja',
            'selling_price' => 15000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Ganti Oli Mesin',
            'selling_price' => 45000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $session = [
            'current_business_id' => $this->business->id,
            'auth_wa_otp_verified_user_id' => $this->user->id,
        ];

        // 1. Check Opname view
        $opnameResponse = $this->actingAs($this->user)
            ->withSession($session)
            ->get(route('inventory.opnames.index'));
        $opnameResponse->assertOk();
        $opnameProducts = $opnameResponse->viewData('products');
        $this->assertTrue($opnameProducts->contains('id', $goods->id));
        $this->assertFalse($opnameProducts->contains('id', $service->id));

        // 2. Check Transfers view
        $transferResponse = $this->actingAs($this->user)
            ->withSession($session)
            ->get(route('inventory.transfers.index'));
        $transferResponse->assertOk();
        $transferProducts = $transferResponse->viewData('products');
        $this->assertTrue($transferProducts->contains('id', $goods->id));
        $this->assertFalse($transferProducts->contains('id', $service->id));
    }

    public function test_public_qr_order_menu_marks_services_available_without_stock(): void
    {
        $unit = Unit::first();

        // Location & Table
        $location = \App\Models\Location::create([
            'business_id' => $this->business->id,
            'name' => 'Bengkel Utama',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $table = \App\Models\PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $location->id,
            'table_number' => 'T01',
            'name' => 'Meja 01',
            'qr_token' => 'qr_token_test_123',
            'is_active' => true,
        ]);

        // Goods with 0 stock
        $goods = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kampas Rem Depan',
            'selling_price' => 120000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Service with 0 stock
        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Tune Up Mesin',
            'selling_price' => 200000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.qr.menu', ['qrToken' => 'qr_token_test_123']));
        $response->assertOk();
        $response->assertViewHas('products');

        $products = collect($response->viewData('products'));
        $goodsItem = $products->firstWhere('id', $goods->id);
        $serviceItem = $products->firstWhere('id', $service->id);

        $this->assertNotNull($goodsItem);
        $this->assertNotNull($serviceItem);

        // Goods with no stock must be unavailable
        $this->assertFalse($goodsItem['is_available']);

        // Service must ALWAYS be available and have type = service
        $this->assertTrue($serviceItem['is_available']);
        $this->assertTrue($serviceItem['is_service']);
        $this->assertEquals(Product::TYPE_SERVICE, $serviceItem['type']);
        $this->assertNull($serviceItem['stock']);
    }

    public function test_business_template_service_seeds_services_with_correct_type(): void
    {
        // Template
        $template = \App\Models\BusinessTypeTemplate::where('industry_category', 'services')->first()
            ?? \App\Models\BusinessTypeTemplate::first();

        if (! $template) {
            $template = \App\Models\BusinessTypeTemplate::create([
                'name' => 'Barbershop & Salon',
                'code' => 'BARBER',
                'industry_category' => 'services',
                'default_cost_components' => [
                    ['category' => 'Direct Labor', 'name' => 'Upah Kapster Potong', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ]);
        }

        $service = new \App\Domain\Template\BusinessTemplateService();
        $service->apply($this->business, $template);

        $seededServices = Product::where('business_id', $this->business->id)
            ->where('type', Product::TYPE_SERVICE)
            ->get();

        $this->assertGreaterThanOrEqual(1, $seededServices->count());
        $firstService = $seededServices->first();
        $this->assertTrue($firstService->isService());
        $this->assertFalse($firstService->isGoods());
    }

    public function test_financial_report_stock_valuation_excludes_services(): void
    {
        $unit = Unit::first();

        $goods = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Oli Mesin Shell Helix',
            'selling_price' => 90000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Flushing Radiator',
            'selling_price' => 60000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $reportService = new \App\Domain\Report\FinancialReportService();
        $valuation = $reportService->getStockValuationAndTurnover($this->business);

        $itemIds = collect($valuation['all_items'])->pluck('product_id')->all();
        $this->assertContains($goods->id, $itemIds);
        $this->assertNotContains($service->id, $itemIds);
    }

    public function test_api_pos_terminal_returns_service_metadata_and_track_inventory(): void
    {
        $unit = Unit::first();

        $service = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Cuci Steam Salju',
            'selling_price' => 30000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $token = $this->user->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Business-Id' => $this->business->id,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/pos/terminal/bootstrap');

        $response->assertOk();
        $products = collect($response->json('products'));
        $serviceApi = $products->firstWhere('id', $service->id);

        $this->assertNotNull($serviceApi);
        $this->assertEquals(Product::TYPE_SERVICE, $serviceApi['type']);
        $this->assertTrue($serviceApi['is_service']);
        $this->assertFalse($serviceApi['track_inventory']);
        $this->assertNull($serviceApi['stock']);
    }
}
