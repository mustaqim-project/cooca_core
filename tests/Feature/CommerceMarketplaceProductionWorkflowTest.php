<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommerceProductReview;
use App\Models\CommerceStoreSetting;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CommerceMarketplaceProductionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private GlobalCustomer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Owner Toko',
            'email' => 'owner@cooca-test.id',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Toko Kopi Sejahtera',
            'slug' => 'toko-kopi-sejahtera',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->merchantUser->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat',
            'code' => 'LOC-01',
            'is_active' => true,
            'is_primary' => true,
            'allow_storefront_pickup' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'min_order_amount' => 10000,
        ]);

        $pieceUnit = Unit::where('code', 'pcs')->first()
            ?? Unit::create([
                'business_id' => $this->business->id,
                'name' => 'Pieces',
                'code' => 'pcs',
                'category' => 'piece',
                'is_active' => true,
                'is_standard' => true,
            ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika Premium 250g',
            'slug' => 'kopi-arabika-premium-250g',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 75000,
            'base_cost' => 45000,
            'output_unit_id' => $pieceUnit->id,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $this->customer = GlobalCustomer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'phone' => '081298765432',
            'phone_verified_at' => now(),
            'profile_completed_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_customer_cart_checkout_redirects_to_dedicated_checkout_page(): void
    {
        $cart = CustomerCart::create([
            'global_customer_id' => $this->customer->id,
            'business_id' => $this->business->id,
        ]);

        CustomerCartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 75000,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('customer.cart.checkout', ['slug' => $this->business->slug]));

        $response->assertRedirect(route('public.storefront.checkout.page', ['slug' => $this->business->slug]));
        $this->assertEquals($cart->id, session("customer_cart_checkout_{$this->business->id}"));
    }

    public function test_customer_can_cancel_unpaid_order_and_reserved_stock_is_released(): void
    {
        // Reserve stock first
        $stockService = app(StockService::class);
        $stockService->reserveProductStock(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $this->product,
            productQuantity: 3.0
        );

        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(3.0, (float) $stock->reserved_quantity);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'global_customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-001',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 225000,
            'total_amount' => 225000,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 3,
            'unit_price' => 75000,
            'subtotal' => 225000,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('customer.orders.cancel', ['id' => $order->id]), [
                'cancel_reason' => 'Ingin ganti varian',
            ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_CANCELLED, $order->status);
        $this->assertNotNull($order->cancelled_at);

        $stock->refresh();
        $this->assertEquals(0.0, (float) $stock->reserved_quantity);
    }

    public function test_customer_can_complete_shipped_order(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'global_customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-002',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => CommerceOrder::STATUS_SHIPPED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 75000,
            'total_amount' => 75000,
            'shipping_waybill_id' => 'JNE1234567890',
            'shipping_courier_name' => 'JNE Reguler',
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('customer.orders.complete', ['id' => $order->id]));

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_COMPLETED, $order->status);
    }

    public function test_customer_can_submit_verified_review_for_completed_order(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'global_customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-003',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => CommerceOrder::STATUS_COMPLETED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 75000,
            'total_amount' => 75000,
        ]);

        $orderItem = CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('customer.orders.review', ['id' => $order->id]), [
                'reviews' => [
                    [
                        'product_id' => $this->product->id,
                        'order_item_id' => $orderItem->id,
                        'rating' => 5,
                        'review_text' => 'Kopi mantap, aroma sangat segar dan packaging aman!',
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('commerce_product_reviews', [
            'business_id' => $this->business->id,
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'global_customer_id' => $this->customer->id,
            'rating' => 5,
            'is_verified_purchase' => true,
            'is_published' => true,
        ]);

        $this->product->refresh();
        $this->assertEquals(5.0, $this->product->rating_average);
        $this->assertEquals(1, $this->product->reviews_count);
    }

    public function test_auto_journal_service_records_double_entry_for_commerce_order(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'global_customer_id' => $this->customer->id,
            'order_number' => 'ORD-JRN-001',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_fee' => 525,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 75000,
            'total_amount' => 75000,
            'paid_at' => now(),
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
        ]);

        $journalService = app(AutoJournalService::class);
        $entry = $journalService->recordCommerceOrderJournal($order);

        $this->assertNotNull($entry);
        $this->assertEquals(JournalEntry::REF_COMMERCE_ORDER, $entry->reference_type);
        $this->assertEquals($order->id, $entry->reference_id);
        $this->assertEquals($entry->total_debit, $entry->total_credit);

        // Idempotency check: Calling again returns the exact same entry without duplicating
        $secondEntry = $journalService->recordCommerceOrderJournal($order);
        $this->assertEquals($entry->id, $secondEntry->id);
    }

    public function test_public_marketplace_subpages_render_successfully(): void
    {
        $respBusinesses = $this->get(route('marketplace.sub.businesses'));
        $respBusinesses->assertOk();
        $respBusinesses->assertViewHas('stores');

        $respProducts = $this->get(route('marketplace.sub.products'));
        $respProducts->assertOk();
        $respProducts->assertViewHas('products');
    }

    public function test_customer_order_detail_renders_biteship_tracking_status_badges(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'global_customer_id' => $this->customer->id,
            'order_number' => 'ORD-BITESHIP-001',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'status' => CommerceOrder::STATUS_SHIPPED,
            'shipping_status' => 'droppingOff',
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 75000,
            'total_amount' => 75000,
            'shipping_waybill_id' => 'BITESHIP123456789',
            'shipping_courier_name' => 'SiCepat BEST',
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('customer.orders.detail', ['id' => $order->id]));

        $response->assertOk();
        $response->assertSee('Sedang Diantar ke Penerima');
        $response->assertSee('Ekspedisi Biteship');
        $response->assertSee('BITESHIP123456789');
    }
}
