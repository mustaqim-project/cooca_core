<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
use App\Models\GlobalCustomerAddress;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerMultiAddressAndBiteshipGeoTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private GlobalCustomer $customer;
    private Product $product;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Kopi Senja Utama',
            'slug' => 'kopi-senja-utama',
            'is_active' => true,
            'currency' => 'IDR',
            'email' => 'kopisenja@cooca.id',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Sudirman',
            'is_active' => true,
            'is_primary' => true,
            'is_online_fulfillment' => true,
            'allow_storefront_pickup' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_scheduled_order' => true,
        ]);

        $this->customer = GlobalCustomer::create([
            'name' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'phone' => '081298765432',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'shipping_address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
        ]);

        $unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'count', 'is_base' => true]
        );

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Espresso Blend',
            'slug' => 'kopi-espresso-blend',
            'selling_price' => 35000,
            'sku' => 'KOP-ESP-01',
            'is_active' => true,
            'show_in_website' => true,
            'show_price_on_web' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'bank_name' => 'QRIS Cooca Pay',
            'type' => 'qris',
            'is_active' => true,
        ]);

        CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Instan Kota',
            'rate_amount' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_create_multiple_addresses_and_switch_default(): void
    {
        // 1. Create first address (Home)
        $homeAddress = GlobalCustomerAddress::create([
            'global_customer_id' => $this->customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi Pratama',
            'recipient_phone' => '081298765432',
            'full_address' => 'Jl. Merdeka No. 10',
            'city' => 'Jakarta Pusat',
            'postal_code' => '10110',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'biteship_area_id' => 'IDNP6IDNC148IDND1127IDZ10110',
            'is_default' => true,
        ]);

        // 2. Create second address (Office)
        $officeAddress = GlobalCustomerAddress::create([
            'global_customer_id' => $this->customer->id,
            'label' => 'Kantor',
            'recipient_name' => 'Budi Kantor',
            'recipient_phone' => '081298765432',
            'full_address' => 'Gedung Wisma 46 Lt. 12',
            'city' => 'Jakarta Pusat',
            'postal_code' => '10220',
            'latitude' => -6.208763,
            'longitude' => 106.819876,
            'is_default' => false,
        ]);

        $this->assertCount(2, $this->customer->fresh()->addresses);
        $this->assertEquals($homeAddress->id, $this->customer->fresh()->defaultAddress->id);

        // 3. Mark Office as default
        $officeAddress->markAsDefault();

        $this->assertTrue($officeAddress->fresh()->is_default);
        $this->assertFalse($homeAddress->fresh()->is_default);
        $this->assertEquals($officeAddress->id, $this->customer->fresh()->defaultAddress->id);
    }

    public function test_customer_address_api_endpoints_work_via_customer_guard(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->postJson(route('customer.addresses.store'), [
                'label' => 'Apartemen',
                'recipient_name' => 'Budi Apartemen',
                'recipient_phone' => '081234567890',
                'full_address' => 'Apartemen Sudirman Tower A No. 1205',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12190',
                'latitude' => -6.225014,
                'longitude' => 106.809658,
                'biteship_area_id' => 'IDNP6IDNC149IDND1130IDZ12190',
                'is_default' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('global_customer_addresses', [
            'global_customer_id' => $this->customer->id,
            'label' => 'Apartemen',
            'postal_code' => '12190',
            'is_default' => true,
        ]);
    }

    public function test_geolocation_reverse_geocode_returns_coordinates_and_status(): void
    {
        // Mock Nominatim response
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Monumen Nasional, Gambir, Jakarta Pusat, DKI Jakarta, 10110, Indonesia',
                'address' => [
                    'road' => 'Jl. Medan Merdeka Barat',
                    'suburb' => 'Gambir',
                    'city' => 'Jakarta Pusat',
                    'postcode' => '10110',
                ],
            ], 200),
            'api.biteship.com/*' => Http::response([
                'success' => true,
                'areas' => [
                    [
                        'id' => 'IDNP6IDNC148IDND1127IDZ10110',
                        'name' => 'Gambir, Jakarta Pusat',
                        'postal_code' => 10110,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/geo/reverse-geocode?lat=-6.175392&lng=106.827153');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'latitude' => -6.175392,
                'longitude' => 106.827153,
            ]);
    }

    public function test_biteship_area_search_returns_area_list(): void
    {
        // Set mock Biteship API key
        config(['services.biteship.api_key' => 'biteship_test_key']);

        Http::fake([
            'api.biteship.com/*' => Http::response([
                'success' => true,
                'areas' => [
                    [
                        'id' => 'IDNP6IDNC148IDND1127IDZ10110',
                        'name' => 'Gambir, Jakarta Pusat',
                        'administrative_division_level_1_name' => 'DKI Jakarta',
                        'administrative_division_level_2_name' => 'Jakarta Pusat',
                        'administrative_division_level_3_name' => 'Gambir',
                        'administrative_division_level_4_name' => 'Gambir',
                        'postal_code' => 10110,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/geo/search-areas?query=Gambir');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'areas' => [
                    '*' => ['id', 'label', 'postal_code'],
                ],
            ]);
    }

    public function test_storefront_checkout_saves_gps_coordinates_and_creates_new_address_entry(): void
    {
        $payload = [
            '_token' => 'dummy-csrf-token',
            'customer_name' => 'Budi Checkout',
            'customer_phone' => '081298765432',
            'customer_email' => 'budi@example.com',
            'shipping_address' => 'Jl. Boulevard Raya Blok QJ No. 1, Kelapa Gading',
            'destination_postal_code' => '14240',
            'destination_latitude' => -6.155432,
            'destination_longitude' => 106.904321,
            'biteship_area_id' => 'IDNP6IDNC150IDND1135IDZ14240',
            'destination_area_id' => 'IDNP6IDNC150IDND1135IDZ14240',
            'save_to_address_book' => true,
            'address_label' => 'Kantor Cabang',
            'notes' => 'Tolong telepon sebelum sampai.',
            'fulfillment_type' => 'merchant_delivery',
            'payment_gateway' => 'tripay',
            'payment_channel' => 'QRIS',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Less sugar',
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson(route('public.storefront.checkout', ['slug' => $this->business->slug]), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify order saved with coordinates & area ID
        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_phone' => '6281298765432',
            'destination_postal_code' => '14240',
            'destination_latitude' => -6.155432,
            'destination_longitude' => 106.904321,
            'destination_area_id' => 'IDNP6IDNC150IDND1135IDZ14240',
        ]);

        // Verify new address was added to customer's address book
        $this->assertDatabaseHas('global_customer_addresses', [
            'global_customer_id' => $this->customer->id,
            'label' => 'Kantor Cabang',
            'postal_code' => '14240',
            'latitude' => -6.155432,
            'longitude' => 106.904321,
            'biteship_area_id' => 'IDNP6IDNC150IDND1135IDZ14240',
        ]);
    }

    public function test_checkout_view_renders_interactive_map_and_search_elements(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('public.storefront.checkout', ['slug' => $this->business->slug]));

        $response->assertStatus(200);
        $response->assertSee('checkout-delivery-map');
        $response->assertSee('searchBiteshipAreas()');
        $response->assertSee('leaflet.js');
        $response->assertSee('leaflet.css');
        $response->assertSee('Titik Presisi Lokasi');
        $response->assertSee('Pilihan Ekspedisi &amp; Biaya Ongkir', false);
        // Ensure raw Biteship Area ID is NOT displayed
        $response->assertDontSee('Biteship Area:');
    }

    public function test_shipping_calculation_considers_product_weight_and_dimensions(): void
    {
        // Update product with custom weight and dimensions
        $this->product->update([
            'weight' => 500.0,
            'length' => 15.0,
            'width' => 10.0,
            'height' => 8.0,
        ]);

        $response = $this->postJson(route('public.storefront.shipping.calculate', ['slug' => $this->business->slug]), [
            'subtotal' => 70000,
            'destination_postal_code' => '14240',
            'shipping_address' => 'Jl. Boulevard Kelapa Gading',
            'latitude' => -6.1554,
            'longitude' => 106.9043,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 35000,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('total_weight_grams', $data);
        $this->assertEquals(1000, $data['total_weight_grams']); // 500g * 2
        $this->assertArrayHasKey('service_fee', $data);
        $this->assertEquals(1000, $data['service_fee']);
    }

    public function test_storefront_checkout_submits_courier_details_and_applies_system_service_fee(): void
    {
        $payload = [
            '_token' => 'dummy-csrf-token',
            'customer_name' => 'Budi Pratama',
            'customer_phone' => '081298765432',
            'customer_email' => 'budi@example.com',
            'shipping_address' => 'Jl. Sudirman Kav 21',
            'destination_postal_code' => '10220',
            'destination_latitude' => -6.2115,
            'destination_longitude' => 106.8229,
            'fulfillment_type' => 'delivery',
            'shipping_rule_id' => 'jne_reg',
            'shipping_fee' => 12000,
            'courier_company' => 'jne',
            'courier_type' => 'reg',
            'courier_name' => 'JNE Reguler',
            'biteship_service_fee' => 1000,
            'payment_gateway' => 'tripay',
            'payment_channel' => 'QRIS',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->postJson(route('public.storefront.checkout', ['slug' => $this->business->slug]), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify order saved with shipping details and biteship_service_fee (subtotal 35000 + ship 12000 + fee 1000 = 48000)
        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'customer_phone' => '6281298765432',
            'shipping_cost' => 12000,
            'biteship_service_fee' => 1000,
            'shipping_courier_code' => 'jne',
            'shipping_courier_service' => 'reg',
            'total_amount' => 48000,
        ]);
    }

    public function test_customer_profile_view_renders_address_book_and_interactive_map(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('customer.profile'));

        $response->assertStatus(200);
        $response->assertSee('profile-address-map');
        $response->assertSee('searchBiteshipAreas()');
        $response->assertSee('leaflet.js');
        $response->assertSee('leaflet.css');
        $response->assertSee('Buku Alamat Pengiriman');
    }
}


