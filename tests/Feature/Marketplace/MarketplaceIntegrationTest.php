<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\MarketplaceManagerService;
use App\Domain\Marketplace\MarketplaceSyncService;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Business $businessA;
    protected Business $businessB;
    protected User $userA;
    protected User $userB;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->userA = User::create([
            'name' => 'Owner Toko A',
            'email' => 'owner_a@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->businessA = Business::create([
            'name' => 'Toko A Sejahtera',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $this->userB = User::create([
            'name' => 'Owner Toko B',
            'email' => 'owner_b@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->businessB = Business::create([
            'name' => 'Toko B Makmur',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->userB->update(['active_business_id' => $this->businessB->id]);

        Context::setBusiness($this->businessA);

        $this->unit = Unit::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Pieces',
            'code'        => 'PCS',
            'symbol'      => 'pcs',
            'category'    => 'quantity',
        ]);
        $this->locationA = \App\Models\Location::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Gudang Utama',
            'type'        => 'warehouse',
            'is_primary'  => true,
            'is_active'   => true,
        ]);
    }

    public function test_it_generates_and_validates_encrypted_multi_tenant_oauth_state(): void
    {
        $manager = app(MarketplaceManagerService::class);

        $state = $manager->generateOAuthState($this->businessA, $this->userA, 'shopee');
        $this->assertNotEmpty($state);

        $decoded = $manager->validateOAuthState($state);
        $this->assertNotNull($decoded);
        $this->assertEquals($this->businessA->id, $decoded['business_id']);
        $this->assertEquals($this->userA->id, $decoded['user_id']);
        $this->assertEquals('shopee', $decoded['channel']);

        // Invalid / Tampered state returns null
        $invalidState = 'tampered_state_string_invalid';
        $this->assertNull($manager->validateOAuthState($invalidState));
    }

    public function test_it_enforces_strict_multi_tenant_isolation_between_businesses(): void
    {
        // Create account for Business A
        MarketplaceAccount::create([
            'business_id'   => $this->businessA->id,
            'channel'       => 'shopee',
            'shop_id'       => 'SHOP-A-123',
            'shop_name'     => 'Shopee Store A',
            'access_token'  => 'tok_a_secret',
            'refresh_token' => 'ref_a_secret',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        // Create account for Business B
        MarketplaceAccount::create([
            'business_id'   => $this->businessB->id,
            'channel'       => 'shopee',
            'shop_id'       => 'SHOP-B-456',
            'shop_name'     => 'Shopee Store B',
            'access_token'  => 'tok_b_secret',
            'refresh_token' => 'ref_b_secret',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        // User A visits index -> only sees Shop A
        $responseA = $this->actingAs($this->userA)->get(route('marketplace-hub.index'));
        $responseA->assertOk();
        $responseA->assertSee('SHOP-A-123');
        $responseA->assertDontSee('SHOP-B-456');

        // User B visits index -> only sees Shop B
        Context::flush();
        Context::setBusiness($this->businessB);
        $responseB = $this->actingAs($this->userB)->get(route('marketplace-hub.index'));
        $responseB->assertOk();
        $responseB->assertSee('SHOP-B-456');
        $responseB->assertDontSee('SHOP-A-123');
    }

    public function test_it_calculates_effective_per_channel_prices_correctly(): void
    {
        $product = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Baju Kemeja Katun',
            'slug'           => 'baju-kemeja-katun',
            'code'           => 'KMJ-001',
            'base_cost'      => 60000,
            'selling_price'  => 100000,
            'output_unit_id' => $this->unit->id,
            'stock'          => 50,
            'is_active'      => true,
        ]);

        // Mapping 1: Shopee with Auto Sync + 1.10 Multiplier (10% higher for admin fee)
        $shopeeMapping = MarketplaceProductMapping::create([
            'business_id'         => $this->businessA->id,
            'product_id'          => $product->id,
            'channel'             => 'shopee',
            'sync_price_auto'     => true,
            'price_multiplier'    => 1.10,
            'sync_stock_auto'     => true,
            'stock_buffer'        => 5,
            'is_active'           => true,
        ]);

        // Mapping 2: TikTok Shop with Manual Fixed Price (Rp 125.000)
        $tiktokMapping = MarketplaceProductMapping::create([
            'business_id'         => $this->businessA->id,
            'product_id'          => $product->id,
            'channel'             => 'tiktok_shop',
            'sync_price_auto'     => false,
            'channel_price'       => 125000,
            'sync_stock_auto'     => false,
            'custom_stock'        => 20,
            'is_active'           => true,
        ]);

        // Test Effective Price calculations
        $this->assertEquals(110000, $shopeeMapping->getEffectivePrice());
        $this->assertEquals(125000, $tiktokMapping->getEffectivePrice());

        // Test Model Product helper methods
        $this->assertEquals(110000, $product->getMarketplacePrice('shopee'));
        $this->assertEquals(125000, $product->getMarketplacePrice('tiktok_shop'));
        $this->assertEquals(100000, $product->getMarketplacePrice('tokopedia')); // Default fallback to base price
    }

    public function test_it_calculates_effective_available_stock_with_buffer_correctly(): void
    {
        $syncService = app(MarketplaceSyncService::class);

        $product = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Kopi Arabika 250g',
            'slug'           => 'kopi-arabika-250g',
            'code'           => 'KOP-250',
            'base_cost'      => 50000,
            'selling_price'  => 85000,
            'output_unit_id' => $this->unit->id,
            'is_active'      => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'product_id'  => $product->id,
            'quantity'    => 20,
        ]);

        // Auto Sync with Buffer 4 -> Effective stock should be 16
        $mappingAuto = MarketplaceProductMapping::create([
            'business_id'         => $this->businessA->id,
            'product_id'          => $product->id,
            'channel'             => 'shopee',
            'sync_price_auto'     => true,
            'price_multiplier'    => 1.0,
            'sync_stock_auto'     => true,
            'stock_buffer'        => 4,
            'is_active'           => true,
        ]);

        // Manual Custom Stock 10
        $mappingManual = MarketplaceProductMapping::create([
            'business_id'         => $this->businessA->id,
            'product_id'          => $product->id,
            'channel'             => 'tokopedia',
            'sync_price_auto'     => true,
            'price_multiplier'    => 1.0,
            'sync_stock_auto'     => false,
            'custom_stock'        => 10,
            'is_active'           => true,
        ]);

        $this->assertEquals(16, $syncService->resolveEffectiveStock($product, $mappingAuto));
        $this->assertEquals(10, $syncService->resolveEffectiveStock($product, $mappingManual));
    }

    public function test_it_updates_product_mapping_via_web_post(): void
    {
        $product = Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Sepatu Sneakers',
            'slug'           => 'sepatu-sneakers',
            'code'           => 'SPT-01',
            'base_cost'      => 150000,
            'selling_price'  => 250000,
            'output_unit_id' => $this->unit->id,
            'stock'          => 15,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->userA)->post(route('marketplace-hub.products.map'), [
            'product_id'          => $product->id,
            'channel'             => 'shopee',
            'marketplace_item_id' => 'SHP-ITEM-999',
            'marketplace_sku'     => 'SHP-SKU-999',
            'sync_price_auto'     => '1',
            'price_multiplier'    => '1.08',
            'sync_stock_auto'     => '1',
            'stock_buffer'        => '2',
            'is_active'           => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('marketplace_product_mappings', [
            'business_id'         => $this->businessA->id,
            'product_id'          => $product->id,
            'channel'             => 'shopee',
            'external_product_id' => 'SHP-ITEM-999',
            'price_multiplier'    => 1.08,
            'stock_buffer'        => 2,
            'is_active'           => 1,
        ]);
    }

    public function test_it_ingests_public_inbound_webhooks_safely(): void
    {
        $partnerKey = 'test_shopee_secret_key_123';
        \App\Models\SystemSetting::set('shopee_partner_key', $partnerKey);

        $payload = [
            'shop_id'   => '123456',
            'code'      => 3,
            'timestamp' => time(),
            'data'      => [
                'ordersn' => '240925SHP9999',
                'status'  => 'PAID',
            ],
        ];

        $rawBody = (string) json_encode($payload);
        $url = url('/webhooks/marketplace/shopee');
        $validSign = hash_hmac('sha256', $url . '|' . $rawBody, $partnerKey);

        $response = $this->call(
            'POST',
            '/webhooks/marketplace/shopee',
            [],
            [],
            [],
            [
                'HTTP_X_SHOPEE_SIGN' => $validSign,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_it_rejects_webhook_with_invalid_or_missing_signature(): void
    {
        $partnerKey = 'test_shopee_secret_key_123';
        \App\Models\SystemSetting::set('shopee_partner_key', $partnerKey);

        $payload = [
            'shop_id'   => '123456',
            'code'      => 3,
            'timestamp' => time(),
            'data'      => [
                'ordersn' => '240925SHP9999',
                'status'  => 'PAID',
            ],
        ];

        // 1. Missing signature header -> 401
        $responseMissing = $this->postJson('/webhooks/marketplace/shopee', $payload);
        $responseMissing->assertStatus(401);
        $responseMissing->assertJson(['success' => false, 'error' => 'Invalid signature']);

        // 2. Forged signature header -> 401
        $responseForged = $this->postJson('/webhooks/marketplace/shopee', $payload, [
            'X-Shopee-Sign' => 'forged_fake_signature_hash',
        ]);
        $responseForged->assertStatus(401);
        $responseForged->assertJson(['success' => false, 'error' => 'Invalid signature']);
    }

    public function test_it_enforces_marketplace_rbac_permissions(): void
    {
        // Create a staff user without marketplace permissions
        $staffUser = User::create([
            'name'              => 'Staff Kasir Toko A',
            'email'             => 'staff_kasir_a@cooca.id',
            'password'          => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->businessA->users()->attach($staffUser->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'cashier',
            'is_active' => true,
        ]);
        $staffUser->update(['active_business_id' => $this->businessA->id]);

        // 1. Staff without marketplace.view is redirected to portal (web) or forbidden (JSON)
        Context::flush();
        Context::setBusiness($this->businessA);
        $response = $this->actingAs($staffUser)->get(route('marketplace-hub.index'));
        $response->assertRedirect(route('portal'));

        $jsonForbidden = $this->actingAs($staffUser)->getJson(route('marketplace-hub.index'));
        $jsonForbidden->assertStatus(403);

        // 2. Assign custom role with marketplace.view only
        $viewOnlyRole = \App\Models\Role::create([
            'business_id' => $this->businessA->id,
            'name'        => 'Marketplace Viewer',
            'slug'        => 'marketplace-viewer',
        ]);
        $viewPerm = \App\Models\Permission::firstOrCreate(
            ['slug' => 'marketplace.view'],
            ['name' => 'Lihat Marketplace Hub', 'category' => 'marketplace']
        );
        $viewOnlyRole->permissions()->attach($viewPerm->id);

        $membership = \App\Models\BusinessMembership::where('business_id', $this->businessA->id)
            ->where('user_id', $staffUser->id)
            ->first();
        $membership->update([
            'role'    => 'custom',
            'role_id' => $viewOnlyRole->id,
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        // Can view index
        $viewResponse = $this->actingAs($staffUser)->get(route('marketplace-hub.index'));
        $viewResponse->assertOk();

        // But cannot mutate / map product without marketplace.manage (web redirects, JSON 403)
        $mutateResponse = $this->actingAs($staffUser)->post(route('marketplace-hub.products.map'), [
            'product_id' => 'dummy',
        ]);
        $mutateResponse->assertRedirect(route('portal'));

        $mutateJsonResponse = $this->actingAs($staffUser)->postJson(route('marketplace-hub.products.map'), [
            'product_id' => 'dummy',
        ]);
        $mutateJsonResponse->assertStatus(403);
    }

    public function test_it_renders_phase_3_bento_apple_hig_ui_and_eliminates_native_confirm(): void
    {
        Context::flush();
        Context::setBusiness($this->businessA);

        // 1. Products Page (Bento XXL Modal, AppAlert confirmSubmit, Financial Calculator)
        $productsResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.products'));
        $productsResponse->assertOk();
        $productsContent = $productsResponse->getContent();

        $this->assertStringContainsString('AppAlert.confirmSubmit', $productsContent);
        $this->assertStringNotContainsString('confirm(', $productsContent);
        $this->assertStringContainsString('max-w-5xl xl:max-w-6xl', $productsContent);
        $this->assertStringContainsString('Kalkulasi Margin Saluran (Live)', $productsContent);
        $this->assertStringContainsString('computedEffectivePrice', $productsContent);
        $this->assertStringContainsString('Menyimpan Pengaturan...', $productsContent);

        // 2. Index Page (Modern Bento Disconnect Modal with Operational Impact Breakdown)
        $indexResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.index'));
        $indexResponse->assertOk();
        $indexContent = $indexResponse->getContent();

        $this->assertStringContainsString('Dampak Operasional Pemutusan:', $indexContent);
        $this->assertStringContainsString('max-w-lg rounded-[28px]', $indexContent);
        $this->assertStringContainsString('Memproses...', $indexContent);

        // 3. Logs Page (Bento XXL max-w-4xl Modal with Credential Masking)
        $logsResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.logs'));
        $logsResponse->assertOk();
        $logsContent = $logsResponse->getContent();

        $this->assertStringContainsString('max-w-4xl', $logsContent);
        $this->assertStringContainsString('maskSensitiveData', $logsContent);
        $this->assertStringContainsString('Zero Plaintext Masked', $logsContent);

        // 4. Orders Page (Modern Pull Modal with Submitting Protection)
        $ordersResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.orders'));
        $ordersResponse->assertOk();
        $ordersContent = $ordersResponse->getContent();

        $this->assertStringContainsString('Menarik Pesanan...', $ordersContent);
        $this->assertStringContainsString('Mulai Tarik Pesanan', $ordersContent);
    }

    public function test_it_rejects_mapping_for_service_items_guardrail_1(): void
    {
        Context::flush();
        Context::setBusiness($this->businessA);

        $serviceProduct = Product::create([
            'business_id'   => $this->businessA->id,
            'name'          => 'Jasa Servis Karburator Motor',
            'type'          => Product::TYPE_SERVICE,
            'selling_price' => 75000,
            'is_active'     => true,
            'output_unit_id' => $this->unit->id,
        ]);

        // 1. JSON Request -> 422
        $jsonResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id' => $serviceProduct->id,
            'channel'    => 'shopee',
        ]);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'error' => "Produk berjenis jasa/layanan fisik (\"{$serviceProduct->name}\") tidak dapat dipetakan ke saluran marketplace ekspedisi pengiriman barang.",
            ]);

        // 2. Web Form Request -> Redirect back with error flash
        $webResponse = $this->actingAs($this->userA)->post(route('marketplace-hub.products.map'), [
            'product_id' => $serviceProduct->id,
            'channel'    => 'shopee',
        ]);

        $webResponse->assertRedirect();
        $webResponse->assertSessionHas('error');
    }

    public function test_it_rejects_mapping_for_restricted_pharmacy_drugs_guardrail_2(): void
    {
        // Set business as retail_pharmacy
        $this->businessA->update([
            'template_code'     => 'retail_pharmacy',
            'industry_category' => 'retail_pharmacy',
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        $drugProduct = Product::create([
            'business_id'   => $this->businessA->id,
            'name'          => 'Amoxicillin 500mg Strip Obat Keras',
            'type'          => Product::TYPE_GOODS,
            'selling_price' => 35000,
            'is_active'     => true,
            'output_unit_id' => $this->unit->id,
        ]);

        $this->assertTrue($this->businessA->isPharmacy());
        $this->assertTrue($drugProduct->isRestrictedPharmacyProduct());

        // 1. JSON Request -> 422
        $jsonResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id' => $drugProduct->id,
            'channel'    => 'shopee',
        ]);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'error' => "Produk \"{$drugProduct->name}\" tergolong obat keras / resep dokter yang dilarang diperjualbelikan di marketplace umum berdasarkan regulasi BPOM RI.",
            ]);

        // 2. Web Form Request -> Redirect back with error flash
        $webResponse = $this->actingAs($this->userA)->post(route('marketplace-hub.products.map'), [
            'product_id' => $drugProduct->id,
            'channel'    => 'tokopedia',
        ]);

        $webResponse->assertRedirect();
        $webResponse->assertSessionHas('error');
    }

    public function test_it_enforces_anti_margin_bleed_guard_when_price_below_cost_guardrail_3(): void
    {
        // Reset template_code to normal
        $this->businessA->update([
            'template_code'     => 'retail_store',
            'industry_category' => 'retail',
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        $product = Product::create([
            'business_id'   => $this->businessA->id,
            'name'          => 'Kemeja Katun Pria Premium',
            'type'          => Product::TYPE_GOODS,
            'selling_price' => 120000,
            'base_cost'     => 100000,
            'is_active'     => true,
            'output_unit_id' => $this->unit->id,
        ]);

        // 1. Trying to set channel_price = 85.000 (< base_cost 100.000) with allow_below_cost = false -> 422
        $failResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id'       => $product->id,
            'channel'          => 'shopee',
            'sync_price_auto'  => 0,
            'channel_price'    => 85000,
            'allow_below_cost' => 0,
        ]);

        $failResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('berada di bawah modal dasar HPP', $failResponse->json('error'));
        $this->assertStringContainsString('15.000', $failResponse->json('error'));

        // 2. Trying with multiplier 0.7 (selling_price 120.000 * 0.7 = 84.000 < 100.000) without consent -> 422
        $multFailResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id'       => $product->id,
            'channel'          => 'shopee',
            'sync_price_auto'  => 1,
            'price_multiplier' => 0.7,
            'allow_below_cost' => 0,
        ]);

        $multFailResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 3. With explicit consent (allow_below_cost = 1) -> 200 Success & mapping saved
        $successResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id'       => $product->id,
            'channel'          => 'shopee',
            'sync_price_auto'  => 0,
            'channel_price'    => 85000,
            'allow_below_cost' => 1,
        ]);

        $successResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('marketplace_product_mappings', [
            'product_id'    => $product->id,
            'channel'       => 'shopee',
            'channel_price' => 85000,
        ]);
    }

    public function test_it_renders_phase_4_sector_banners_filters_and_anti_margin_bleed_ui(): void
    {
        // 1. Test Pharmacy Context UI (BPOM Banner)
        $this->businessA->update([
            'template_code'     => 'retail_pharmacy',
            'industry_category' => 'retail_pharmacy',
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        $pharmacyResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.products'));
        $pharmacyResponse->assertOk();
        $pharmacyContent = $pharmacyResponse->getContent();

        $this->assertStringContainsString('Guardrail Regulasi BPOM RI (Sektor Farmasi &amp; Apotek)', $pharmacyContent);
        $this->assertStringContainsString('Peraturan BPOM RI No. 8 Tahun 2020', $pharmacyContent);
        $this->assertStringContainsString('PERINGATAN ANTI-MARGIN BLEED', $pharmacyContent);
        $this->assertStringContainsString('allow_below_cost', $pharmacyContent);

        // 2. Test Service Sector Context UI (Service separation notice & type tabs)
        $this->businessA->update([
            'template_code'     => 'service_workshop',
            'industry_category' => 'service_workshop',
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        $serviceResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.products'));
        $serviceResponse->assertOk();
        $serviceContent = $serviceResponse->getContent();

        $this->assertStringContainsString('Pemisahan Barang Fisik vs Jasa Kasir', $serviceContent);
        $this->assertStringContainsString('Layanan Jasa', $serviceContent);
        $this->assertStringContainsString('Barang Fisik', $serviceContent);

        // 3. Test Product Type Filter Query Parameter
        Product::create([
            'business_id'   => $this->businessA->id,
            'name'          => 'Oli Mesin Matic 10W-30',
            'type'          => Product::TYPE_GOODS,
            'selling_price' => 50000,
            'output_unit_id' => $this->unit->id,
            'is_active'     => true,
        ]);

        Product::create([
            'business_id'   => $this->businessA->id,
            'name'          => 'Jasa Tune Up Motor Injeksi',
            'type'          => Product::TYPE_SERVICE,
            'selling_price' => 45000,
            'output_unit_id' => $this->unit->id,
            'is_active'     => true,
        ]);

        $filterGoodsResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.products', ['type' => 'goods']));
        $filterGoodsResponse->assertOk();
        $filterGoodsContent = $filterGoodsResponse->getContent();
        $this->assertStringContainsString('Oli Mesin Matic', $filterGoodsContent);
        $this->assertStringNotContainsString('Jasa Tune Up Motor Injeksi', $filterGoodsContent);

        $filterServiceResponse = $this->actingAs($this->userA)->get(route('marketplace-hub.products', ['type' => 'service']));
        $filterServiceResponse->assertOk();
        $filterServiceContent = $filterServiceResponse->getContent();
        $this->assertStringContainsString('Jasa Tune Up Motor Injeksi', $filterServiceContent);
        $this->assertStringNotContainsString('Oli Mesin Matic', $filterServiceContent);
    }

    public function test_it_prevents_bola_idor_cross_tenant_manipulation(): void
    {
        // 1. Create a product strictly owned by Business B
        $unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'name'        => 'Pieces',
            'code'        => 'PCS',
            'symbol'      => 'pcs',
            'category'    => 'quantity',
        ]);

        $productB = Product::create([
            'business_id'    => $this->businessB->id,
            'name'           => 'Barang Rahasia Toko B',
            'type'           => Product::TYPE_GOODS,
            'selling_price'  => 500000,
            'base_cost'      => 400000,
            'output_unit_id' => $unitB->id,
            'is_active'      => true,
        ]);

        // Account owned by Business B
        $accountB = MarketplaceAccount::create([
            'business_id'   => $this->businessB->id,
            'channel'       => 'tiktok_shop',
            'shop_id'       => 'SHOP-B-SECRET',
            'shop_name'     => 'Store B Secret',
            'access_token'  => 'tok_b_secret_999',
            'refresh_token' => 'ref_b_secret_999',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        Context::flush();
        Context::setBusiness($this->businessA);

        // Attack 1: User A tries to map Business B's product -> 404 ModelNotFound
        $idorMapResponse = $this->actingAs($this->userA)->postJson(route('marketplace-hub.products.map'), [
            'product_id' => $productB->id,
            'channel'    => 'shopee',
        ]);
        $idorMapResponse->assertStatus(404);

        // Attack 2: User A tries to trigger sync price for Business B's product -> 404
        $idorSyncPriceResponse = $this->actingAs($this->userA)->post(route('marketplace-hub.products.sync-price', $productB->id));
        $idorSyncPriceResponse->assertStatus(404);

        // Attack 3: User A tries to trigger sync stock for Business B's product -> 404
        $idorSyncStockResponse = $this->actingAs($this->userA)->post(route('marketplace-hub.products.sync-stock', $productB->id));
        $idorSyncStockResponse->assertStatus(404);

        // Attack 4: User A tries to disconnect Business B's account by ID -> Error, Business B's account remains connected
        $idorDisconnectResponse = $this->actingAs($this->userA)->post(route('marketplace-hub.disconnect', $accountB->id));
        $idorDisconnectResponse->assertSessionHas('error');
        $this->assertDatabaseHas('marketplace_accounts', [
            'id'     => $accountB->id,
            'status' => MarketplaceAccount::STATUS_CONNECTED,
        ]);
    }

    public function test_it_verifies_and_rejects_tiktok_and_tokopedia_webhooks(): void
    {
        // 1. TikTok Shop Webhook Cryptographic Verification
        $ttsSecret = 'tts_secret_xyz789';
        \App\Models\SystemSetting::set('tiktok_shop_app_secret', $ttsSecret);
        config(['services.tiktok_shop.app_secret' => $ttsSecret]);
        app()->forgetInstance(MarketplaceManagerService::class);

        $tiktokPayload = [
            'type'      => 1,
            'shop_id'   => 'TTS-SHOP-01',
            'timestamp' => time(),
            'data'      => ['order_id' => 'TTS-ORD-999'],
        ];
        $tiktokRaw = (string) json_encode($tiktokPayload);
        $validTtsSign = hash_hmac('sha256', $tiktokRaw, $ttsSecret);

        // Missing signature -> 401
        $ttsMissing = $this->call('POST', '/webhooks/marketplace/tiktok', [], [], [], ['CONTENT_TYPE' => 'application/json'], $tiktokRaw);
        $ttsMissing->assertStatus(401);

        // Invalid signature -> 401
        $ttsForged = $this->call('POST', '/webhooks/marketplace/tiktok', [], [], [], [
            'HTTP_AUTHORIZATION' => 'invalid_hash_value',
            'CONTENT_TYPE'       => 'application/json',
        ], $tiktokRaw);
        $ttsForged->assertStatus(401);

        // Valid signature -> 200
        $ttsValid = $this->call('POST', '/webhooks/marketplace/tiktok', [], [], [], [
            'HTTP_AUTHORIZATION' => $validTtsSign,
            'CONTENT_TYPE'       => 'application/json',
        ], $tiktokRaw);
        $ttsValid->assertOk()->assertJson(['success' => true]);

        // 2. Tokopedia Webhook Verification
        $tkpdSecret = 'tkpd_secret_abc123';
        \App\Models\SystemSetting::set('tokopedia_webhook_secret', $tkpdSecret);
        config(['services.tokopedia.webhook_secret' => $tkpdSecret]);
        app()->forgetInstance(MarketplaceManagerService::class);

        $tokpedPayload = [
            'order_id'     => 1234567,
            'fs_id'        => 9988,
            'order_status' => 200,
        ];
        $tokpedRaw = (string) json_encode($tokpedPayload);

        // Missing token -> 401
        $tokpedMissing = $this->call('POST', '/webhooks/marketplace/tokopedia', [], [], [], ['CONTENT_TYPE' => 'application/json'], $tokpedRaw);
        $tokpedMissing->assertStatus(401);

        // Valid token in header -> 200
        $tokpedValid = $this->call('POST', '/webhooks/marketplace/tokopedia', [], [], [], [
            'HTTP_X_TKPD_TOKEN' => $tkpdSecret,
            'CONTENT_TYPE'      => 'application/json',
        ], $tokpedRaw);
        $tokpedValid->assertOk()->assertJson(['success' => true]);
    }

    public function test_it_runs_setup_tiktok_review_demo_command_cleanly(): void
    {
        Context::flush();
        Context::setBusiness($this->businessA);

        Product::create([
            'business_id'    => $this->businessA->id,
            'name'           => 'Baju Kemeja Review Demo',
            'type'           => Product::TYPE_GOODS,
            'selling_price'  => 150000,
            'base_cost'      => 100000,
            'output_unit_id' => $this->unit->id,
            'is_active'      => true,
        ]);

        $exitCode = \Illuminate\Support\Facades\Artisan::call('marketplace:setup-tiktok-review', [
            '--business'  => $this->businessA->id,
            '--shop-id'   => 'IDLSA-TEST-REVIEW',
            '--shop-name' => 'Demo Toko TikTok ID',
        ]);

        $this->assertEquals(0, $exitCode);

        // Verify account is connected
        $this->assertDatabaseHas('marketplace_accounts', [
            'business_id' => $this->businessA->id,
            'channel'     => MarketplaceAccount::CHANNEL_TIKTOK,
            'shop_id'     => 'IDLSA-TEST-REVIEW',
            'shop_name'   => 'Demo Toko TikTok ID',
            'status'      => MarketplaceAccount::STATUS_CONNECTED,
        ]);

        // Verify orders are seeded
        $this->assertDatabaseHas('marketplace_orders', [
            'business_id' => $this->businessA->id,
            'channel'     => MarketplaceAccount::CHANNEL_TIKTOK,
            'buyer_name'  => 'Rizky Ramadhani (TikTok Buyer)',
        ]);

        // Verify disconnect option
        $disconnectExit = \Illuminate\Support\Facades\Artisan::call('marketplace:setup-tiktok-review', [
            '--business'   => $this->businessA->id,
            '--disconnect' => true,
        ]);
        $this->assertEquals(0, $disconnectExit);

        $this->assertDatabaseHas('marketplace_accounts', [
            'business_id' => $this->businessA->id,
            'channel'     => MarketplaceAccount::CHANNEL_TIKTOK,
            'status'      => MarketplaceAccount::STATUS_DISCONNECTED,
        ]);
    }
}




