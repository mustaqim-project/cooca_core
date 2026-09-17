<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommercePaymentProof;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CommerceStorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private CommercePaymentMethod $paymentMethod;
    private GlobalCustomer $globalCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Pemilik Toko',
            'email' => 'owner@tokoberkah.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Toko Berkah UMKM',
            'slug' => 'toko-berkah',
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
            'name' => 'Toko Utama',
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
            'name' => 'Kopi Arabika Premium 250g',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 75000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT Toko Berkah',
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

        $this->globalCustomer = GlobalCustomer::create([
            'google_id' => 'google-user-storefront',
            'name' => 'Ahmad Pelanggan',
            'email' => 'ahmad@example.com',
            'phone' => '081298765432',
            'phone_verified_at' => now(),
        ]);
    }

    public function test_unauthenticated_checkout_fails_with_401(): void
    {
        $response = $this->postJson("/b/{$this->business->slug}/checkout", []);
        $response->assertStatus(401);
    }

    public function test_public_checkout_creates_order_and_reserves_stock(): void
    {
        $payload = [
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'customer_email' => 'ahmad@example.com',
            'fulfillment_type' => 'pickup',
            'payment_method_id' => $this->paymentMethod->id,
            'notes' => 'Tolong bungkus rapi ya.',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Giling halus',
                ],
            ],
        ];

        $response = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'order' => [
                'id',
                'order_number',
                'tracking_token',
                'total_amount',
                'status',
                'tracking_url',
            ],
        ]);

        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '6281298765432',
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'total_amount' => 150000,
        ]);

        $this->assertDatabaseHas('commerce_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 75000,
            'subtotal' => 150000,
        ]);

        // Verify reserved stock allocation
        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(10, (float) $stock->quantity);
        $this->assertEquals(2, (float) $stock->reserved_quantity);
        $this->assertEquals(8, (float) $stock->available_quantity);

        // Verify CRM customer auto-creation
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'phone' => '6281298765432',
        ]);
    }

    public function test_checkout_fails_when_quantity_exceeds_available_stock(): void
    {
        $payload = [
            'customer_name' => 'Budi Borong',
            'customer_phone' => '081300001111',
            'fulfillment_type' => 'pickup',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15, // Only 10 available
                ],
            ],
        ];

        $response = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);

        // Verify no order was created and stock is untouched
        $this->assertDatabaseCount('commerce_orders', 0);
        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(0, (float) $stock->reserved_quantity);
        $this->assertEquals(10, (float) $stock->available_quantity);
    }

    public function test_customer_can_view_order_tracking_page(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20260915-0001',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'fulfillment_type' => 'pickup',
            'payment_method_id' => $this->paymentMethod->id,
            'customer_name' => 'Dewi Lestari',
            'customer_phone' => '081234567890',
            'subtotal' => 75000,
            'total_amount' => 75000,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 75000,
            'quantity' => 1,
            'subtotal' => 75000,
        ]);

        $response = $this->get("/b/{$this->business->slug}/order/{$order->tracking_token}");

        $response->assertStatus(200);
        $response->assertSeeText($order->order_number);
        $response->assertSeeText('Dewi Lestari');
        $response->assertSeeText('Rp 75.000');
        $response->assertSeeText('BCA');
    }

    public function test_customer_can_upload_payment_proof_to_private_storage(): void
    {
        Storage::fake('local');

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20260915-0002',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'fulfillment_type' => 'pickup',
            'payment_method_id' => $this->paymentMethod->id,
            'customer_name' => 'Dewi Lestari',
            'customer_phone' => '081234567890',
            'subtotal' => 75000,
            'total_amount' => 75000,
        ]);

        $file = UploadedFile::fake()->image('struk_bca.jpg', 600, 800);

        $response = $this->post("/b/{$this->business->slug}/order/{$order->tracking_token}/proof", [
            'payment_proof' => $file,
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Dewi Lestari',
        ]);

        $response->assertRedirect();

        // Verify order status transitioned
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PROOF_SUBMITTED, $order->status);

        // Verify payment proof created
        $this->assertDatabaseHas('commerce_payment_proofs', [
            'commerce_order_id' => $order->id,
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Dewi Lestari',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $proof = CommercePaymentProof::where('commerce_order_id', $order->id)->first();
        Storage::disk('local')->assertExists($proof->file_path);
        // Verify path is in private directory
        $this->assertStringContainsString('commerce_proofs', $proof->file_path);
    }

    public function test_merchant_can_verify_payment_and_stock_is_committed(): void
    {
        Storage::fake('local');

        // Create reserved stock of 2
        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $stock->update(['reserved_quantity' => 2]);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20260915-0003',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'fulfillment_type' => 'pickup',
            'payment_method_id' => $this->paymentMethod->id,
            'customer_name' => 'Rian Hidayat',
            'customer_phone' => '081211112222',
            'subtotal' => 150000,
            'total_amount' => 150000,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 75000,
            'quantity' => 2,
            'subtotal' => 150000,
        ]);

        $proof = CommercePaymentProof::create([
            'business_id' => $this->business->id,
            'commerce_order_id' => $order->id,
            'file_path' => 'private/commerce_proofs/' . $this->business->id . '/test.jpg',
            'file_size_kb' => 1024,
            'mime_type' => 'image/jpeg',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post("/storefront/orders/{$order->id}/verify-payment");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PAID, $order->status);

        $proof->refresh();
        $this->assertEquals(CommercePaymentProof::STATUS_VERIFIED, $proof->status);
        $this->assertEquals($this->merchantUser->id, $proof->verified_by);

        // Verify stock is committed (physical quantity decremented by 2, reserved decremented to 0)
        $stock->refresh();
        $this->assertEquals(8, (float) $stock->quantity);
        $this->assertEquals(0, (float) $stock->reserved_quantity);
        $this->assertEquals(8, (float) $stock->available_quantity);

        // Verify stock movement recorded
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'movement_type' => StockMovement::TYPE_ONLINE_SALE,
            'quantity_change' => -2,
        ]);
    }

    public function test_merchant_can_reject_payment_proof(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20260915-0004',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'fulfillment_type' => 'pickup',
            'customer_name' => 'Eko Prasetyo',
            'customer_phone' => '081299990000',
            'subtotal' => 75000,
            'total_amount' => 75000,
        ]);

        $proof = CommercePaymentProof::create([
            'business_id' => $this->business->id,
            'commerce_order_id' => $order->id,
            'file_path' => 'private/commerce_proofs/' . $this->business->id . '/test.jpg',
            'file_size_kb' => 1024,
            'mime_type' => 'image/jpeg',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post("/storefront/orders/{$order->id}/reject-payment", [
                'rejection_reason' => 'Nominal transfer tidak sesuai (kurang Rp 20.000).',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PAYMENT_REJECTED, $order->status);

        $proof->refresh();
        $this->assertEquals(CommercePaymentProof::STATUS_REJECTED, $proof->status);
        $this->assertEquals('Nominal transfer tidak sesuai (kurang Rp 20.000).', $proof->rejection_reason);
    }

    public function test_multi_business_isolation_for_storefront_orders(): void
    {
        // Another business and owner
        $otherUser = User::create([
            'name' => 'Other Owner',
            'email' => 'other@tokolain.com',
            'phone' => '081299998888',
            'password' => bcrypt('password123'),
        ]);
        $otherUser->forceFill(['email_verified_at' => now()])->save();

        $otherBusiness = Business::create([
            'name' => 'Toko Sebelah',
            'slug' => 'toko-sebelah',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $otherBusiness->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $otherUser->update(['active_business_id' => $otherBusiness->id]);

        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20260915-0005',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'fulfillment_type' => 'pickup',
            'customer_name' => 'Korban IDOR',
            'customer_phone' => '081200000000',
            'subtotal' => 75000,
            'total_amount' => 75000,
        ]);

        // User from other business tries to view order of business A
        $response = $this->actingAs($otherUser)
            ->withSession([
                'active_business_id' => $otherBusiness->id,
                'auth_wa_otp_verified_user_id' => $otherUser->id,
            ])
            ->get("/storefront/orders/{$order->id}");

        $verifyResponse = $this->actingAs($otherUser)
            ->withSession([
                'active_business_id' => $otherBusiness->id,
                'auth_wa_otp_verified_user_id' => $otherUser->id,
            ])
            ->post("/storefront/orders/{$order->id}/verify-payment");

        // Both requests must be denied (403 Forbidden or 404 Not Found via BusinessScope)
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404], true));
        $this->assertTrue(in_array($verifyResponse->getStatusCode(), [403, 404], true));
    }

    public function test_checkout_rejects_zero_or_negative_quantity(): void
    {
        $payloadZero = [
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'fulfillment_type' => 'pickup',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 0,
                ],
            ],
        ];

        $responseZero = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payloadZero);

        $responseZero->assertStatus(422);
        $responseZero->assertJsonValidationErrors(['items.0.quantity']);

        $payloadNegative = [
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'fulfillment_type' => 'pickup',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => -5,
                ],
            ],
        ];

        $responseNegative = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payloadNegative);

        $responseNegative->assertStatus(422);
        $responseNegative->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_request_order_rejects_zero_or_negative_quantity(): void
    {
        $payloadZero = [
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'fulfillment_type' => 'pickup',
            'items' => [
                [
                    'product_name' => 'Custom Request Item',
                    'quantity' => 0,
                ],
            ],
        ];

        $responseZero = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/request-order", $payloadZero);

        $responseZero->assertStatus(422);
        $responseZero->assertJsonValidationErrors(['items.0.quantity']);

        $payloadNegative = [
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'fulfillment_type' => 'pickup',
            'items' => [
                [
                    'product_name' => 'Custom Request Item',
                    'quantity' => -1,
                ],
            ],
        ];

        $responseNegative = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/request-order", $payloadNegative);

        $responseNegative->assertStatus(422);
        $responseNegative->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_customer_po_rejects_zero_or_negative_item_and_batch_quantity(): void
    {
        $payloadZeroItem = [
            'customer_name' => 'PT Pengadaan Nusantara',
            'customer_phone' => '081298765432',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 0,
                    'unit_price' => 75000,
                ],
            ],
            'batches' => [
                [
                    'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
                    'quantity' => 1,
                ],
            ],
        ];

        $responseZeroItem = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/customer-po", $payloadZeroItem);

        $responseZeroItem->assertStatus(422);
        $responseZeroItem->assertJsonValidationErrors(['items.0.quantity']);

        $payloadZeroBatch = [
            'customer_name' => 'PT Pengadaan Nusantara',
            'customer_phone' => '081298765432',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 5,
                    'unit_price' => 75000,
                ],
            ],
            'batches' => [
                [
                    'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
                    'quantity' => 0,
                ],
            ],
        ];

        $responseZeroBatch = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/customer-po", $payloadZeroBatch);

        $responseZeroBatch->assertStatus(422);
        $responseZeroBatch->assertJsonValidationErrors(['batches.0.quantity']);
    }

    public function test_cart_service_rejects_zero_or_negative_quantity(): void
    {
        $cart = \App\Models\CustomerCart::create([
            'global_customer_id' => $this->globalCustomer->id,
            'business_id' => $this->business->id,
        ]);

        $cartService = new \App\Domain\Commerce\CartService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Jumlah item yang dipesan harus lebih dari 0.');

        $cartService->addItem($cart, $this->product, 0);
    }

    public function test_payment_proof_status_helpers_and_merchant_order_show_view(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-PROOF-001',
            'tracking_token' => Str::random(32),
            'order_type' => CommerceOrder::TYPE_SCHEDULED_ORDER,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_PICKUP,
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'payment_status' => CommerceOrder::PAYMENT_VERIFYING,
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081298765432',
            'subtotal' => 75000,
            'total_amount' => 75000,
            'scheduled_date' => now()->addDay()->toDateString(),
        ]);

        $proof = CommercePaymentProof::create([
            'business_id' => $this->business->id,
            'commerce_order_id' => $order->id,
            'file_path' => 'proofs/dummy.jpg',
            'file_size_kb' => 120,
            'mime_type' => 'image/jpeg',
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Ahmad',
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $this->assertTrue($proof->isPending());
        $this->assertFalse($proof->isVerified());
        $this->assertFalse($proof->isRejected());
        $this->assertSame('Menunggu Verifikasi', $proof->status_label);

        $proof->update(['status' => CommercePaymentProof::STATUS_VERIFIED]);
        $this->assertTrue($proof->isVerified());
        $this->assertSame('Terverifikasi', $proof->status_label);

        $proof->update(['status' => CommercePaymentProof::STATUS_REJECTED]);
        $this->assertTrue($proof->isRejected());
        $this->assertSame('Ditolak', $proof->status_label);

        // Reset to pending and check merchant order show page view
        $proof->update(['status' => CommercePaymentProof::STATUS_PENDING]);

        $response = $this->actingAs($this->merchantUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('storefront.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Tolak Bukti Transfer');
        $response->assertSee('ORD-PROOF-001');
    }
}
