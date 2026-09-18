<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BiteshipShippingIntegrationTest extends TestCase
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
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $unit = Unit::first();

        $this->user = User::create([
            'name' => 'Biteship Merchant',
            'email' => 'merchant_biteship@example.com',
            'email_verified_at' => now(),
            'phone' => '081234567899',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Biteship Store Official',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'slug' => 'biteship-store-official',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat',
            'is_active' => true,
            'is_primary' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Buku Catatan Premium',
            'selling_price' => 50000,
            'base_cost' => 30000,
            'output_unit_id' => $unit?->id,
            'weight_grams' => 250,
            'is_active' => true,
            'type' => 'goods',
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_delivery' => true,
            'allow_pickup' => true,
            'origin_contact_name' => 'Admin Gudang',
            'origin_contact_phone' => '081234567890',
            'origin_address' => 'Plaza Senayan, Jalan Asia Afrika No 8, Gelora, Tanah Abang',
            'origin_postal_code' => '10270',
            'origin_latitude' => -6.2253,
            'origin_longitude' => 106.7993,
            'biteship_enabled_couriers' => ['jne', 'sicepat', 'jnt'],
        ]);
    }

    public function test_owner_can_update_origin_address_and_biteship_couriers(): void
    {
        $payload = [
            'origin_contact_name' => 'Logistik Manager',
            'origin_contact_phone' => '081987654321',
            'origin_address' => 'Jl. Jend. Sudirman Kav 52-53, Senayan',
            'origin_postal_code' => '12190',
            'origin_latitude' => -6.2241,
            'origin_longitude' => 106.8094,
            'origin_area_id' => 'IDNP6IDNC149IDND1125',
            'biteship_enabled_couriers' => ['jne', 'sicepat', 'anteraja', 'gojek'],
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('storefront.shipping.origin.save'), $payload);

        $response->assertRedirect(route('storefront.shipping.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commerce_store_settings', [
            'business_id' => $this->business->id,
            'origin_contact_name' => 'Logistik Manager',
            'origin_postal_code' => '12190',
            'origin_area_id' => 'IDNP6IDNC149IDND1125',
        ]);

        $setting = CommerceStoreSetting::where('business_id', $this->business->id)->first();
        $this->assertNotNull($setting);
        $this->assertNotEmpty($setting->origin_location_id);
    }

    public function test_public_can_calculate_shipping_rates_via_biteship_service(): void
    {
        $payload = [
            'subtotal' => 100000,
            'destination_postal_code' => '12310',
            'destination_address' => 'Jl. RS Fatmawati No 10, Cilandak, Jakarta Selatan',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'name' => $this->product->name,
                    'quantity' => 2,
                    'price' => 50000,
                    'weight' => 250,
                ],
            ],
        ];

        $response = $this->postJson(
            route('public.storefront.shipping.calculate', $this->business->slug),
            $payload
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'shipping_fee',
                'is_free',
                'options',
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['options']);
        $this->assertGreaterThan(0, $data['shipping_fee']);
    }

    public function test_customer_can_checkout_with_biteship_courier_details(): void
    {
        $customer = GlobalCustomer::create([
            'name' => 'Siti Nurhaliza',
            'phone' => '087711223344',
            'phone_verified_at' => now(),
            'email' => 'siti@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($customer, 'customer');

        $checkoutPayload = [
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '087711223344',
            'customer_email' => 'siti@example.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Margonda Raya No. 100, Beji, Depok',
            'destination_postal_code' => '16424',
            'courier_company' => 'jne',
            'courier_type' => 'reg',
            'courier_name' => 'JNE - Reguler',
            'shipping_fee' => 18000,
            'payment_gateway' => 'manual',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 50000,
                ],
            ],
        ];

        $response = $this->postJson(
            route('public.storefront.checkout', $this->business->slug),
            $checkoutPayload
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_name' => 'Siti Nurhaliza',
            'destination_postal_code' => '16424',
            'shipping_courier_code' => 'jne',
            'shipping_courier_service' => 'reg',
            'shipping_courier_name' => 'JNE - Reguler',
            'shipping_cost' => 18000,
            'biteship_service_fee' => 1000,
            'total_amount' => 119000,
        ]);
    }

    public function test_biteship_service_fee_is_charged_to_customer_and_calculated_in_rates(): void
    {
        $shippingService = new \App\Domain\Commerce\Storefront\CommerceShippingService();
        $calc = $shippingService->calculateShipping(
            business: $this->business,
            subtotal: 50000,
            destinationPostalCode: '12760',
            items: [
                ['name' => 'Produk', 'quantity' => 1, 'value' => 50000, 'weight' => 200]
            ]
        );

        $this->assertArrayHasKey('service_fee', $calc);
        $this->assertEquals(1000.0, $calc['service_fee']);
        $this->assertArrayHasKey('total_shipping_fee', $calc);
        $this->assertEquals($calc['shipping_fee'] + 1000.0, $calc['total_shipping_fee']);
    }

    public function test_merchant_can_create_track_and_cancel_biteship_order(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-TEST-BITESHIP-001',
            'tracking_token' => Str::random(32),
            'customer_name' => 'Budi Utomo',
            'customer_phone' => '081288889999',
            'customer_email' => 'budi@example.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
            'destination_postal_code' => '12930',
            'shipping_courier_code' => 'sicepat',
            'shipping_courier_service' => 'sicepat_reg',
            'shipping_courier_name' => 'SiCepat - Regular Package',
            'shipping_fee' => 15000,
            'subtotal' => 50000,
            'total_amount' => 65000,
            'status' => CommerceOrder::STATUS_PAID,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        // 1. Create Biteship Order
        $createRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('storefront.orders.biteship.create', $order));

        $createRes->assertRedirect();
        $createRes->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->biteship_order_id);
        $this->assertNotNull($order->shipping_waybill_id);
        $this->assertNotEmpty($order->shipping_status);

        // 2. Track Biteship Order
        $trackRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('storefront.orders.biteship.track', $order));

        $trackRes->assertRedirect();
        $trackRes->assertSessionHas('success');

        // 3. Cancel Biteship Order
        $cancelRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('storefront.orders.biteship.cancel', $order), [
                'reason' => 'Pelanggan meminta reschedule pengiriman',
            ]);

        $cancelRes->assertRedirect();
        $cancelRes->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('cancelled', $order->shipping_status);
    }

    public function test_biteship_locations_api_and_maps_search(): void
    {
        // 1. Test Maps Search Area API
        $searchRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('storefront.shipping.search_areas', ['query' => 'Cilandak']));

        $searchRes->assertOk();
        $searchRes->assertJsonStructure([
            'success',
            'areas',
        ]);
        $this->assertTrue($searchRes->json('success'));
        $this->assertNotEmpty($searchRes->json('areas'));

        // 2. Test BiteshipService Locations API direct operations
        $biteshipService = new \App\Domain\Shipping\BiteshipService();
        $createLoc = $biteshipService->createLocation([
            'name' => 'Toko Cabang Jakarta',
            'contact_name' => 'Manager Toko',
            'contact_phone' => '081299887766',
            'address' => 'Jl. RS Fatmawati No 15, Cilandak Barat',
            'postal_code' => 12430,
            'latitude' => -6.2921,
            'longitude' => 106.7972,
        ]);

        $this->assertTrue($createLoc['success']);
        $this->assertNotEmpty($createLoc['id']);

        $locationId = $createLoc['id'];
        $updateLoc = $biteshipService->updateLocation($locationId, [
            'name' => 'Toko Cabang Jakarta Updated',
            'contact_name' => 'Manager Toko Baru',
        ]);
        $this->assertTrue($updateLoc['success']);
    }

    public function test_biteship_activation_orders_simulation_command(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('biteship:simulate-activation-orders');
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('BITESHIP PRODUCTION ACTIVATION SIMULATOR', $output);
        $this->assertStringContainsString('Pesanan Delivered Berhasil Dibuat', $output);
        $this->assertStringContainsString('Pesanan Cancelled Berhasil Dibuat', $output);
        $this->assertStringContainsString('ID Pesanan Test yang Terkirim', $output);
        $this->assertStringContainsString('ID Pesanan Test yang Dibatalkan', $output);

        // Verify Delivered Order in Database
        $deliveredOrder = CommerceOrder::where('shipping_status', 'delivered')->latest()->first();
        $this->assertNotNull($deliveredOrder);
        $this->assertSame(CommerceOrder::STATUS_COMPLETED, $deliveredOrder->status);
        $this->assertNotNull($deliveredOrder->biteship_order_id);
        $this->assertSame(24, strlen($deliveredOrder->biteship_order_id));
        $this->assertStringStartsWith('BITESHIP-', $deliveredOrder->shipping_waybill_id);
        $this->assertEquals(1000.0, $deliveredOrder->biteship_service_fee);

        // Verify Cancelled Order in Database
        $cancelledOrder = CommerceOrder::where('shipping_status', 'cancelled')->latest()->first();
        $this->assertNotNull($cancelledOrder);
        $this->assertSame(CommerceOrder::STATUS_CANCELLED, $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->biteship_order_id);
        $this->assertSame(24, strlen($cancelledOrder->biteship_order_id));
        $this->assertNotNull($cancelledOrder->cancelled_at);
        $this->assertStringStartsWith('BITESHIP-', $cancelledOrder->shipping_waybill_id);
        $this->assertEquals(1000.0, $cancelledOrder->biteship_service_fee);

        // Verify Resi Change Order in Database
        $resiOrder = CommerceOrder::where('notes', 'like', '%Simulasi pergantian/update nomor resi pengiriman%')->latest()->first();
        $this->assertNotNull($resiOrder);
        $this->assertSame(CommerceOrder::STATUS_PROCESSING, $resiOrder->status);
        $this->assertSame('in_transit', $resiOrder->shipping_status);
        $this->assertNotNull($resiOrder->biteship_order_id);
        $this->assertSame(24, strlen($resiOrder->biteship_order_id));
        $this->assertStringStartsWith('BITESHIP-JNE-REV', $resiOrder->shipping_waybill_id);
    }
}


