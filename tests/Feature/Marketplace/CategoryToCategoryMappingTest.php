<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\MarketplaceSyncService;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryToCategoryMappingTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $user;
    protected Unit $unit;
    protected ProductCategory $category;
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

        $this->category = ProductCategory::create([
            'business_id'               => $this->business->id,
            'name'                      => 'Aneka Kopi Spesial',
            'code'                      => 'KOPI',
            'marketplace_category_id'   => '601100',
            'marketplace_category_name' => 'Makanan & Minuman (FnB)',
        ]);

        $this->product = Product::create([
            'business_id'         => $this->business->id,
            'category_id'         => $this->category->id,
            'name'                => 'Kopi Arabika Gayo 200g',
            'code'                => 'ARABIKA-GAYO',
            'type'                => Product::TYPE_GOODS,
            'unit_id'             => $this->unit->id,
            'output_unit_id'      => $this->unit->id,
            'sell_price'          => 85000,
            'is_sellable'         => true,
            'is_pos_available'    => true,
            'marketplace_options' => [
                'channels' => ['shopee', 'tiktok', 'tokopedia'],
            ],
        ]);
    }

    public function test_category_can_store_and_update_marketplace_category(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('product-categories.store'), [
                'name'                      => 'Busana Muslim Pria',
                'code'                      => 'BUS-MUS',
                'marketplace_category_id'   => '600003',
                'marketplace_category_name' => 'Muslim Fashion',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_categories', [
            'business_id'               => $this->business->id,
            'name'                      => 'Busana Muslim Pria',
            'marketplace_category_id'   => '600003',
            'marketplace_category_name' => 'Muslim Fashion',
        ]);

        $createdCategory = ProductCategory::where('name', 'Busana Muslim Pria')->first();
        $this->assertNotNull($createdCategory);
        $this->assertTrue($createdCategory->hasMarketplaceCategory());
        $this->assertEquals('600003', $createdCategory->marketplace_category_id);
    }

    public function test_category_update_cascades_to_existing_marketplace_mappings_when_requested(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'shopee',
            'shop_id'       => 'shop_shopee_1',
            'shop_name'     => 'Shopee Kopi Official',
            'access_token'  => 'tok_test',
            'refresh_token' => 'ref_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $mapping = MarketplaceProductMapping::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'product_id'             => $this->product->id,
            'channel'                => 'shopee',
            'marketplace_product_id' => 'shp_item_999',
            'sync_status'            => MarketplaceProductMapping::STATUS_SYNCED,
            'raw_metadata'           => ['category_id' => '999999'],
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('product-categories.update', $this->category->id), [
                'name'                      => 'Aneka Kopi Spesial Edit',
                'code'                      => 'KOPI',
                'marketplace_category_id'   => '601100',
                'marketplace_category_name' => 'Makanan & Minuman (FnB)',
                'cascade_to_products'       => true,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_categories', [
            'id'   => $this->category->id,
            'name' => 'Aneka Kopi Spesial Edit',
        ]);

        $mapping->refresh();
        $this->assertEquals('601100', $mapping->raw_metadata['category_id'] ?? null);
    }

    public function test_publish_product_inherits_marketplace_category_from_product_category(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'shopee',
            'shop_id'       => 'shop_shopee_2',
            'shop_name'     => 'Shopee Kopi Barista',
            'access_token'  => 'tok_test',
            'refresh_token' => 'ref_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $service = app(MarketplaceSyncService::class);
        $result = $service->publishProductToChannel($this->product, 'shopee');

        $this->assertTrue($result['success']);

        $mapping = MarketplaceProductMapping::where('product_id', $this->product->id)
            ->where('channel', 'shopee')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals('601100', $mapping->raw_metadata['category_id'] ?? null);
    }

    public function test_explicit_category_in_options_overrides_parent_category_mapping(): void
    {
        MarketplaceAccount::create([
            'business_id'   => $this->business->id,
            'channel'       => 'shopee',
            'shop_id'       => 'shop_shopee_3',
            'shop_name'     => 'Shopee Custom Store',
            'access_token'  => 'tok_test',
            'refresh_token' => 'ref_test',
            'status'        => MarketplaceAccount::STATUS_CONNECTED,
            'is_active'     => true,
        ]);

        $service = app(MarketplaceSyncService::class);
        // Explicitly pass category 600007 (Sepatu) overriding category 601100 (Makanan & Minuman)
        $result = $service->publishProductToChannel($this->product, 'shopee', [
            'marketplace_category_id' => '600007',
        ]);

        $this->assertTrue($result['success']);

        $mapping = MarketplaceProductMapping::where('product_id', $this->product->id)
            ->where('channel', 'shopee')
            ->first();

        $this->assertNotNull($mapping);
        $this->assertEquals('600007', $mapping->raw_metadata['category_id'] ?? null);
    }
}
