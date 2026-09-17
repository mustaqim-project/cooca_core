<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Models\Business;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductChannelVisibilityAndPreorderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Unit $unit;
    private CommercePaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@fnb-catering.test',
            'phone' => '081122334455',
            'password' => bcrypt('secret123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Dapur Katering Ummi',
            'slug' => 'dapur-ummi',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'allow_negative_stock' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Dapur Utama',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->unit = Unit::firstOrCreate(
            ['code' => 'porsi'],
            ['name' => 'Porsi', 'category' => 'count', 'is_base' => true]
        );

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_scheduled_order' => true,
            'min_order_amount' => 0,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'bank_name' => 'BCA',
            'type' => CommercePaymentMethod::TYPE_BANK_TRANSFER,
            'account_number' => '1234567890',
            'account_holder' => 'Dapur Ummi',
            'is_active' => true,
        ]);
    }

    public function test_product_hidden_from_pos_is_excluded_from_for_pos_scope(): void
    {
        $posProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Nasi Box Ayam Bakar',
            'selling_price' => 30000,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        $nonPosProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Paket Wedding Deluxe (Khusus Web/SO)',
            'selling_price' => 5000000,
            'is_active' => true,
            'show_in_pos' => false,
        ]);

        $posResults = Product::where('business_id', $this->business->id)->forPos()->get();

        $this->assertTrue($posResults->contains('id', $posProduct->id));
        $this->assertFalse($posResults->contains('id', $nonPosProduct->id));
    }

    public function test_product_hidden_from_website_is_excluded_from_storefront_and_rejected_at_checkout(): void
    {
        $webProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Tumpeng Mini Kuning',
            'selling_price' => 45000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $internalProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Biaya Kantong Plastik / Internal POS',
            'selling_price' => 500,
            'is_active' => true,
            'show_in_website' => false,
        ]);

        $storefrontResults = Product::where('business_id', $this->business->id)->forStorefront()->get();

        $this->assertTrue($storefrontResults->contains('id', $webProduct->id));
        $this->assertFalse($storefrontResults->contains('id', $internalProduct->id));

        // Checkout service must reject ordering the internal-only product
        $orderService = app(CommerceOrderService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("tidak tersedia untuk pemesanan online");

        $orderService->createCheckoutOrder(
            business: $this->business,
            customerData: [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
            ],
            itemsData: [
                [
                    'product_id' => $internalProduct->id,
                    'quantity' => 1,
                ],
            ],
            fulfillmentType: 'pickup',
            paymentMethodId: $this->paymentMethod->id
        );
    }

    public function test_product_hidden_from_sales_order_is_excluded_from_sales_order_scope(): void
    {
        $soProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Paket Catering Harian 100 Pax',
            'selling_price' => 2500000,
            'is_active' => true,
            'show_in_sales_order' => true,
        ]);

        $retailOnlyProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Es Teh Manis Cup (POS Retail Only)',
            'selling_price' => 5000,
            'is_active' => true,
            'show_in_sales_order' => false,
        ]);

        $soResults = Product::where('business_id', $this->business->id)->forSalesOrder()->get();

        $this->assertTrue($soResults->contains('id', $soProduct->id));
        $this->assertFalse($soResults->contains('id', $retailOnlyProduct->id));
    }

    public function test_product_with_hidden_price_on_web_keeps_selling_price_in_database_and_displays_properly(): void
    {
        $customCake = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Custom Wedding Cake 3 Tingkat',
            'selling_price' => 1750000,
            'is_active' => true,
            'show_in_website' => true,
            'show_price_on_web' => false,
        ]);

        // Model helper check
        $this->assertFalse($customCake->isPriceVisibleOnWeb());
        $this->assertEquals(1750000, $customCake->selling_price);

        // Web endpoint response check
        $response = $this->get(route('public.business.landing', $this->business->slug));
        $response->assertOk();
        $response->assertSee('Custom Wedding Cake 3 Tingkat');
    }

    public function test_checkout_preorder_without_schedule_date_is_rejected(): void
    {
        $preorderProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Tumpeng Syukuran 50 Porsi (PO H-3)',
            'selling_price' => 1200000,
            'is_active' => true,
            'show_in_website' => true,
            'is_preorder' => true,
            'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
            'preorder_lead_days' => 3,
        ]);

        $orderService = app(CommerceOrderService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Pesanan Anda memuat produk Pre-Order. Silakan tentukan tanggal jadwal pengiriman/pengambilan.');

        $orderService->createCheckoutOrder(
            business: $this->business,
            customerData: [
                'name' => 'Siti Rahma',
                'phone' => '081299887766',
            ],
            itemsData: [
                [
                    'product_id' => $preorderProduct->id,
                    'quantity' => 1,
                ],
            ],
            fulfillmentType: 'pickup',
            paymentMethodId: $this->paymentMethod->id,
            options: [
                'scheduled_date' => null, // Intentionally empty
            ]
        );
    }

    public function test_checkout_preorder_with_insufficient_lead_days_is_rejected(): void
    {
        $preorderProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kue Lapis Legit Spesial (PO H-2)',
            'selling_price' => 350000,
            'is_active' => true,
            'show_in_website' => true,
            'is_preorder' => true,
            'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
            'preorder_lead_days' => 2,
        ]);

        $orderService = app(CommerceOrderService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('membutuhkan waktu persiapan minimal 2 hari');

        // Customer chooses tomorrow, which is only +1 day (less than 2 days lead time)
        $invalidTomorrowDate = Carbon::today()->addDay()->format('Y-m-d');

        $orderService->createCheckoutOrder(
            business: $this->business,
            customerData: [
                'name' => 'Siti Rahma',
                'phone' => '081299887766',
            ],
            itemsData: [
                [
                    'product_id' => $preorderProduct->id,
                    'quantity' => 1,
                ],
            ],
            fulfillmentType: 'pickup',
            paymentMethodId: $this->paymentMethod->id,
            options: [
                'scheduled_date' => $invalidTomorrowDate,
            ]
        );
    }

    public function test_checkout_preorder_with_valid_lead_days_succeeds(): void
    {
        $preorderProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kue Kering Nastar Wisman 1kg (PO H-3)',
            'selling_price' => 180000,
            'is_active' => true,
            'show_in_website' => true,
            'is_preorder' => true,
            'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
            'preorder_lead_days' => 3,
        ]);

        $orderService = app(CommerceOrderService::class);

        // Customer chooses today + 4 days (meets minimum 3 days)
        $validDate = Carbon::today()->addDays(4)->format('Y-m-d');

        $order = $orderService->createCheckoutOrder(
            business: $this->business,
            customerData: [
                'name' => 'Siti Rahma',
                'phone' => '081299887766',
            ],
            itemsData: [
                [
                    'product_id' => $preorderProduct->id,
                    'quantity' => 2,
                ],
            ],
            fulfillmentType: 'pickup',
            paymentMethodId: $this->paymentMethod->id,
            options: [
                'scheduled_date' => $validDate,
                'scheduled_time_slot' => 'Siang (12:00 - 15:30)',
            ]
        );

        $this->assertNotNull($order->id);
        $this->assertEquals($validDate, $order->scheduled_date?->format('Y-m-d'));
        $this->assertEquals('Siang (12:00 - 15:30)', $order->scheduled_time_slot);
        $this->assertEquals(360000, $order->total_amount);
    }

    public function test_update_product_unchecking_channels_persists_false_values(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Paket Kaos Polos Combed 30s',
            'slug' => 'paket-kaos-polos-combed-30s',
            'code' => 'PRD-TSHIRT-01',
            'selling_price' => 75000,
            'base_cost' => 45000,
            'is_active' => true,
            'show_in_website' => true,
            'show_in_pos' => true,
            'show_in_sales_order' => true,
            'show_price_on_web' => true,
            'is_preorder' => true,
        ]);

        // Simulating the exact HTML form payload where user UNCHECKED 'show_in_website' and 'is_preorder'
        // Browsers omit unchecked checkboxes completely from the request payload.
        $payload = [
            'name' => 'Paket Kaos Polos Combed 30s (Updated)',
            'sku' => 'PRD-TSHIRT-01',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 80000,
            'base_cost' => 45000,
            'min_stock' => 5,
            'show_in_pos' => '1',
            'show_in_sales_order' => '1',
            'show_price_on_web' => '1',
            // 'show_in_website' is omitted (unchecked)
            // 'is_preorder' is omitted (unchecked)
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->user)->put(route('products.update', $product->slug), $payload);

        $response->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertFalse($product->show_in_website, 'show_in_website should be false when unchecked in edit form');
        $this->assertFalse($product->is_preorder, 'is_preorder should be false when unchecked in edit form');
        $this->assertTrue($product->show_in_pos, 'show_in_pos should remain true');
        $this->assertTrue($product->show_in_sales_order, 'show_in_sales_order should remain true');
        $this->assertTrue($product->show_price_on_web, 'show_price_on_web should remain true');
        $this->assertTrue($product->is_active, 'is_active should remain true');
        $this->assertEquals(80000, $product->selling_price);
    }

    public function test_quick_toggle_product_setting_endpoint_works(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Arabika Gayo 250gr',
            'slug' => 'kopi-arabika-gayo-250gr',
            'code' => 'PRD-COFFEE-01',
            'selling_price' => 95000,
            'is_active' => true,
            'show_in_website' => true,
            'show_in_pos' => true,
            'show_in_sales_order' => true,
            'show_price_on_web' => true,
            'is_preorder' => false,
        ]);

        // 1. Explicitly toggle show_in_website to false
        $response = $this->actingAs($this->user)->postJson(route('products.toggle-setting', $product->id), [
            'field' => 'show_in_website',
            'value' => false,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'field' => 'show_in_website',
            'value' => false,
            'product_id' => $product->id,
        ]);

        $this->assertFalse($product->fresh()->show_in_website);

        // 2. Toggle without value (flip current state back to true) using slug
        $flipResponse = $this->actingAs($this->user)->postJson(route('products.toggle-setting', $product->slug), [
            'field' => 'show_in_website',
        ]);

        $flipResponse->assertOk();
        $flipResponse->assertJson([
            'status' => 'success',
            'field' => 'show_in_website',
            'value' => true,
        ]);

        $this->assertTrue($product->fresh()->show_in_website);

        // 3. Toggle Pre-Order status
        $poResponse = $this->actingAs($this->user)->postJson(route('products.toggle-setting', $product->id), [
            'field' => 'is_preorder',
            'value' => true,
        ]);

        $poResponse->assertOk();
        $this->assertTrue($product->fresh()->is_preorder);
    }

    public function test_quick_toggle_cross_tenant_is_forbidden(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Bisnis Lain',
            'slug' => 'bisnis-lain',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $otherProduct = Product::create([
            'business_id' => $otherBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Milik Toko Lain',
            'slug' => 'produk-milik-toko-lain',
            'selling_price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('products.toggle-setting', $otherProduct->id), [
            'field' => 'show_in_website',
            'value' => false,
        ]);

        // BusinessScope ensures cross-tenant product cannot be found by route model binding (HTTP 404)
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }
}

