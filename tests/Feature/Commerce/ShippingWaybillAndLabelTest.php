<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Domain\Shipping\BarcodeService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommerceStoreSetting;
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

final class ShippingWaybillAndLabelTest extends TestCase
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
            'name'              => 'Logistics Merchant',
            'email'             => 'logistics_merchant@example.com',
            'email_verified_at' => now(),
            'phone'             => '081234567888',
            'phone_verified_at' => now(),
            'password'          => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name'            => 'Toko Logistik Mandiri',
            'currency_code'   => 'IDR',
            'currency_symbol' => 'Rp',
            'slug'            => 'toko-logistik-mandiri',
            'is_active'       => true,
            'phone'           => '081298765432',
            'address'         => 'Jl. Radio Dalam No. 12, Kebayoran Baru',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Utama',
            'is_primary'  => true,
        ]);

        $this->product = Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Oli Mesin Matic 0.8L',
            'sku'            => 'OLI-001',
            'type'           => 'goods',
            'output_unit_id' => $unit->id,
            'selling_price'  => 45000,
            'base_cost'      => 35000,
            'weight_grams'   => 850,
            'is_active'      => true,
        ]);

        CommerceStoreSetting::create([
            'business_id'          => $this->business->id,
            'origin_contact_name'  => 'Budi Logistik',
            'origin_contact_phone' => '081298765432',
            'origin_address'       => 'Jl. Radio Dalam No. 12, Jakarta Selatan',
            'origin_postal_code'   => '12140',
        ]);
    }

    public function test_it_generates_valid_vector_barcode_svg(): void
    {
        $barcodeService = new BarcodeService();
        $svg = $barcodeService->generateSvg('SOCAG0012345');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function test_it_displays_printable_shipping_label_with_barcode_and_addresses(): void
    {
        $order = CommerceOrder::create([
            'business_id'              => $this->business->id,
            'location_id'              => $this->location->id,
            'order_number'             => 'ORD-20260918-0001',
            'order_type'               => 'delivery',
            'fulfillment_type'         => 'delivery',
            'customer_name'            => 'Ahmad Santoso',
            'customer_phone'           => '081234567800',
            'customer_email'           => 'ahmad@example.com',
            'shipping_address'         => 'Jl. Mampang Prapatan No. 88, RT 02/05',
            'destination_postal_code'  => '12760',
            'shipping_courier_code'    => 'jne',
            'shipping_courier_service' => 'REG',
            'shipping_courier_name'    => 'JNE Express REG',
            'shipping_cost'            => 11000,
            'shipping_fee'             => 11000,
            'subtotal_amount'          => 45000,
            'total_amount'             => 56000,
            'status'                   => CommerceOrder::STATUS_PAID,
            'payment_status'           => CommerceOrder::PAYMENT_PAID,
            'shipping_waybill_id'      => 'JNE1234567890ID',
            'tracking_token'           => 'trk_' . Str::random(24),
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id'        => $this->product->id,
            'product_name'      => $this->product->name,
            'quantity'          => 1,
            'unit_price'        => 45000,
            'subtotal'          => 45000,
        ]);

        Context::setBusiness($this->business);
        $response = $this->actingAs($this->user)
            ->get(route('storefront.orders.shipping_label', $order));

        $response->assertStatus(200);
        $response->assertSee('JNE1234567890ID');
        $response->assertSee('Ahmad Santoso');
        $response->assertSee('12760');
        $response->assertSee('12140');
        $response->assertSee('Oli Mesin Matic 0.8L');
        $response->assertSee('NON-COD');
        $response->assertSee('Cetak Resi');
    }

    public function test_it_allows_merchant_to_update_waybill_manually(): void
    {
        $order = CommerceOrder::create([
            'business_id'             => $this->business->id,
            'location_id'             => $this->location->id,
            'order_number'            => 'ORD-20260918-0002',
            'order_type'              => 'delivery',
            'fulfillment_type'        => 'delivery',
            'customer_name'           => 'Bambang Sudiro',
            'customer_phone'          => '081299998888',
            'shipping_address'        => 'Jl. Margonda No. 10, Depok',
            'destination_postal_code' => '16424',
            'shipping_cost'           => 12000,
            'subtotal_amount'         => 45000,
            'total_amount'            => 57000,
            'status'                  => CommerceOrder::STATUS_PAID,
            'payment_status'          => CommerceOrder::PAYMENT_PAID,
            'tracking_token'          => 'trk_' . Str::random(24),
        ]);

        Context::setBusiness($this->business);
        $response = $this->actingAs($this->user)
            ->post(route('storefront.orders.waybill.update', $order), [
                'shipping_waybill_id'      => 'SPXID0987654321',
                'shipping_courier_code'    => 'sicepat',
                'shipping_courier_service' => 'BEST',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('SPXID0987654321', $order->shipping_waybill_id);
        $this->assertEquals('sicepat', $order->shipping_courier_code);
        $this->assertEquals('BEST', $order->shipping_courier_service);
    }

    public function test_it_auto_generates_waybill_number_when_requested(): void
    {
        $order = CommerceOrder::create([
            'business_id'             => $this->business->id,
            'location_id'             => $this->location->id,
            'order_number'            => 'ORD-20260918-0003',
            'order_type'              => 'delivery',
            'fulfillment_type'        => 'delivery',
            'customer_name'           => 'Citra Kirana',
            'customer_phone'          => '081277776666',
            'shipping_address'        => 'Jl. Gatot Subroto No. 5, Jakarta',
            'destination_postal_code' => '12930',
            'subtotal_amount'         => 45000,
            'total_amount'            => 45000,
            'status'                  => CommerceOrder::STATUS_PAID,
            'payment_status'          => CommerceOrder::PAYMENT_PAID,
            'tracking_token'          => 'trk_' . Str::random(24),
        ]);

        Context::setBusiness($this->business);
        $response = $this->actingAs($this->user)
            ->post(route('storefront.orders.waybill.update', $order), [
                'generate_auto'         => '1',
                'shipping_courier_code' => 'kurir_toko',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertNotEmpty($order->shipping_waybill_id);
        $this->assertStringStartsWith('CCKUR', $order->shipping_waybill_id);
    }
}
