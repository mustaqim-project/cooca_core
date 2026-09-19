<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\ReservedSlugService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceOrder;
use App\Models\CommerceStoreSetting;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\Product;
use App\Models\User;
use App\Services\Seo\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalStorefrontSlugRoutingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'email' => 'owner@cooca.id',
            'phone' => '081234567890',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kedai Kopi Nusantara',
            'slug' => 'kedai-kopi-nusantara',
            'is_active' => true,
            'currency' => 'IDR',
            'email' => 'kopi@cooca.id',
        ]);

        $this->business->users()->attach($this->owner->id, ['role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);

        BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Kopi Nusantara Terbaik',
            'is_published' => true,
            'theme_color' => '#007AFF',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_reservation' => true,
        ]);
    }

    public function test_storefront_accessible_via_canonical_direct_slug(): void
    {
        $response = $this->get('/' . $this->business->slug);

        $response->assertStatus(200);
        $response->assertSee('Kopi Nusantara Terbaik');
        // Check canonical tag points to canonical URL
        $response->assertSee('<link rel="canonical" href="' . $this->business->public_url . '">', false);
        // Check og:url points to canonical URL
        $response->assertSee('<meta property="og:url" content="' . $this->business->public_url . '">', false);
    }

    public function test_legacy_b_slug_remains_functional_as_alias(): void
    {
        $response = $this->get('/b/' . $this->business->slug);

        $response->assertStatus(200);
        $response->assertSee('Kopi Nusantara Terbaik');
        // Canonical tag still points to canonical root slug URL
        $response->assertSee('<link rel="canonical" href="' . $this->business->public_url . '">', false);
    }

    public function test_business_model_public_url_and_storefront_url_accessors(): void
    {
        $expected = url('/' . $this->business->slug);

        $this->assertSame($expected, $this->business->public_url);
        $this->assertSame($expected, $this->business->storefront_url);
    }

    public function test_storefront_actions_accessible_without_b_prefix(): void
    {
        // 1. Order tracking without /b/ prefix
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-TEST-001',
            'tracking_token' => \Illuminate\Support\Str::random(32),
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081299998888',
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'total_amount' => 50000,
            'payment_channel' => 'QRIS',
        ]);

        $trackingResponse = $this->get("/{$this->business->slug}/order/{$order->tracking_token}");
        $trackingResponse->assertStatus(200);
        $trackingResponse->assertSee($order->order_number);

        // Also test legacy alias /b/... works
        $legacyTracking = $this->get("/b/{$this->business->slug}/order/{$order->tracking_token}");
        $legacyTracking->assertStatus(200);

        \App\Models\CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Ongkir Flat',
            'rule_type' => \App\Models\CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 10000,
            'is_active' => true,
        ]);

        // 2. Shipping calculate without /b/ prefix
        $calcResponse = $this->postJson("/{$this->business->slug}/shipping/calculate", [
            'subtotal' => 50000,
        ]);
        $calcResponse->assertStatus(200);
        $calcResponse->assertJsonPath('success', true);

        // 3. Reservation check without /b/ prefix
        $resCheck = $this->getJson("/{$this->business->slug}/reservasi/check?date=" . now()->addDay()->toDateString() . "&time_slot=19:00");
        $resCheck->assertStatus(200);
    }

    public function test_reserved_slug_protection_sanitizes_system_routes(): void
    {
        // Reserved words validation
        $this->assertTrue(ReservedSlugService::isReserved('admin'));
        $this->assertTrue(ReservedSlugService::isReserved('login'));
        $this->assertTrue(ReservedSlugService::isReserved('pos'));
        $this->assertTrue(ReservedSlugService::isReserved('hrm'));
        $this->assertTrue(ReservedSlugService::isReserved('api'));
        $this->assertFalse(ReservedSlugService::isReserved('kopi-senja-kebon-jeruk'));

        // Attempt to create business named "Admin"
        $adminBiz = Business::create([
            'name' => 'Admin',
            'is_active' => true,
            'currency' => 'IDR',
        ]);
        $this->assertNotSame('admin', $adminBiz->slug);
        $this->assertSame('admin-store', $adminBiz->slug);

        // Attempt to create business named "Login"
        $loginBiz = Business::create([
            'name' => 'Login',
            'is_active' => true,
            'currency' => 'IDR',
        ]);
        $this->assertNotSame('login', $loginBiz->slug);
        $this->assertSame('login-store', $loginBiz->slug);

        // Attempt to create second business named "Login" (should append index)
        $loginBiz2 = Business::create([
            'name' => 'Login',
            'is_active' => true,
            'currency' => 'IDR',
        ]);
        $this->assertSame('login-store-2', $loginBiz2->slug);
    }

    public function test_customer_portal_checkout_redirects_to_canonical_slug(): void
    {
        $customer = GlobalCustomer::create([
            'google_id' => 'google-test-12345',
            'email' => 'customer@example.com',
            'name' => 'Test Customer',
            'phone' => '08123456789',
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Robusta 250gr',
            'selling_price' => 25000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $cart = CustomerCart::create([
            'global_customer_id' => $customer->id,
            'business_id' => $this->business->id,
        ]);

        CustomerCartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25000,
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.checkout', ['slug' => $this->business->slug]));

        // Assert redirect to canonical route 'public.business.landing' (which is /{slug})
        $response->assertRedirect(route('public.business.landing', ['slug' => $this->business->slug]));
        $this->assertEquals(url('/' . $this->business->slug), route('public.business.landing', ['slug' => $this->business->slug]));
    }

    public function test_sitemap_outputs_canonical_urls_without_b_prefix(): void
    {
        $sitemapService = new SitemapService();
        $xml = $sitemapService->generateXml();

        // Must contain canonical storefront URL
        $this->assertStringContainsString('https://cooca.id/' . $this->business->slug, $xml);

        // Must NOT contain legacy /b/ URL
        $this->assertStringNotContainsString('https://cooca.id/b/' . $this->business->slug, $xml);
    }
}
