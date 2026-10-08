<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentProof;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MobileMerchantOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Juragan Toko',
            'email' => 'juragan@merchant-test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Mitra Berkah',
            'slug' => 'toko-mitra-berkah',
            'email' => 'berkah@toko.com',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Robusta 250g',
            'slug' => 'kopi-robusta-250g',
            'selling_price' => 50000,
            'is_active' => true,
            'status' => 'published',
        ]);
    }

    public function test_guest_cannot_access_merchant_orders(): void
    {
        $response = $this->getJson('/api/v1/commerce/orders');
        $response->assertStatus(401);
    }

    public function test_can_list_merchant_storefront_orders(): void
    {
        Sanctum::actingAs($this->owner);

        CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-MERCHANT-01',
            'tracking_token' => Str::random(32),
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '08123456789',
            'subtotal' => 50000,
            'total_amount' => 60000,
        ]);

        $response = $this->getJson('/api/v1/commerce/orders?status=needs_verification', [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_verify_and_approve_payment_proof(): void
    {
        Sanctum::actingAs($this->owner);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-PROOF-01',
            'tracking_token' => Str::random(32),
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'customer_name' => 'Budi Pembeli',
            'customer_phone' => '08198765432',
            'subtotal' => 50000,
            'total_amount' => 50000,
        ]);

        $proof = CommercePaymentProof::create([
            'business_id' => $this->business->id,
            'commerce_order_id' => $order->id,
            'file_path' => 'proofs/test_transfer.jpg',
            'file_size_kb' => 150,
            'mime_type' => 'image/jpeg',
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Budi Pembeli',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $response = $this->postJson("/api/v1/commerce/orders/{$order->id}/verify-payment", [
            'action' => 'approve',
            'notes' => 'Dana transfer sudah masuk mutasi bank.',
        ], [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals(CommerceOrder::STATUS_PAID, $order->fresh()->status);
        $this->assertEquals(CommercePaymentProof::STATUS_VERIFIED, $proof->fresh()->status);
    }

    public function test_can_update_manual_waybill_tracking(): void
    {
        Sanctum::actingAs($this->owner);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-SHIP-01',
            'tracking_token' => Str::random(32),
            'status' => CommerceOrder::STATUS_PAID,
            'customer_name' => 'Citra Kirana',
            'customer_phone' => '08155566677',
            'subtotal' => 50000,
            'total_amount' => 60000,
        ]);

        $response = $this->postJson("/api/v1/commerce/orders/{$order->id}/waybill", [
            'waybill_id' => 'JNE1234567890ID',
            'courier_name' => 'JNE Express',
        ], [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $fresh = $order->fresh();
        $this->assertEquals('JNE1234567890ID', $fresh->shipping_waybill_id);
        $this->assertEquals(CommerceOrder::STATUS_SHIPPED, $fresh->status);
    }

    public function test_can_fetch_thermal_shipping_label_data(): void
    {
        Sanctum::actingAs($this->owner);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-THERMAL-01',
            'tracking_token' => Str::random(32),
            'status' => CommerceOrder::STATUS_PAID,
            'customer_name' => 'Doni Salman',
            'customer_phone' => '08122334455',
            'shipping_address' => 'Jl. Kemang Raya No. 10, Jakarta Selatan',
            'subtotal' => 50000,
            'total_amount' => 60000,
            'shipping_waybill_id' => 'SPX0987654321',
            'shipping_courier_name' => 'Shopee Xpress',
        ]);

        $response = $this->getJson("/api/v1/commerce/orders/{$order->id}/shipping-label-data", [
            'X-Business-Id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'order_number' => 'ORD-THERMAL-01',
                'barcode_value' => 'SPX0987654321',
                'recipient' => [
                    'name' => 'Doni Salman',
                    'phone' => '08122334455',
                ],
            ],
        ]);
    }
}
