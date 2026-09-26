<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductBundleItem;
use App\Models\ProductChannelPrice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductBundleAndChannelPriceSchemaTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner F&B',
            'email' => 'owner.fnb@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Kafe Senja Bersama',
            'slug' => 'kafe-senja-bersama',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PORSI',
            'symbol' => 'prs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);
    }

    public function test_product_bundle_item_creation_and_relationship(): void
    {
        $productA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Aren',
            'code' => 'KSA',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'base_cost' => 5000,
            'is_bundle' => false,
        ]);

        $productB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Butter Croissant',
            'code' => 'BCR',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 20000,
            'base_cost' => 8000,
            'is_bundle' => false,
        ]);

        $bundleProduct = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kombo Kopi & Croissant',
            'code' => 'KMB-KC',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 30000,
            'base_cost' => 0,
            'is_bundle' => true,
        ]);

        $itemA = ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundleProduct->id,
            'child_product_id' => $productA->id,
            'quantity' => 1.0,
        ]);

        $itemB = ProductBundleItem::create([
            'business_id' => $this->business->id,
            'parent_product_id' => $bundleProduct->id,
            'child_product_id' => $productB->id,
            'quantity' => 1.0,
        ]);

        $this->assertTrue($bundleProduct->isBundle());
        $this->assertFalse($productA->isBundle());

        $this->assertCount(2, $bundleProduct->bundleItems);
        $this->assertEquals($bundleProduct->id, $itemA->parentProduct->id);
        $this->assertEquals($productA->id, $itemA->childProduct->id);
        $this->assertEquals($productB->id, $itemB->childProduct->id);

        // HPP should sum: (5000 * 1) + (8000 * 1) = 13000
        $this->assertEquals(13000.0, $bundleProduct->getBundleHpp());
    }

    public function test_product_channel_price_creation_and_resolution(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Aren',
            'code' => 'KSA',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'base_cost' => 5000,
        ]);

        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'channel' => ProductChannelPrice::CHANNEL_GOFOOD,
            'price' => 18000,
        ]);

        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'channel' => ProductChannelPrice::CHANNEL_SHOPEEFOOD,
            'price' => 19000,
        ]);

        $this->assertEquals(18000.0, $product->getChannelPrice(ProductChannelPrice::CHANNEL_GOFOOD));
        $this->assertEquals(19000.0, $product->getChannelPrice(ProductChannelPrice::CHANNEL_SHOPEEFOOD));
        // Fallback to normal selling_price for dine_in or non-configured channels
        $this->assertEquals(15000.0, $product->getChannelPrice(ProductChannelPrice::CHANNEL_DINE_IN));
        $this->assertEquals(15000.0, $product->getChannelPrice(ProductChannelPrice::CHANNEL_GRABFOOD));
    }

    public function test_pos_order_accepts_sales_channel_and_external_ref(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-20260926-0001',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'delivery',
            'sales_channel' => 'gofood',
            'external_order_ref' => 'GF-89201',
            'subtotal' => 36000,
            'total_amount' => 36000,
            'paid_amount' => 36000,
            'change_amount' => 0,
        ]);

        $this->assertEquals('gofood', $order->sales_channel);
        $this->assertEquals('GF-89201', $order->external_order_ref);

        $fresh = PosOrder::find($order->id);
        $this->assertEquals('gofood', $fresh->sales_channel);
        $this->assertEquals('GF-89201', $fresh->external_order_ref);
    }
}
