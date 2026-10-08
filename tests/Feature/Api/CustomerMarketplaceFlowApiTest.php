<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\GlobalCustomerAddress;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CustomerMarketplaceFlowApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Merchants
        $this->businessA = Business::create([
            'name' => 'Toko Sejahtera',
            'slug' => 'toko-sejahtera',
            'email' => 'sejahtera@store.com',
            'is_active' => true,
        ]);

        $this->businessB = Business::create([
            'name' => 'Toko Makmur',
            'slug' => 'toko-makmur',
            'email' => 'makmur@store.com',
            'is_active' => true,
        ]);

        // 2. Setup Units & Products
        $unitA = \App\Models\Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'BTL',
            'name' => 'Botol',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        $unitB = \App\Models\Unit::create([
            'business_id' => $this->businessB->id,
            'code' => 'LIT',
            'name' => 'Liter',
            'category' => \App\Models\Unit::CATEGORY_VOLUME,
        ]);

        $this->productA = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'output_unit_id' => $unitA->id,
            'name' => 'Madu Hutan Asli 500ml',
            'slug' => 'madu-hutan-asli-500ml',
            'selling_price' => 85000,
            'stock' => 50,
            'is_active' => true,
            'status' => 'published',
        ]);

        $this->productB = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessB->id,
            'output_unit_id' => $unitB->id,
            'name' => 'Minyak Kelapa Murni 1L',
            'slug' => 'minyak-kelapa-murni-1l',
            'selling_price' => 60000,
            'stock' => 30,
            'is_active' => true,
            'status' => 'published',
        ]);
    }

    public function test_can_browse_public_marketplace_catalog(): void
    {
        // 1. Home Feed
        $homeRes = $this->getJson('/api/v1/marketplace/home');
        $homeRes->assertStatus(200);
        $homeRes->assertJsonStructure([
            'success',
            'banners',
            'categories',
            'featured_stores',
            'popular_products',
        ]);

        // 2. Search Products
        $searchRes = $this->getJson('/api/v1/marketplace/products?search=Madu');
        $searchRes->assertStatus(200);
        $this->assertCount(1, $searchRes->json('data'));
        $this->assertEquals('Madu Hutan Asli 500ml', $searchRes->json('data.0.name'));

        // 3. Product Detail
        $detailRes = $this->getJson("/api/v1/marketplace/products/{$this->productA->slug}");
        $detailRes->assertStatus(200);
        $this->assertEquals($this->productA->id, $detailRes->json('data.id'));
    }

    public function test_customer_auth_registration_and_login(): void
    {
        // 1. Register
        $regRes = $this->postJson('/api/v1/customer/auth/register', [
            'name' => 'Rina Kartika',
            'email' => 'rina@customer-marketplace.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $regRes->assertStatus(201);
        $this->assertNotEmpty($regRes->json('token'));
        $this->assertEquals('Rina Kartika', $regRes->json('customer.name'));

        // 2. Login
        $loginRes = $this->postJson('/api/v1/customer/auth/login', [
            'login' => 'rina@customer-marketplace.com',
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200);
        $this->assertNotEmpty($loginRes->json('token'));
    }

    public function test_customer_wishlist_toggle_and_list(): void
    {
        $customer = GlobalCustomer::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@customer.com',
            'password' => bcrypt('secret123'),
        ]);

        Sanctum::actingAs($customer);

        // 1. Toggle Add to wishlist
        $toggleAdd = $this->postJson('/api/v1/customer/wishlist/toggle', [
            'product_id' => $this->productA->id,
        ]);

        $toggleAdd->assertStatus(201);
        $toggleAdd->assertJson([
            'status' => 'success',
            'action' => 'added',
            'is_wishlisted' => true,
        ]);

        // 2. Fetch Wishlist
        $listRes = $this->getJson('/api/v1/customer/wishlist');
        $listRes->assertStatus(200);
        $this->assertCount(1, $listRes->json('data'));

        // 3. Toggle Remove from wishlist
        $toggleRemove = $this->postJson('/api/v1/customer/wishlist/toggle', [
            'product_id' => $this->productA->id,
        ]);

        $toggleRemove->assertStatus(200);
        $toggleRemove->assertJson([
            'status' => 'success',
            'action' => 'removed',
            'is_wishlisted' => false,
        ]);
    }

    public function test_multi_merchant_cart_and_anti_idor_order_protection(): void
    {
        $customer1 = GlobalCustomer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@customer.com',
            'password' => bcrypt('secret123'),
        ]);

        $customer2 = GlobalCustomer::create([
            'name' => 'Eko Prasetyo',
            'email' => 'eko@customer.com',
            'password' => bcrypt('secret123'),
        ]);

        Sanctum::actingAs($customer1);

        // 1. Add item from Store A to Cart
        $cartAddRes = $this->postJson("/api/v1/customer/cart/{$this->businessA->slug}", [
            'product_id' => $this->productA->id,
            'quantity' => 2,
        ]);

        $cartAddRes->assertStatus(200);
        $cartAddRes->assertJson([
            'success' => true,
        ]);

        // 2. View Grouped Cart
        $cartListRes = $this->getJson('/api/v1/customer/cart');
        $cartListRes->assertStatus(200);
        $this->assertCount(1, $cartListRes->json('data.carts'));
        $this->assertEquals($this->businessA->name, $cartListRes->json('data.carts.0.business.name'));

        // 3. Order created by Customer 1
        $order = CommerceOrder::create([
            'business_id' => $this->businessA->id,
            'global_customer_id' => $customer1->id,
            'order_number' => 'ORD-CUST1-001',
            'tracking_token' => Str::random(32),
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'customer_name' => $customer1->name,
            'customer_phone' => '08123456789',
            'subtotal' => 170000,
            'total_amount' => 180000,
        ]);

        // Customer 1 can view their own order
        $cust1ViewRes = $this->getJson("/api/v1/customer/orders/{$order->id}");
        $cust1ViewRes->assertStatus(200);
        $this->assertEquals('ORD-CUST1-001', $cust1ViewRes->json('data.order_number'));

        // 4. Anti-IDOR Protection: Customer 2 attempts to view Customer 1's order
        Sanctum::actingAs($customer2);
        $cust2IntrusionRes = $this->getJson("/api/v1/customer/orders/{$order->id}");
        // Must be rejected with 403 Forbidden or 404 Not Found
        $this->assertTrue(in_array($cust2IntrusionRes->status(), [403, 404], true));
    }
}
