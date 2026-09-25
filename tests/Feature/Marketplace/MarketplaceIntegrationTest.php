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
        $response = $this->postJson('/webhooks/marketplace/shopee', [
            'shop_id'   => '123456',
            'code'      => 3,
            'timestamp' => time(),
            'data'      => [
                'ordersn' => '240925SHP9999',
                'status'  => 'PAID',
            ],
        ], [
            'Authorization' => 'dummy_valid_signature',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }
}
