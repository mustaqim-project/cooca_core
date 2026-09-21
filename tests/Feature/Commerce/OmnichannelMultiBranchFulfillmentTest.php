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
}
