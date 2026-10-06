<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosReportOrderDetailModalTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $supervisor;
    private Business $business;
    private Location $location;
    private PosShift $shift;
    private Customer $customer;
    private Product $goodsProduct;
    private Product $serviceProduct;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Cafe & Resto',
            'email' => 'owner@caferesto.cooca.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->supervisor = User::create([
            'name' => 'Supervisor Outlet',
            'email' => 'supervisor@caferesto.cooca.com',
            'password' => bcrypt('password123'),
        ]);
        $this->supervisor->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Kopi Nusantara & Bistro',
            'slug' => 'kopi-nusantara-bistro',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->supervisor->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->supervisor->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
        session(['active_business_id' => $this->business->id]);

        app(EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Senopati',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Bapak Ahmad Member VIP',
            'phone' => '081299887766',
            'email' => 'ahmad@example.com',
        ]);

        $this->shift = PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'shift_number' => 'SHIFT-MODAL-01',
            'opening_cash' => 500000,
            'status' => PosShift::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $portion = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'is_standard' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Signature Coffee',
        ]);

        $this->goodsProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $portion->id,
            'name' => 'Kopi Gula Aren Special',
            'code' => 'KOP-001',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 28000.0,
            'base_cost' => 11000.0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        $this->serviceProduct = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $portion->id,
            'name' => 'Jasa Cuci Cangkir Keramik',
            'code' => 'SRV-001',
            'type' => Product::TYPE_SERVICE,
            'selling_price' => 15000.0,
            'base_cost' => 2000.0,
            'track_stock' => false,
            'is_active' => true,
        ]);
    }

    public function test_order_detail_endpoint_returns_rich_json_payload_with_financial_and_audit_metrics(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'customer_id' => $this->customer->id,
            'pos_shift_id' => $this->shift->id,
            'order_number' => 'POS-2026-DTL-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja VIP 01',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 71000.0,
            'discount_amount' => 5000.0,
            'voucher_code' => 'HEMAT10K',
            'voucher_discount_amount' => 10000.0,
            'points_discount_amount' => 2000.0,
            'tax_percentage' => 11.0,
            'tax_amount' => 5940.0,
            'service_charge_percentage' => 5.0,
            'service_charge_amount' => 2700.0,
            'rounding_amount' => -40.0,
            'total_amount' => 62600.0,
            'total_hpp_cost' => 24000.0,
            'total_gross_profit' => 38600.0,
            'paid_amount' => 70000.0,
            'change_amount' => 7400.0,
            'print_count' => 1,
            'last_printed_at' => now(),
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->goodsProduct->id,
            'product_name' => $this->goodsProduct->name,
            'product_code' => $this->goodsProduct->code,
            'quantity' => 2,
            'unit_price' => 28000.0,
            'unit_cost_hpp' => 11000.0,
            'subtotal' => 56000.0,
            'discount_amount' => 5000.0,
            'total_price' => 51000.0,
            'total_hpp' => 22000.0,
            'batch_number' => 'BATCH-2026-001',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $this->serviceProduct->id,
            'product_name' => $this->serviceProduct->name,
            'product_code' => $this->serviceProduct->code,
            'quantity' => 1,
            'unit_price' => 15000.0,
            'unit_cost_hpp' => 2000.0,
            'subtotal' => 15000.0,
            'discount_amount' => 0.0,
            'total_price' => 15000.0,
            'total_hpp' => 2000.0,
        ]);

        // Split Payment: Cash 20k, QRIS 50k
        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 20000.0,
            'net_amount' => 20000.0,
            'status' => 'paid',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 50000.0,
            'net_amount' => 49650.0,
            'fee_amount' => 350.0,
            'reference_number' => 'QRIS-TRX-998877',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('pos.reports.orders.detail', $order->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_number' => 'POS-2026-DTL-001',
                    'status' => 'completed',
                    'order_type' => 'dine_in',
                    'table_or_reference' => 'Meja VIP 01',
                    'customer' => [
                        'name' => 'Bapak Ahmad Member VIP',
                        'phone' => '081299887766',
                    ],
                    'cashier' => [
                        'name' => 'Owner Cafe & Resto',
                    ],
                    'financial' => [
                        'subtotal' => 71000.0,
                        'order_discount' => 5000.0,
                        'voucher_code' => 'HEMAT10K',
                        'voucher_discount' => 10000.0,
                        'points_discount' => 2000.0,
                        'total_discount' => 17000.0,
                        'total_amount' => 62600.0,
                        'paid_amount' => 70000.0,
                        'change_amount' => 7400.0,
                    ],
                    'audit' => [
                        'printed_count' => 1,
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data['items']);
        $this->assertCount(2, $data['payments']);
        $itemNames = collect($data['items'])->pluck('name')->all();
        $this->assertContains('Kopi Gula Aren Special', $itemNames);
        $this->assertContains('Jasa Cuci Cangkir Keramik', $itemNames);
        $batchNumbers = collect($data['items'])->pluck('batch_number')->filter()->values()->all();
        $this->assertContains('BATCH-2026-001', $batchNumbers);
        $this->assertEquals('QRIS-TRX-998877', $data['payments'][1]['reference_number']);
    }

    /**
     * Test verifikasi channel_meta untuk pesanan Online Food Delivery (ShopeeFood / GoFood).
     */
    public function test_order_detail_endpoint_returns_online_food_delivery_channel_meta_calculations(): void
    {
        $sfOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-SF-2026-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'sales_channel' => 'shopeefood',
            'external_order_ref' => 'SF-889911',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'discount_amount' => 10000.0,
            'total_amount' => 90000.0,
            'total_hpp_cost' => 40000.0,
            'total_gross_profit' => 50000.0,
            'paid_amount' => 90000.0,
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('pos.reports.orders.detail', $sfOrder->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_number' => 'POS-SF-2026-001',
                    'sales_channel' => 'shopeefood',
                    'channel_meta' => [
                        'channel_code' => 'shopeefood',
                        'channel_name' => 'ShopeeFood',
                        'channel_type' => 'online_delivery',
                        'platform_fee_percent' => 20.0,
                        'platform_fee_amount' => 18000.0,
                        'net_merchant_payout' => 72000.0,
                        'real_gross_profit' => 32000.0,
                        'badge_bg' => '#EE4D2D',
                    ],
                ],
            ]);
    }

    /**
     * Test verifikasi industry_meta untuk bengkel otomotif dan laundry.
     */
    public function test_order_detail_endpoint_returns_automotive_workshop_and_laundry_industry_meta(): void
    {
        $workshopOrder = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-WS-2026-001',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 250000.0,
            'total_amount' => 250000.0,
            'paid_amount' => 250000.0,
            'vehicle_license_plate' => 'B 1234 COOCA',
            'vehicle_model' => 'Toyota Avanza 1.5G',
            'vehicle_mileage' => 45200,
            'service_notes' => 'Ganti oli mesin, filter oli, dan kuras radiator',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('pos.reports.orders.detail', $workshopOrder->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_number' => 'POS-WS-2026-001',
                    'industry_meta' => [
                        'vehicle_license_plate' => 'B 1234 COOCA',
                        'vehicle_model' => 'Toyota Avanza 1.5G',
                        'vehicle_mileage' => 45200,
                        'service_notes' => 'Ganti oli mesin, filter oli, dan kuras radiator',
                        'has_industry_data' => true,
                    ],
                ],
            ]);
    }

    public function test_order_detail_endpoint_enforces_strict_multi_tenant_isolation(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Competitor Cafe',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $foreignOrder = PosOrder::create([
            'business_id' => $otherBusiness->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-FOREIGN-999',
            'order_date' => now()->toDateString(),
            'order_source' => PosOrder::SOURCE_POS,
            'order_type' => 'dine_in',
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 100000.0,
            'total_amount' => 100000.0,
            'paid_amount' => 100000.0,
        ]);

        // Attempt to access foreign tenant's order
        $response = $this->actingAs($this->owner)
            ->getJson(route('pos.reports.orders.detail', $foreignOrder->id));

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }
}
