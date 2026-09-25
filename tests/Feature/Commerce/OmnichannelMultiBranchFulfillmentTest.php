<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OmnichannelMultiBranchFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
    }

    public function test_pickup_order_resolves_specified_branch_location(): void
    {
        $business = Business::create([
            'name' => 'Toko Kue Nusantara',
            'slug' => 'toko-kue-nusantara',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $mainLocation = Location::create([
            'business_id' => $business->id,
            'name' => 'Outlet Pusat Sudirman',
            'slug' => 'outlet-pusat-sudirman',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
            'allow_storefront_pickup' => true,
            'is_online_fulfillment' => true,
        ]);

        $branchLocation = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Bintaro Sektor 7',
            'slug' => 'cabang-bintaro-sektor-7',
            'type' => 'outlet',
            'is_primary' => false,
            'is_active' => true,
            'allow_storefront_pickup' => true,
            'is_online_fulfillment' => true,
        ]);

        $unit = \App\Models\Unit::first();

        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Brownies Panggang Fudgy',
            'slug' => 'brownies-panggang-fudgy',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $unit->id,
            'selling_price' => 75000,
            'base_cost' => 35000,
            'show_in_website' => true,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $branchLocation->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'last_cost' => 35000,
        ]);

        $orderService = app(CommerceOrderService::class);

        $order = $orderService->createCheckoutOrder(
            business: $business,
            customerData: [
                'name' => 'Rina Wijaya',
                'phone' => '081298765432',
                'email' => 'rina@example.com',
            ],
            itemsData: [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_PICKUP,
            paymentMethodId: null,
            options: [
                'location_id' => $branchLocation->id,
            ]
        );

        $this->assertInstanceOf(CommerceOrder::class, $order);
        $this->assertEquals($branchLocation->id, $order->location_id);
        $this->assertEquals(CommerceOrder::FULFILLMENT_PICKUP, $order->fulfillment_type);
        $this->assertEquals(150000, $order->total_amount);
    }

    public function test_delivery_order_with_coordinates_routes_to_nearest_online_fulfillment_branch(): void
    {
        $business = Business::create([
            'name' => 'Restoran Padang Berkah',
            'slug' => 'restoran-padang-berkah',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'allow_delivery' => true,
        ]);

        // Jakarta Pusat branch: -6.1818, 106.8223
        $centralBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Sabang Jakpus',
            'slug' => 'cabang-sabang-jakpus',
            'type' => 'outlet',
            'latitude' => -6.1818,
            'longitude' => 106.8223,
            'is_primary' => true,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        // Jakarta Selatan branch: -6.2442, 106.8005
        $southBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Blok M Jaksel',
            'slug' => 'cabang-blok-m-jaksel',
            'type' => 'outlet',
            'latitude' => -6.2442,
            'longitude' => 106.8005,
            'is_primary' => false,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        $unit = \App\Models\Unit::first();

        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Rendang Sapi Daging Pilihan',
            'slug' => 'rendang-sapi-daging-pilihan',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $unit->id,
            'selling_price' => 35000,
            'base_cost' => 18000,
            'show_in_website' => true,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $southBranch->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'last_cost' => 18000,
        ]);

        $orderService = app(CommerceOrderService::class);

        // Customer coordinate in Senopati/Kebayoran Baru (very close to Blok M Jaksel: -6.2350, 106.8090)
        $order = $orderService->createCheckoutOrder(
            business: $business,
            customerData: [
                'name' => 'Ahmad Fauzi',
                'phone' => '081311223344',
                'address' => 'Jl. Suryo No. 12, Senopati, Jaksel',
            ],
            itemsData: [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            paymentMethodId: null,
            options: [
                'customer_lat' => -6.2350,
                'customer_lng' => 106.8090,
            ]
        );

        $this->assertInstanceOf(CommerceOrder::class, $order);
        // Expect South Jakarta branch to be chosen because distance to Senopati is ~1.5km vs ~6.5km to Sabang
        $this->assertEquals($southBranch->id, $order->location_id);
    }

    public function test_padang_buyer_routes_to_closer_jakarta_branch_over_surabaya(): void
    {
        $business = Business::create([
            'name' => 'Distributor Nasional',
            'slug' => 'distributor-nasional',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'allow_delivery' => true,
        ]);

        // Jakarta Branch: -6.2088, 106.8456 (Distance to Padang: ~920 km)
        $jakartaBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Jakarta Pusat',
            'slug' => 'cabang-jakarta-pusat',
            'type' => 'outlet',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => false,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        // Surabaya Branch: -7.2575, 112.7521 (Distance to Padang: ~1,540 km)
        $surabayaBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Surabaya Gubeng',
            'slug' => 'cabang-surabaya-gubeng',
            'type' => 'outlet',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_primary' => true,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        $unit = \App\Models\Unit::first();
        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Sparepart Mesin Premium',
            'slug' => 'sparepart-mesin-premium',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $unit->id,
            'selling_price' => 500000,
            'base_cost' => 300000,
            'show_in_website' => true,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $jakartaBranch->id,
            'product_id' => $product->id,
            'quantity' => 25,
            'last_cost' => 300000,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $surabayaBranch->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'last_cost' => 300000,
        ]);

        // 1. Test Shipping calculation resolves Jakarta as closer origin to Padang (-0.9471, 100.4172)
        $shippingService = app(\App\Domain\Commerce\Storefront\CommerceShippingService::class);
        $quote = $shippingService->calculateShipping(
            business: $business,
            subtotal: 500000,
            destinationPostalCode: '25111',
            destinationCoordinates: [
                'latitude' => -0.9471,
                'longitude' => 100.4172,
            ],
            items: [
                ['name' => 'Sparepart Mesin Premium', 'value' => 500000, 'weight' => 1000, 'quantity' => 1],
            ],
            destinationAddress: 'Jl. Khatib Sulaiman No. 45, Padang Barat, Kota Padang'
        );

        $this->assertEquals($jakartaBranch->id, $quote['origin_location_id'] ?? null);

        // 2. Test Order Creation routes to Jakarta Branch
        $orderService = app(CommerceOrderService::class);
        $order = $orderService->createCheckoutOrder(
            business: $business,
            customerData: [
                'name' => 'Hendra Syahputra',
                'phone' => '081267890123',
                'address' => 'Jl. Khatib Sulaiman No. 45, Padang Barat, Kota Padang',
            ],
            itemsData: [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            paymentMethodId: null,
            options: [
                'customer_lat' => -0.9471,
                'customer_lng' => 100.4172,
            ]
        );

        $this->assertEquals($jakartaBranch->id, $order->location_id);
    }

    public function test_padang_buyer_routes_to_surabaya_when_closer_jakarta_branch_out_of_stock(): void
    {
        $business = Business::create([
            'name' => 'Distributor Nasional 2',
            'slug' => 'distributor-nasional-2',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'allow_delivery' => true,
        ]);

        // Jakarta Branch: Closer to Padang (~920 km), BUT STOCK IS 0
        $jakartaBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Jakarta Pusat (Kosong)',
            'slug' => 'cabang-jakarta-pusat-kosong',
            'type' => 'outlet',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => false,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        // Surabaya Branch: Further from Padang (~1,540 km), BUT HAS 30 PCS STOCK
        $surabayaBranch = Location::create([
            'business_id' => $business->id,
            'name' => 'Cabang Surabaya Gubeng (Ada Stok)',
            'slug' => 'cabang-surabaya-gubeng-ada-stok',
            'type' => 'outlet',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'is_primary' => true,
            'is_active' => true,
            'is_online_fulfillment' => true,
        ]);

        $unit = \App\Models\Unit::first();
        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Genset Industri Silent',
            'slug' => 'genset-industri-silent',
            'type' => Product::TYPE_GOODS,
            'output_unit_id' => $unit->id,
            'selling_price' => 15000000,
            'base_cost' => 10000000,
            'show_in_website' => true,
            'is_active' => true,
        ]);

        // Jakarta has 0 stock
        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $jakartaBranch->id,
            'product_id' => $product->id,
            'quantity' => 0,
            'last_cost' => 10000000,
        ]);

        // Surabaya has 30 stock
        \App\Models\InventoryStock::create([
            'business_id' => $business->id,
            'location_id' => $surabayaBranch->id,
            'product_id' => $product->id,
            'quantity' => 30,
            'last_cost' => 10000000,
        ]);

        // 1. Shipping calculation should automatically bypass empty Jakarta and use Surabaya origin
        $shippingService = app(\App\Domain\Commerce\Storefront\CommerceShippingService::class);
        $quote = $shippingService->calculateShipping(
            business: $business,
            subtotal: 15000000,
            destinationPostalCode: '25111',
            destinationCoordinates: [
                'latitude' => -0.9471,
                'longitude' => 100.4172,
            ],
            items: [
                [
                    'product_id' => $product->id,
                    'name' => 'Genset Industri Silent',
                    'value' => 15000000,
                    'weight' => 20000,
                    'quantity' => 1,
                ],
            ],
            destinationAddress: 'Jl. Khatib Sulaiman No. 45, Padang Barat, Kota Padang'
        );

        $this->assertEquals($surabayaBranch->id, $quote['origin_location_id'] ?? null);

        // 2. Order creation should route fulfillment to Surabaya Branch and deduct stock there
        $orderService = app(CommerceOrderService::class);
        $order = $orderService->createCheckoutOrder(
            business: $business,
            customerData: [
                'name' => 'Hendra Syahputra',
                'phone' => '081267890123',
                'address' => 'Jl. Khatib Sulaiman No. 45, Padang Barat, Kota Padang',
            ],
            itemsData: [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            paymentMethodId: null,
            options: [
                'customer_lat' => -0.9471,
                'customer_lng' => 100.4172,
            ]
        );

        $this->assertEquals($surabayaBranch->id, $order->location_id);

        // Verify Surabaya stock was reserved/deducted (now 29 remaining available)
        $surabayaStock = \App\Models\InventoryStock::where('location_id', $surabayaBranch->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(1, $surabayaStock->reserved_quantity);
    }
}
