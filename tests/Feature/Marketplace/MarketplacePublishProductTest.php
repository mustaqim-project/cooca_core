<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\MarketplaceSyncService;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplacePublishProductTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $user;
    protected Unit $unit;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'Owner Toko',
            'email'    => 'owner@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name'            => 'Toko Utama COOCA',
            'currency_code'   => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name'        => 'Pieces',
            'code'        => 'PCS',
            'symbol'      => 'pcs',
            'category'    => 'quantity',
        ]);

        $this->product = Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Kopi Susu Gula Aren 250ml',
            'code'           => 'KOP-250',
            'type'           => Product::TYPE_GOODS,
            'unit_id'        => $this->unit->id,
            'output_unit_id' => $this->unit->id,
            'selling_price'  => 20000,
            'base_cost'      => 12000,
            'stock'          => 50,
            'image'          => 'products/main_thumb.jpg',
            'is_active'      => true,
        ]);

        // Add 2 gallery images
        ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'products/gallery/thumb_1.jpg',
            'sort_order' => 1,
        ]);
        ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'products/gallery/thumb_2.jpg',
            'sort_order' => 2,
        ]);
    }

    public function test_it_publishes_product_to_shopee_with_gallery_images(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'shopee',
            'shop_id'       => 'SHOP-12345',
            'shop_name'     => 'Shopee COOCA Store',
            'access_token'  => 'tok_test',
            'refresh_token' => 'ref_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.publish', $this->product->id), [
                'channel'          => 'shopee',
                'channel_price'    => 24000,
                'sync_price_auto'  => false,
                'custom_stock'     => 25,
                'sync_stock_auto'  => false,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('external_product_id'));

        // Check database mapping
        $mapping = MarketplaceProductMapping::where('business_id', $this->business->id)
            ->where('product_id', $this->product->id)
            ->where('channel', 'shopee')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals(24000, $mapping->channel_price);
        $this->assertEquals(25, $mapping->custom_stock);
        $this->assertEquals(MarketplaceProductMapping::STATUS_SYNCED, $mapping->sync_status);

        // Check audit sync log
        $this->assertDatabaseHas('marketplace_sync_logs', [
            'business_id' => $this->business->id,
            'channel'     => 'shopee',
            'action'      => 'publish_product',
            'status'      => 'success',
        ]);
    }

    public function test_it_publishes_product_to_tiktok_shop_with_markup_multiplier(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'tiktok_shop',
            'shop_id'       => 'TTS-998877',
            'shop_name'     => 'TikTok Shop COOCA Store',
            'access_token'  => 'tok_tts_test',
            'refresh_token' => 'ref_tts_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.publish', $this->product->id), [
                'channel'          => 'tiktok_shop',
                'price_multiplier' => 1.10,
                'sync_price_auto'  => true,
                'sync_stock_auto'  => true,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $mapping = MarketplaceProductMapping::where('business_id', $this->business->id)
            ->where('product_id', $this->product->id)
            ->where('channel', 'tiktok_shop')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals(1.10, $mapping->price_multiplier);
        $this->assertTrue($mapping->sync_price_auto);
        $this->assertEquals(MarketplaceProductMapping::STATUS_SYNCED, $mapping->sync_status);
    }

    public function test_it_blocks_publishing_service_products(): void
    {
        $service = Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Jasa Cuci Baju / Servis',
            'type'           => Product::TYPE_SERVICE,
            'unit_id'        => $this->unit->id,
            'output_unit_id' => $this->unit->id,
            'selling_price'  => 50000,
            'base_cost'      => 10000,
            'is_active'      => true,
        ]);

        MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => 'shopee',
            'shop_id'      => 'SHOP-12345',
            'shop_name'    => 'Shopee Store',
            'access_token' => 'tok_test',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.publish', $service->id), [
                'channel' => 'shopee',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_it_registers_all_30_official_marketplace_categories(): void
    {
        $categories = \App\Domain\Marketplace\MarketplaceCategoryRegistry::all();
        $this->assertCount(30, $categories);

        // Check key categories requested
        $names = array_column($categories, 'name');
        $this->assertContains('Perlengkapan Rumah Tangga', $names);
        $this->assertContains('Perlengkapan Dapur', $names);
        $this->assertContains('Tekstil & Soft Furnishing', $names);
        $this->assertContains('Peralatan Rumah Tangga', $names);
        $this->assertContains('Pakaian & Dalaman Wanita', $names);
        $this->assertContains('Muslim Fashion', $names);
        $this->assertContains('Sepatu', $names);
        $this->assertContains('Perawatan & Kecantikan', $names);
        $this->assertContains('Ponsel & Elektronik', $names);
        $this->assertContains('Komputer & Peralatan Kantor', $names);
        $this->assertContains('Perlengkapan Hewan Peliharaan', $names);
        $this->assertContains('Ibu & Bayi', $names);
        $this->assertContains('Olahraga & Outdoor', $names);
        $this->assertContains('Mainan & Hobi', $names);
        $this->assertContains('Furnitur', $names);
        $this->assertContains('Alat & Perangkat Keras', $names);
        $this->assertContains('Renovasi Rumah', $names);
        $this->assertContains('Otomotif & Motor', $names);
        $this->assertContains('Aksesori Pakaian', $names);
        $this->assertContains('Makanan & Minuman', $names);
        $this->assertContains('Kesehatan', $names);
        $this->assertContains('Buku, Majalah, & Audio', $names);
        $this->assertContains('Pakaian Anak', $names);
        $this->assertContains('Pakaian & Dalaman Pria', $names);
        $this->assertContains('Koper & Tas', $names);
        $this->assertContains('Produk Virtual', $names);
        $this->assertContains('Barang Bekas', $names);
        $this->assertContains('Koleksi', $names);
        $this->assertContains('Aksesori Perhiasan & Turunannya', $names);
        $this->assertContains('Pemesanan & Voucher', $names);
    }

    public function test_it_suggests_category_automatically_for_coffee_product(): void
    {
        $suggested = \App\Domain\Marketplace\MarketplaceCategoryRegistry::suggestCategory($this->product);
        $this->assertNotNull($suggested);
        $this->assertEquals('Makanan & Minuman', $suggested['name']);
        $this->assertEquals('601100', $suggested['id']);
    }

    public function test_it_publishes_product_with_selected_marketplace_category(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'tiktok_shop',
            'shop_id'       => 'TTS-CAT-TEST',
            'shop_name'     => 'TikTok Shop Category Test',
            'access_token'  => 'tok_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.publish', $this->product->id), [
                'channel'          => 'tiktok_shop',
                'category_id'      => '601100',
                'category_name'    => 'Makanan & Minuman',
                'channel_price'    => 25000,
                'sync_price_auto'  => false,
                'sync_stock_auto'  => true,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $mapping = MarketplaceProductMapping::where('business_id', $this->business->id)
            ->where('product_id', $this->product->id)
            ->where('channel', 'tiktok_shop')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals('601100', $mapping->raw_metadata['category_id'] ?? null);
        $this->assertEquals('Makanan & Minuman', $mapping->raw_metadata['category_name'] ?? null);
    }

    public function test_it_persists_category_in_update_mapping_modal_endpoint(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'tokopedia',
            'shop_id'       => 'TKPD-MAP-TEST',
            'shop_name'     => 'Tokopedia Map Test',
            'access_token'  => 'tok_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.map'), [
                'product_id'          => $this->product->id,
                'channel'             => 'tokopedia',
                'category_id'         => '600202',
                'category_name'       => 'Sepatu',
                'marketplace_item_id' => 'TKPD-123984',
                'channel_price'       => 20000,
                'sync_price_auto'     => 0,
                'sync_stock_auto'     => 1,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $mapping = MarketplaceProductMapping::where('business_id', $this->business->id)
            ->where('product_id', $this->product->id)
            ->where('channel', 'tokopedia')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals('TKPD-123984', $mapping->external_product_id);
        $this->assertEquals('600202', $mapping->raw_metadata['category_id'] ?? null);
        $this->assertEquals('Sepatu', $mapping->raw_metadata['category_name'] ?? null);
    }
}
