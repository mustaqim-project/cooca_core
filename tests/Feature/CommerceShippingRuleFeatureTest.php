<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Context;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceShippingRuleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private CommercePaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Owner Logistik',
            'email' => 'logistik@tokoberkah.com',
            'phone' => '081299998888',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Toko Logistik UMKM',
            'slug' => 'toko-logistik',
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
            'name' => 'Gudang Utama',
            'is_primary' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Paket Sembako Berkah',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 100000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT Toko Logistik',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'min_order_amount' => 0,
            'order_auto_cancel_minutes' => 60,
        ]);
    }

    public function test_public_shipping_calculate_api_returns_options(): void
    {
        CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Instan Kota',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 15000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Bebas Ongkir Min. 150rb',
            'rule_type' => CommerceShippingRule::TYPE_FREE_THRESHOLD,
            'rate_amount' => 20000,
            'min_order_for_free' => 150000,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Case 1: subtotal 100000 (does not meet free shipping threshold)
        $response = $this->postJson("/b/{$this->business->slug}/shipping/calculate", [
            'subtotal' => 100000,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.shipping_fee', 15000);
        $response->assertJsonPath('data.is_free', false);

        // Case 2: subtotal 200000 (qualifies for free shipping)
        $responseFree = $this->postJson("/b/{$this->business->slug}/shipping/calculate", [
            'subtotal' => 200000,
        ]);

        $responseFree->assertOk();
        $responseFree->assertJsonPath('data.shipping_fee', 0);
        $responseFree->assertJsonPath('data.is_free', true);
    }

    public function test_checkout_with_merchant_delivery_applies_shipping_fee(): void
    {
        $rule = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Flat Rp 12.000',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 12000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $payload = [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081233334444',
            'customer_email' => 'budi@example.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Merdeka No. 45, Jakarta Selatan',
            'shipping_rule_id' => $rule->id,
            'payment_method_id' => $this->paymentMethod->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $customer = \App\Models\GlobalCustomer::create([
            'google_id' => 'google-user-shipping',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081233334444',
            'phone_verified_at' => now(),
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $order = CommerceOrder::withoutGlobalScopes()->where('business_id', $this->business->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(100000, (float) $order->subtotal);
        $this->assertEquals(12000, (float) $order->shipping_fee);
        $this->assertEquals(112000, (float) $order->total_amount);
        $this->assertEquals($rule->id, $order->shipping_rule_id);
    }

    public function test_merchant_can_manage_shipping_rules(): void
    {
        $acting = $this->actingAs($this->merchantUser)->withSession([
            'active_business_id' => $this->business->id,
            'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
        ]);

        // 1. View rules page
        $acting->get('/storefront/shipping')->assertOk();

        // 2. Create flat rule
        $response = $acting->from('/storefront/shipping')->post('/storefront/shipping', [
            'name' => 'Kurir Area Kecamatan',
            'rule_type' => 'flat',
            'rate_amount' => 10000,
            'is_active' => 1,
            'sort_order' => 1,
        ]);
        $response->assertRedirect('/storefront/shipping');

        $rule = CommerceShippingRule::where('name', 'Kurir Area Kecamatan')->first();
        $this->assertNotNull($rule);
        $this->assertEquals(10000, (float) $rule->rate_amount);

        // 3. Toggle rule active state (becomes false)
        $toggleRes = $acting->from('/storefront/shipping')->post("/storefront/shipping/{$rule->id}/toggle");
        $toggleRes->assertRedirect('/storefront/shipping');
        $rule->refresh();
        $this->assertFalse((bool) $rule->is_active);

        // 4. Update rule (reactivate and update amount)
        $acting->from('/storefront/shipping')->put("/storefront/shipping/{$rule->id}", [
            'name' => 'Kurir Area Kecamatan Super Cepat',
            'rule_type' => 'flat',
            'rate_amount' => 15000,
            'is_active' => 1,
            'sort_order' => 1,
        ])->assertRedirect('/storefront/shipping');

        $rule->refresh();
        $this->assertEquals('Kurir Area Kecamatan Super Cepat', $rule->name);
        $this->assertEquals(15000, (float) $rule->rate_amount);
        $this->assertTrue((bool) $rule->is_active);

        // 5. Delete rule
        $acting->from('/storefront/shipping')->delete("/storefront/shipping/{$rule->id}")->assertRedirect('/storefront/shipping');
        $this->assertDatabaseMissing('commerce_shipping_rules', ['id' => $rule->id]);
    }
}
