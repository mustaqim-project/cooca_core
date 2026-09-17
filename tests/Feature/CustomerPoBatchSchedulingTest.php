<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultUnitSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerPoBatchSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private CommerceStoreSetting $setting;
    private Location $location;
    private Product $product;
    private CommercePaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // Freeze time on a Wednesday so Friday is a few days ahead
        Carbon::setTestNow('2026-09-16 10:00:00'); // Wednesday

        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@dsr-test.id',
            'phone' => '081122334455',
            'password' => bcrypt('secret123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Dapur Sedap Rasa Test',
            'slug' => 'dapur-sedap-rasa-test',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'allow_negative_stock' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Kelapa Gading',
            'is_primary' => true,
            'is_active' => true,
        ]);

        BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Dapur Sedap Rasa Test',
            'subheadline' => 'Spesialis Katering Nasi Box & PO Nusantara',
            'is_published' => true,
            'show_pos_products' => true,
            'theme_color' => '#007AFF',
            'cta_primary_text' => 'Pesan Sekarang',
            'whatsapp_number' => '081122334455',
        ]);

        $unit = Unit::first();

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Nasi Box Spesial Ayam Bakar',
            'code' => 'PRD-TEST-01',
            'type' => 'goods',
            'output_unit_id' => $unit->id,
            'selling_price' => 35000,
            'is_active' => true,
            'is_preorder' => true,
            'show_on_storefront' => true,
            'show_price_on_web' => true,
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '8800112233',
            'account_holder' => 'PT Dapur Sedap Rasa',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->setting = CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_request_order' => false,
            'allow_scheduled_order' => false,
            'allow_customer_po' => true,
            'allow_reservation' => false,
            'allow_custom_date' => false, // Strict batch only!
            'lead_time_hours' => 1,
            'cut_off_time' => '20:00:00',
            'daily_order_quota' => 150,
            'quota_metric' => 'quantity',
            'preorder_quota_unit' => 'PCS',
            'batch_dates_mode' => 'operating_days',
            'operating_days' => ['friday'],
            'available_slots' => ['09:00 - 11:00'],
            'min_order_amount' => 20000,
            'order_auto_cancel_minutes' => 60,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_landing_page_renders_batch_chips_and_hides_custom_date_when_allow_custom_date_is_false(): void
    {
        $response = $this->get("/b/{$this->business->slug}");

        $response->assertStatus(200);
        $response->assertSee('Pilih Batch Pengiriman');
        $response->assertSee('Sisa 150 PCS');
        $response->assertSee('Pengiriman hanya dibuka pada tanggal batch di atas');
        $response->assertDontSee('Atau Pilih Tanggal Sendiri');
    }

    public function test_landing_page_shows_custom_date_when_allow_custom_date_is_true(): void
    {
        $this->setting->update(['allow_custom_date' => true]);

        $response = $this->get("/b/{$this->business->slug}");

        $response->assertStatus(200);
        $response->assertSee('Atau Pilih Tanggal Sendiri');
    }

    public function test_order_creation_rejects_unallowed_date_when_strict_batch_is_enforced(): void
    {
        $orderService = app(CommerceOrderService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Toko hanya menerima pemesanan pada jadwal batch pengiriman yang telah ditentukan.');

        // 2026-09-17 is Thursday, not in operating_days ['friday']
        $orderService->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_PICKUP,
            scheduledDate: '2026-09-17',
        );
    }

    public function test_order_creation_succeeds_on_valid_batch_date(): void
    {
        $orderService = app(CommerceOrderService::class);

        // 2026-09-18 is Friday (valid batch)
        $order = $orderService->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25,
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_PICKUP,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
        );

        $this->assertInstanceOf(CommerceOrder::class, $order);
        $this->assertEquals('2026-09-18', $order->scheduled_date?->toDateString());
        $this->assertEquals(CommerceOrder::TYPE_SCHEDULED_ORDER, $order->order_type);
        $this->assertEquals(875000, $order->total_amount); // 25 * 35,000
    }

    public function test_order_creation_rejects_when_quantity_exceeds_quota(): void
    {
        $orderService = app(CommerceOrderService::class);

        // First order takes 140 PCS out of 150 PCS quota
        $orderService->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Pelanggan 1',
                'phone' => '081234567891',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 140,
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_PICKUP,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
        );

        // Second order attempts to order 20 PCS (140 + 20 = 160 > 150)
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Sisa kuota untuk batch tanggal 18 September 2026 tersisa 10 PCS. Pesanan Anda (20 PCS) melebihi kuota yang tersedia.');

        $orderService->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Pelanggan 2',
                'phone' => '081234567892',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_PICKUP,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
        );
    }

    public function test_merchant_can_update_storefront_batch_settings(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post(route('storefront.settings.update'), [
            'is_storefront_enabled' => 1,
            'is_discoverable' => 1,
            'allow_pickup' => 1,
            'allow_delivery' => 0,
            'allow_request_order' => 0,
            'allow_scheduled_order' => 0,
            'allow_customer_po' => 1,
            'allow_reservation' => 0,
            'allow_custom_date' => 0,
            'quota_metric' => 'quantity',
            'preorder_quota_unit' => 'Porsi',
            'batch_dates_mode' => 'custom_dates',
            'custom_batch_dates' => "2026-09-25 : 100 : Batch Jumat 1\n2026-10-02 : 120 : Batch Jumat 2",
            'lead_time_hours' => 2,
            'cut_off_time' => '19:00',
            'daily_order_quota' => 100,
            'min_order_amount' => 15000,
            'order_auto_cancel_minutes' => 45,
        ]);

        $response->assertRedirect();
        $this->setting->refresh();

        $this->assertFalse($this->setting->allow_custom_date);
        $this->assertEquals('quantity', $this->setting->quota_metric);
        $this->assertEquals('Porsi', $this->setting->preorder_quota_unit);
        $this->assertEquals('custom_dates', $this->setting->batch_dates_mode);
        $this->assertCount(2, $this->setting->custom_batch_dates);
        $this->assertEquals('2026-09-25', $this->setting->custom_batch_dates[0]['date']);
        $this->assertEquals(100, $this->setting->custom_batch_dates[0]['quota']);
    }

    public function test_batch_dates_use_pure_indonesian_day_names_and_months(): void
    {
        $response = $this->get("/b/{$this->business->slug}");

        $response->assertStatus(200);
        $response->assertSee('JUMAT');
        $response->assertSee('18 Sep');
        $response->assertSee('Pesan Bareng / Join Pre-Order');
        $response->assertSee('Salin Link Batch');
        $response->assertDontSee('FRIDAY');
    }

    public function test_shareable_batch_link_preselects_date_and_renders_join_po_hub(): void
    {
        $response = $this->get("/b/{$this->business->slug}?batch=2026-09-18");

        $response->assertStatus(200);
        $response->assertSee('Pre-Order Terbuka');
        $response->assertSee('scheduled_date: \'2026-09-18\'', false);
    }

    public function test_office_group_buying_link_renders_group_name_and_prefills_office_field(): void
    {
        $response = $this->get("/b/{$this->business->slug}?batch=2026-09-18&group=PT+Telkom+Indonesia+Lt+8");

        $response->assertStatus(200);
        $response->assertSee('Pesanan Kantor: PT Telkom Indonesia Lt 8');
        $response->assertSee('group_name: "PT Telkom Indonesia Lt 8"', false);
    }

    public function test_order_creation_preserves_item_level_recipient_notes_for_office_colleagues(): void
    {
        $orderService = app(CommerceOrderService::class);

        $order = $orderService->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Koordinator Kantor (Budi)',
                'phone' => '081299887766',
                'email' => 'budi.office@telkom.co.id',
                'address' => 'Gedung Telkom Lt. 8, Jakarta Selatan',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'notes' => 'Budi - Lt. 4 (Pedas)',
                ],
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Siti - Divisi Keuangan (Tanpa Sambal)',
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            scheduledDate: '2026-09-18',
            scheduledTimeSlot: '09:00 - 11:00',
            paymentMethodId: $this->paymentMethod->id,
            options: [
                'notes' => '[Kantor: PT Telkom] Titip di resepsionis lantai 1',
                'shipping_fee' => 0,
            ]
        );

        $this->assertInstanceOf(CommerceOrder::class, $order);
        $this->assertEquals('2026-09-18', $order->scheduled_date->toDateString());
        $this->assertStringContainsString('[Kantor: PT Telkom]', $order->notes);

        $order->load('items');
        $this->assertCount(2, $order->items);

        $this->assertEquals('Budi - Lt. 4 (Pedas)', $order->items[0]->notes);
        $this->assertEquals(1, $order->items[0]->quantity);

        $this->assertEquals('Siti - Divisi Keuangan (Tanpa Sambal)', $order->items[1]->notes);
        $this->assertEquals(2, $order->items[1]->quantity);
    }

    public function test_multi_office_orders_consume_shared_150_pcs_quota_and_reject_when_limit_reached(): void
    {
        $service = new CommerceOrderService();

        // 1. Kantor Mandiri orders 100 PCS
        $orderMandiri = $service->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Ahmad Mandiri',
                'phone' => '081234567890',
                'email' => 'ahmad@mandiri.co.id',
                'address' => 'Plaza Mandiri Lantai 8, Jakarta',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'notes' => 'Porsi Karyawan Mandiri',
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
            options: ['notes' => '[Kantor: Kantor Mandiri]']
        );
        $this->assertInstanceOf(CommerceOrder::class, $orderMandiri);

        // Verify remaining quota is 50 PCS in landing page
        $resMid = $this->get("/b/{$this->business->slug}");
        $resMid->assertOk();
        $resMid->assertSee('Sisa 50 PCS');

        // 2. Kantor BCA orders 50 PCS with different office location
        $orderBca = $service->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Budi BCA',
                'phone' => '081987654321',
                'email' => 'budi@bca.co.id',
                'address' => 'Menara BCA Lantai 12, Grand Indonesia',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 50,
                    'notes' => 'Porsi Karyawan BCA',
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
            options: ['notes' => '[Kantor: Kantor BCA]']
        );
        $this->assertInstanceOf(CommerceOrder::class, $orderBca);

        // Verify batch is now sold out (0 PCS left)
        $resFull = $this->get("/b/{$this->business->slug}");
        $resFull->assertOk();
        $resFull->assertSee('Penuh');

        // 3. Kantor C tries to order 1 PCS -> Must be rejected cleanly
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Sisa kuota untuk batch tanggal');
        $this->expectExceptionMessage('tersisa 0 PCS');

        $service->createScheduledOrder(
            business: $this->business,
            customerData: [
                'name' => 'Charlie BNI',
                'phone' => '08111222333',
                'email' => 'charlie@bni.co.id',
                'address' => 'Gedung Grha BNI',
            ],
            itemsData: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
            fulfillmentType: CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
            scheduledDate: '2026-09-18',
            paymentMethodId: $this->paymentMethod->id,
            options: ['notes' => '[Kantor: Kantor BNI]']
        );
    }

    public function test_storefront_landing_page_does_not_leak_arrow_function_scripts_in_dom(): void
    {
        $response = $this->get("/b/{$this->business->slug}?batch=2026-09-18&group=Kantor+Mandiri");
        $response->assertOk();

        // Must not leak the raw javascript string into DOM
        $response->assertDontSee("showToast('Tautan Pre-Order berhasil disalin!");
        $response->assertDontSee("}).catch(() =>");
        $response->assertDontSee("prompt('Salin tautan Pre-Order:'");

        // Must render clean interactive elements
        $response->assertSee('Pesan Bareng / Join Pre-Order');
        $response->assertSee('Salin Link Batch');
        $response->assertSee('Ajak Teman Kantor (WA)');
        $response->assertSee('Pesanan Kantor: Kantor Mandiri');
    }
}

