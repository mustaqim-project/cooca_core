<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceScheduledOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private Product $product;
    private CommercePaymentMethod $paymentMethod;
    private CommerceStoreSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Pemilik Katering',
            'email' => 'katering@tokoberkah.com',
            'phone' => '081255556666',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Berkah Katering & Bakery',
            'slug' => 'berkah-katering',
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
            'name' => 'Dapur Utama',
            'is_primary' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Kotak',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Paket Nasi Kotak Ayam Bakar',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 35000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '9988776655',
            'account_holder' => 'Berkah Katering',
            'is_active' => true,
        ]);

        $this->setting = CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_request_order' => true,
            'allow_scheduled_order' => true,
            'lead_time_hours' => 24,
            'daily_order_quota' => 5,
            'order_auto_cancel_minutes' => 120,
        ]);

        $this->customer = \App\Models\GlobalCustomer::create([
            'google_id' => 'google-user-scheduled',
            'name' => 'Ibu Ratna',
            'email' => 'ratna@example.com',
            'phone' => '081234567890',
            'phone_verified_at' => now(),
        ]);
    }

    public function test_customer_can_create_scheduled_order(): void
    {
        $targetDate = Carbon::now()->addDays(2)->format('Y-m-d');

        $payload = [
            'customer_name' => 'Ibu Ratna',
            'customer_phone' => '081234567890',
            'customer_email' => 'ratna@example.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Gedung Pertemuan Graha Lestari',
            'scheduled_date' => $targetDate,
            'scheduled_time_slot' => '11:00 - 13:00',
            'payment_method_id' => $this->paymentMethod->id,
            'notes' => 'Acara syukuran keluarga',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 30,
                    'notes' => 'Sambal dipisah',
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $order = CommerceOrder::withoutGlobalScopes()->where('business_id', $this->business->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(CommerceOrder::TYPE_SCHEDULED_ORDER, $order->order_type);
        $this->assertEquals(CommerceOrder::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertEquals($targetDate, $order->scheduled_date->format('Y-m-d'));
        $this->assertEquals('11:00 - 13:00', $order->scheduled_time_slot);
        $this->assertEquals(1050000, (float) $order->subtotal);

        // Verify stock reservation
        $stock = InventoryStock::withoutGlobalScopes()
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(30, (float) $stock->reserved_quantity);
    }

    public function test_scheduled_order_fails_if_violating_lead_time(): void
    {
        // Setting requires 24 hours lead time. Try ordering for today (within 24h)
        $invalidDate = Carbon::now()->format('Y-m-d');

        $payload = [
            'customer_name' => 'Pak Hendra',
            'customer_phone' => '081277778888',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => $invalidDate,
            'payment_method_id' => $this->paymentMethod->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('waktu persiapan minimum', $response->json('message'));
    }

    public function test_scheduled_order_fails_when_daily_quota_is_exceeded(): void
    {
        $targetDate = Carbon::now()->addDays(3)->format('Y-m-d');

        // Set daily quota to 2
        $this->setting->update(['daily_order_quota' => 2]);

        // Create 2 existing orders on that date
        for ($i = 1; $i <= 2; $i++) {
            CommerceOrder::create([
                'business_id' => $this->business->id,
                'location_id' => $this->location->id,
                'order_number' => "ORD-EXISTING-{$i}",
                'tracking_token' => Str::random(64),
                'order_type' => CommerceOrder::TYPE_SCHEDULED_ORDER,
                'status' => CommerceOrder::STATUS_PAID,
                'customer_name' => "Customer {$i}",
                'customer_phone' => "08129999000{$i}",
                'scheduled_date' => $targetDate,
                'subtotal' => 100000,
                'total_amount' => 100000,
            ]);
        }

        // Try to place the 3rd order
        $payload = [
            'customer_name' => 'Budi Kuota',
            'customer_phone' => '081244445555',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => $targetDate,
            'payment_method_id' => $this->paymentMethod->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Kapasitas kuota pesanan', $response->json('message'));
    }

    public function test_customer_can_submit_request_order(): void
    {
        $payload = [
            'customer_name' => 'PT Makmur Sentosa',
            'customer_phone' => '081288889999',
            'customer_email' => 'purchasing@makmursentosa.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Kawasan Industri MM2100 Blok C-4',
            'scheduled_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'notes' => 'Permintaan katering prasmanan gathering 100 orang tema masakan Nusantara',
            'items' => [
                [
                    'product_id' => null,
                    'product_name' => 'Paket Prasmanan Nusantara Lengkap',
                    'quantity' => 100,
                    'notes' => 'Menu: Rendang, Gulai Ikan Kakap, Sayur Daun Singkong, Es Doger',
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/request-order", $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $order = CommerceOrder::withoutGlobalScopes()
            ->where('business_id', $this->business->id)
            ->where('customer_phone', '6281288889999')
            ->first();

        $this->assertNotNull($order);
        $this->assertEquals(CommerceOrder::TYPE_REQUEST_ORDER, $order->order_type);
        $this->assertEquals(CommerceOrder::STATUS_PENDING_REVIEW, $order->status);
        $this->assertEquals(CommerceOrder::PAYMENT_UNPAID, $order->payment_status);
        $this->assertCount(1, $order->items);
        $this->assertEquals('Paket Prasmanan Nusantara Lengkap', $order->items->first()->product_name);
    }

    public function test_merchant_can_review_and_quote_request_order(): void
    {
        // 1. Create a Request Order from customer
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'REQ-20260915-0001',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_REQUEST_ORDER,
            'status' => CommerceOrder::STATUS_PENDING_REVIEW,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'customer_name' => 'Ibu Dewi',
            'customer_phone' => '6281233332222',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Anggrek No. 12, Bekasi Barat',
            'subtotal' => 0,
            'total_amount' => 0,
        ]);

        $item = $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 50,
            'unit_price' => 0,
            'subtotal' => 0,
            'notes' => 'Pesanan khusus syukuran kantor',
        ]);

        $acting = $this->actingAs($this->merchantUser)->withSession([
            'active_business_id' => $this->business->id,
            'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
        ]);

        // 2. Merchant submits price quotation
        $quotePayload = [
            'items' => [
                [
                    'id' => $item->id,
                    'unit_price' => 32000, // Diskon spesial partai besar
                    'quantity' => 50,
                ],
            ],
            'shipping_cost' => 50000,
            'payment_method_id' => $this->paymentMethod->id,
            'notes' => 'Harga sudah termasuk ongkos kirim dan tim pelayan.',
        ];

        $response = $acting->from("/storefront/orders/{$order->id}")
            ->post("/storefront/orders/{$order->id}/quote", $quotePayload);

        $response->assertRedirect("/storefront/orders/{$order->id}");
        $response->assertSessionHas('success');

        // 3. Verify order transitions to pending_payment with updated totals and stock reservation
        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertEquals(1600000, (float) $order->subtotal); // 50 * 32.000
        $this->assertEquals(50000, (float) $order->shipping_cost);
        $this->assertEquals(1650000, (float) $order->total_amount);
        $this->assertNotNull($order->reserved_until);

        // Verify stock was reserved for physical goods
        $stock = InventoryStock::withoutGlobalScopes()->where('product_id', $this->product->id)->first();
        $this->assertEquals(50, (float) $stock->reserved_quantity);
    }
}
