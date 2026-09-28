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
}
