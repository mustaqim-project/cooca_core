<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductChannelPrice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosChannelPricingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Kasir F&B',
            'email' => 'kasir_fnb@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kedai Kopi Multi Channel',
            'pos_enable_tax' => false,
            'pos_enable_service_charge' => false,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'type' => 'outlet',
            'is_active' => true,
            'is_primary' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Based',
            'slug' => 'espresso-based',
        ]);

        // Base product: Rp18.000 regular price, base cost Rp7.000
        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'code' => 'KOP-LATTE',
            'name' => 'Cafe Latte Single Origin',
            'slug' => 'cafe-latte-single-origin',
            'base_cost' => 7000,
            'selling_price' => 18000,
            'is_active' => true,
        ]);

        // Stock: 100 cups
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'last_cost' => 7000,
        ]);

        // Custom pricing for delivery channels: GoFood Rp23.000, GrabFood Rp23.000, ShopeeFood Rp22.000
        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'channel' => ProductChannelPrice::CHANNEL_GOFOOD,
            'price' => 23000,
        ]);

        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'channel' => ProductChannelPrice::CHANNEL_GRABFOOD,
            'price' => 23000,
        ]);

        ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'channel' => ProductChannelPrice::CHANNEL_SHOPEEFOOD,
            'price' => 22000,
        ]);
    }

    public function test_pos_terminal_view_contains_channel_prices_map(): void
    {
        $this->actingAs($this->user, 'web');
        session(['active_business_id' => $this->business->id]);

        $response = $this->get(route('pos.terminal'));
        $response->assertStatus(200);

        // Verify products collection has channel_prices attribute mapped
        $products = $response->viewData('products');
        $this->assertNotEmpty($products);

        $productItem = $products->firstWhere('id', $this->product->id);
        $this->assertNotNull($productItem);
        $this->assertIsArray($productItem->channel_prices);
        $this->assertEquals(18000.0, $productItem->channel_prices['dine_in']);
        $this->assertEquals(18000.0, $productItem->channel_prices['takeaway']);
        $this->assertEquals(23000.0, $productItem->channel_prices['gofood']);
        $this->assertEquals(23000.0, $productItem->channel_prices['grabfood']);
        $this->assertEquals(22000.0, $productItem->channel_prices['shopeefood']);
    }

    public function test_pos_checkout_saves_gofood_channel_price_and_external_order_ref(): void
    {
        $this->actingAs($this->user, 'web');
        session(['active_business_id' => $this->business->id]);

        // Open cashier shift
        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 50000,
        ]);

        // Checkout 2 cups at GoFood channel price (2 * Rp23.000 = Rp46.000)
        $payload = [
            'location_id' => $this->location->id,
            'order_type' => 'takeaway',
            'sales_channel' => 'gofood',
            'external_order_ref' => 'GF-99201',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 23000,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'digital',
                    'amount' => 46000,
                ],
            ],
        ];

        $response = $this->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.total_amount', 46000)
            ->assertJsonPath('order.sales_channel', 'gofood')
            ->assertJsonPath('order.external_order_ref', 'GF-99201');

        $order = PosOrder::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('gofood', $order->sales_channel);
        $this->assertEquals('GF-99201', $order->external_order_ref);
        $this->assertEquals(46000.0, (float) $order->total_amount);

        // HPP calculation: 2 * Rp7.000 = Rp14.000
        $this->assertEquals(14000.0, (float) $order->total_hpp_cost);
        // Gross Profit: Rp46.000 - Rp14.000 = Rp32.000
        $this->assertEquals(32000.0, (float) $order->total_gross_profit);

        // Verify stock decremented
        $stock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(98, $stock->quantity);
    }

    public function test_pos_receipt_displays_channel_badge_and_ref(): void
    {
        $this->actingAs($this->user, 'web');
        session(['active_business_id' => $this->business->id]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-GF-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'takeaway',
            'sales_channel' => 'gofood',
            'external_order_ref' => 'GF-112233',
            'subtotal' => 23000,
            'total_amount' => 23000,
            'paid_amount' => 23000,
            'change_amount' => 0,
            'total_hpp_cost' => 7000,
            'total_gross_profit' => 16000,
        ]);

        $response = $this->get(route('pos.receipt', $order->id));
        $response->assertStatus(200);
        $response->assertSee('CHANNEL:');
        $response->assertSee('GOFOOD');
        $response->assertSee('REF ORDER:');
        $response->assertSee('#GF-112233');
    }
}
