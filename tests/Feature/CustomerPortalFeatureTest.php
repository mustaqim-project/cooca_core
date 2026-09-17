<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommercePaymentProof;
use App\Models\CommerceStoreSetting;
use App\Models\Customer;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerPortalFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Location $location;
    private Product $product;
    private Customer $customer;
    private GlobalCustomer $globalCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $merchant = User::create([
            'name' => 'Owner Merchant',
            'email' => 'merchant@cooca.test',
            'phone' => '08123456780',
            'password' => bcrypt('password123'),
        ]);
        $merchant->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Toko Busana Cantik',
            'slug' => 'toko-busana-cantik',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'is_primary' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_direct_checkout' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create(['code' => 'pcs', 'name' => 'Pieces', 'category' => 'quantity']);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'code' => 'PRD-001',
            'name' => 'Kemeja Katun Putih',
            'slug' => 'kemeja-katun-putih',
            'output_unit_id' => $pcs->id,
            'selling_price' => 150000,
            'base_cost' => 80000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Rian Pratama',
            'phone' => '081299112233',
            'email' => 'rian@customer.id',
            'password' => Hash::make('secret123'),
            'shipping_address' => 'Jl. Kebon Jeruk No. 25, Jakarta Barat',
            'points_balance' => 200,
            'membership_tier' => 'silver',
        ]);

        $this->globalCustomer = GlobalCustomer::create([
            'google_id' => 'google-user-12345',
            'name' => 'Rian Pratama',
            'phone' => '081299112233',
            'email' => 'rian@customer.id',
            'phone_verified_at' => now(),
            'shipping_address' => 'Jl. Kebon Jeruk No. 25, Jakarta Barat',
        ]);
    }

    public function test_login_page_renders_credentials_form_and_google_auth_button(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertStatus(200);
        $response->assertSee('Lanjutkan dengan Google');
        $response->assertSee('Masuk Akun');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Akun Seeder Demo');
    }

    public function test_customer_can_login_with_email_and_password_without_google(): void
    {
        $globalCustomer = GlobalCustomer::create([
            'name' => 'Ahmad Mandiri',
            'email' => 'mandiri_test@cooca.id',
            'phone' => '081234567891',
            'password' => Hash::make('password123'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('customer.login.submit'), [
            'login' => 'mandiri_test@cooca.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($globalCustomer, 'customer');
    }

    public function test_customer_can_login_with_phone_and_password_without_google(): void
    {
        $globalCustomer = GlobalCustomer::create([
            'name' => 'Budi BCA',
            'email' => 'bca_test@cooca.id',
            'phone' => '081987654329',
            'password' => Hash::make('password123'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('customer.login.submit'), [
            'login' => '081987654329',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($globalCustomer, 'customer');
    }

    public function test_customer_login_redirects_to_custom_url_with_batch_and_group(): void
    {
        $globalCustomer = GlobalCustomer::create([
            'name' => 'Kantor Mandiri User',
            'email' => 'kantor_user@cooca.id',
            'phone' => '081299998888',
            'password' => Hash::make('password123'),
            'phone_verified_at' => now(),
        ]);

        $redirectTarget = '/dapur-sedap-rasa?batch=batch-1-jumat&group=KANTOR-MANDIRI-123';

        $response = $this->post(route('customer.login.submit'), [
            'login' => 'kantor_user@cooca.id',
            'password' => 'password123',
            'redirect_to' => $redirectTarget,
        ]);

        $response->assertRedirect($redirectTarget);
        $this->assertAuthenticatedAs($globalCustomer, 'customer');
    }

    public function test_authenticated_global_customer_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->globalCustomer, 'customer')->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rian Pratama');
    }

    public function test_customer_from_store_a_can_shop_in_store_b_without_re_registering(): void
    {
        // Second store (Toko B)
        $storeB = Business::create([
            'name' => 'Toko Elektronik Makmur',
            'slug' => 'toko-elektronik-makmur',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first();
        $prodB = Product::create([
            'business_id' => $storeB->id,
            'code' => 'ELC-001',
            'name' => 'Kabel Data Type-C',
            'slug' => 'kabel-data-type-c',
            'output_unit_id' => $pcs->id,
            'selling_price' => 50000,
            'base_cost' => 20000,
            'is_active' => true,
        ]);

        // Customer (logged in globally) shops at Toko A
        $this->actingAs($this->globalCustomer, 'customer')
            ->post(route('customer.cart.add', ['slug' => $this->business->slug]), [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ])
            ->assertRedirect();

        // The exact same customer shops at Toko B without registering again
        $this->actingAs($this->globalCustomer, 'customer')
            ->post(route('customer.cart.add', ['slug' => $storeB->slug]), [
                'product_id' => $prodB->id,
                'quantity'   => 1,
            ])
            ->assertRedirect();

        // View multi-store cart: both stores appear with their respective products
        $cartView = $this->actingAs($this->globalCustomer, 'customer')->get(route('customer.cart'));
        $cartView->assertStatus(200);
        $cartView->assertSee('Toko Busana Cantik');
        $cartView->assertSee('Kemeja Katun Putih');
        $cartView->assertSee('Toko Elektronik Makmur');
        $cartView->assertSee('Kabel Data Type-C');

        // Clicking checkout for Toko B redirects to Toko B's landing page
        $checkoutB = $this->actingAs($this->globalCustomer, 'customer')
            ->post(route('customer.cart.checkout', ['slug' => $storeB->slug]));
        $checkoutB->assertRedirect(route('public.business.landing.legacy', ['slug' => $storeB->slug]));
    }

    public function test_checkout_and_reservation_require_verified_otp(): void
    {
        $unverifiedCustomer = GlobalCustomer::create([
            'google_id' => 'unverified-google-1',
            'name' => 'Budi Santoso',
            'phone' => '081233445566',
            'email' => 'budi@test.id',
            'phone_verified_at' => null, // Not verified!
        ]);

        // Attempting checkout via AJAX returns 403 with requires_otp
        $res = $this->actingAs($unverifiedCustomer, 'customer')
            ->postJson(route('public.storefront.checkout', ['slug' => $this->business->slug]), [
                'customer_name' => 'Budi Santoso',
                'customer_phone' => '081233445566',
                'fulfillment_type' => 'pickup',
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 1],
                ],
            ]);

        $res->assertStatus(403);
        $res->assertJson(['requires_otp' => true]);

        // Submitting OTP 123456 in local environment verifies the customer
        $verifyRes = $this->actingAs($unverifiedCustomer, 'customer')
            ->post(route('customer.otp.verify'), ['otp' => '123456']);
        $verifyRes->assertRedirect();

        $unverifiedCustomer->refresh();
        $this->assertNotNull($unverifiedCustomer->phone_verified_at);
    }

    public function test_unauthenticated_user_cannot_access_customer_dashboard(): void
    {
        $response = $this->get(route('customer.dashboard'));

        $response->assertRedirect(route('customer.login'));
    }

    public function test_customer_dashboard_displays_stats_and_points(): void
    {
        // Create an order for this customer
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20260915-0001',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_COMPLETED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_PICKUP,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_email' => $this->customer->email,
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 150000,
            'shipping_cost' => 0,
            'total_amount' => 150000,
            'tracking_token' => Str::random(64),
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rian Pratama');
        $response->assertSee('Poin Reward');
        $response->assertSee('200');
        $response->assertSee('ORD-20260915-0001');
    }

    public function test_customer_can_view_order_history_with_status_filtering(): void
    {
        CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20260915-PND1',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 300000,
            'shipping_cost' => 15000,
            'total_amount' => 315000,
            'tracking_token' => Str::random(64),
        ]);

        CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20260915-CMP1',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_COMPLETED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_PICKUP,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 150000,
            'shipping_cost' => 0,
            'total_amount' => 150000,
            'tracking_token' => Str::random(64),
        ]);

        $responseAll = $this->actingAs($this->customer, 'customer')->get(route('customer.orders'));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('ORD-20260915-PND1');
        $responseAll->assertSee('ORD-20260915-CMP1');

        $responsePending = $this->actingAs($this->customer, 'customer')->get(route('customer.orders', ['status' => 'pending_payment']));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('ORD-20260915-PND1');
        $responsePending->assertDontSee('ORD-20260915-CMP1');
    }

    public function test_customer_can_view_order_detail_and_tracking_timeline(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20260915-DET1',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_email' => $this->customer->email,
            'shipping_address' => $this->customer->shipping_address,
            'subtotal' => 150000,
            'shipping_cost' => 10000,
            'total_amount' => 160000,
            'tracking_token' => Str::random(64),
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('customer.orders.detail', $order));

        $response->assertStatus(200);
        $response->assertSee('ORD-20260915-DET1');
        $response->assertSee('Kemeja Katun Putih');
        $response->assertSee('Bukti Transfer Pembayaran');
    }

    public function test_customer_cannot_view_order_belonging_to_another_customer(): void
    {
        $otherCustomer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Siti Nurhaliza',
            'phone' => '081200009999',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
        ]);

        $otherOrder = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $otherCustomer->id,
            'order_number' => 'ORD-20260915-OTH1',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_COMPLETED,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_PICKUP,
            'customer_name' => $otherCustomer->name,
            'customer_phone' => $otherCustomer->phone,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total_amount' => 100000,
            'tracking_token' => Str::random(64),
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('customer.orders.detail', $otherOrder));

        $response->assertStatus(404);
    }

    public function test_customer_can_upload_payment_proof(): void
    {
        Storage::fake('local');

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20260915-PRF1',
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'subtotal' => 150000,
            'shipping_cost' => 10000,
            'total_amount' => 160000,
            'tracking_token' => Str::random(64),
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 600)->size(200);

        $response = $this->actingAs($this->customer, 'customer')->post(
            route('customer.orders.upload_proof', $order),
            [
                'payment_proof' => $file,
                'sender_bank' => 'BCA',
                'sender_account_name' => 'Rian Pratama',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PROOF_SUBMITTED, $order->status);

        $this->assertDatabaseHas('commerce_payment_proofs', [
            'commerce_order_id' => $order->id,
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Rian Pratama',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);
    }

    public function test_customer_can_update_profile_and_shipping_address(): void
    {
        $response = $this->actingAs($this->globalCustomer, 'customer')->put(
            route('customer.profile.update'),
            [
                'name' => 'Rian Pratama Updated',
                'email' => 'rian.new@customer.id',
                'phone' => '081299112233',
                'shipping_address' => 'Jl. Kebon Jeruk Baru No. 100, RT 01 / RW 02',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->globalCustomer->refresh();
        $this->assertEquals('Rian Pratama Updated', $this->globalCustomer->name);
        $this->assertEquals('rian.new@customer.id', $this->globalCustomer->email);
        $this->assertEquals('Jl. Kebon Jeruk Baru No. 100, RT 01 / RW 02', $this->globalCustomer->shipping_address);
    }

    public function test_storefront_checkout_automatically_links_authenticated_customer(): void
    {
        $pm = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Toko Busana Cantik',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->globalCustomer, 'customer')->post(
            route('public.storefront.checkout', ['slug' => $this->business->slug]),
            [
                'customer_name' => 'Rian Pratama',
                'customer_phone' => '081299112233',
                'customer_email' => 'rian@customer.id',
                'fulfillment_type' => 'pickup',
                'payment_method_id' => $pm->id,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'customer_phone' => '6281299112233',
        ]);
    }
}
