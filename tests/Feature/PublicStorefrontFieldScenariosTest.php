<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceReservation;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\CustomerPoBatch;
use App\Models\GlobalCustomer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PublicStorefrontFieldScenariosTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private Unit $pcsUnit;
    private GlobalCustomer $customer;
    private CommercePaymentMethod $qrisPayment;
    private CommercePaymentMethod $bankPayment;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Pemilik Bisnis Nusantara',
            'email' => 'owner@nusantara.com',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Nusantara Multi Bisnis',
            'slug' => 'nusantara-multi-bisnis',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_primary' => true,
        ]);

        $this->pcsUnit = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->qrisPayment = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'qris',
            'bank_name' => 'QRIS Cooca Pay',
            'account_holder' => 'Nusantara Multi Bisnis',
            'instructions' => 'Buka aplikasi e-wallet atau m-Banking Anda, scan barcode QRIS resmi toko kami.',
            'is_active' => true,
        ]);

        $this->bankPayment = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '8881234567',
            'account_holder' => 'PT Nusantara Multi Bisnis',
            'instructions' => 'Transfer tepat hingga 3 digit terakhir untuk verifikasi otomatis.',
            'is_active' => true,
        ]);

        $this->customer = GlobalCustomer::create([
            'google_id' => 'google-user-scenario-field',
            'name' => 'Budi Hartono',
            'email' => 'budi.hartono@gmail.com',
            'phone' => '081299887766',
            'phone_verified_at' => now(),
        ]);
    }

    /**
     * Skenario 1: F&B Restoran / Kafe (Dine-in Table Booking + Takeaway / Delivery Order)
     */
    public function test_scenario_1_fnb_restaurant_reservation_and_pickup_checkout(): void
    {
        $table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => '05',
            'name' => 'Meja VIP Balcony',
            'capacity' => 4,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_reservation' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'available_slots' => ['12:00 - 14:00 (Makan Siang)', '18:30 - 20:30 (Makan Malam)'],
        ]);

        $foodProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->pcsUnit->id,
            'name' => 'Nasi Goreng Spesial Wagyu',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 55000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $foodProduct->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        // 1.1 Kunjungan Halaman Depan: Pastikan Meja #05 tampil di dropdown reservasi
        $pageResponse = $this->get("/b/{$this->business->slug}");
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Meja VIP Balcony');
        $pageResponse->assertSee('Kapasitas 4 Orang');

        // 1.2 Pelanggan Reservasi Meja #05
        $resPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'customer_email' => 'budi.hartono@gmail.com',
            'reservation_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'time_slot' => '18:30 - 20:30 (Makan Malam)',
            'guest_count' => 4,
            'pos_table_id' => $table->id,
            'notes' => 'Tolong sediakan baby chair 1 pcs.',
        ];

        $resSubmit = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/reservasi", $resPayload);

        $resSubmit->assertStatus(200);
        $resSubmit->assertJsonPath('success', true);

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $this->business->id,
            'pos_table_id' => $table->id,
            'guest_count' => 4,
            'time_slot' => '18:30 - 20:30 (Makan Malam)',
        ]);

        // 1.3 Pelanggan Memesan Makanan untuk Ambil Sendiri (Takeaway / Pickup)
        $orderPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'customer_email' => 'budi.hartono@gmail.com',
            'fulfillment_type' => 'pickup',
            'payment_method_id' => $this->qrisPayment->id,
            'notes' => 'Cabai rawit dipisah.',
            'items' => [
                [
                    'product_id' => $foodProduct->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $orderSubmit = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $orderPayload);

        $orderSubmit->assertStatus(200);
        $orderSubmit->assertJsonPath('success', true);
        $orderSubmit->assertJsonPath('order.total_amount', 110000);
    }

    /**
     * Skenario 2: Retail FMCG / Toko Online (Minimum Order & Aturan Bebas Ongkir)
     */
    public function test_scenario_2_retail_fmcg_minimum_order_and_free_shipping(): void
    {
        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'min_order_amount' => 100000, // Minimal belanja 100rb
        ]);

        $shippingRule = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Express Toko',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 15000,
            'min_order_for_free' => 200000, // Bebas ongkir jika belanja >= 200rb
            'is_active' => true,
        ]);

        $retailProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->pcsUnit->id,
            'name' => 'Minyak Goreng Premium 2L',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 40000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $retailProduct->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // 2.1 Belanja di bawah minimum (2 x 40.000 = 80.000 < 100.000) -> Gagal
        $belowMinPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'fulfillment_type' => 'pickup',
            'items' => [
                ['product_id' => $retailProduct->id, 'quantity' => 2],
            ],
        ];

        $failResponse = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $belowMinPayload);

        $failResponse->assertStatus(422);

        // 2.2 Belanja mencapai minimum tapi belum bebas ongkir (3 x 40.000 = 120.000)
        // Ongkir 15.000 berlaku -> Total = 135.000
        $withShippingPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Mawar No. 12, Jakarta',
            'shipping_rule_id' => $shippingRule->id,
            'items' => [
                ['product_id' => $retailProduct->id, 'quantity' => 3],
            ],
        ];

        $withShippingRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $withShippingPayload);

        $withShippingRes->assertStatus(200);
        $withShippingRes->assertJsonPath('order.total_amount', 135000);

        // 2.3 Belanja mencapai threshold bebas ongkir (5 x 40.000 = 200.000 >= 200.000)
        // Ongkir = 0 -> Total = 200.000
        $freeShippingPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Jl. Mawar No. 12, Jakarta',
            'shipping_rule_id' => $shippingRule->id,
            'items' => [
                ['product_id' => $retailProduct->id, 'quantity' => 5],
            ],
        ];

        $freeShippingRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $freeShippingPayload);

        $freeShippingRes->assertStatus(200);
        $freeShippingRes->assertJsonPath('order.total_amount', 200000);
    }

    /**
     * Skenario 3: Jasa / Salon / Barbershop / Bengkel (Booking Layanan Terpilih + Kursi/Bay)
     */
    public function test_scenario_3_service_booking_with_service_link(): void
    {
        $barberChair = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'K1',
            'name' => 'Kursi Pangkas Master #1',
            'capacity' => 1,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $serviceProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->pcsUnit->id,
            'name' => 'Gentlemen Signature Haircut & Shave',
            'type' => Product::TYPE_SERVICE,
            'selling_price' => 85000,
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_reservation' => true,
        ]);

        // Pelanggan mengetuk tombol Reservasi pada kartu layanan
        $bookingPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'reservation_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'time_slot' => '14:00 - 16:00 (Siang)',
            'guest_count' => 1,
            'pos_table_id' => $barberChair->id,
            'product_id' => $serviceProduct->id,
            'notes' => 'Booking Layanan: Gentlemen Signature Haircut & Shave',
        ];

        $bookingRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/reservasi", $bookingPayload);

        $bookingRes->assertStatus(200);
        $bookingRes->assertJsonPath('success', true);

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $this->business->id,
            'pos_table_id' => $barberChair->id,
            'product_id' => $serviceProduct->id,
            'guest_count' => 1,
        ]);
    }

    /**
     * Skenario 4: Manufaktur / Percetakan / Katering PO (B2B Multi-Batch PO & RFQ)
     */
    public function test_scenario_4_manufacturing_b2b_request_order_and_customer_po_batch(): void
    {
        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_request_order' => true,
            'allow_customer_po' => true,
        ]);

        // 4.1 Permintaan Pesanan Khusus (Request Order / RFQ)
        $rfqPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'customer_email' => 'budi.hartono@gmail.com',
            'fulfillment_type' => 'merchant_delivery',
            'shipping_address' => 'Gudang Logistik Kawasan Industri Blok C2, Cikarang',
            'scheduled_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'notes' => 'Cetak kemasan sablon 2 warna dengan bahan kraft tebal 350gsm.',
            'items' => [
                [
                    'product_name' => 'Box Kemasan Karton Custom Sablon',
                    'quantity' => 1000,
                    'notes' => 'Ukuran 25x20x10 cm',
                ],
            ],
        ];

        $rfqRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/request-order", $rfqPayload);

        $rfqRes->assertStatus(200);
        $rfqRes->assertJsonPath('success', true);
        $this->assertDatabaseHas('commerce_orders', [
            'business_id' => $this->business->id,
            'fulfillment_type' => 'merchant_delivery',
        ]);

        // 4.2 Customer PO dengan 2 Batch Jadwal Pengiriman Bertahap
        $batchDate1 = Carbon::now()->addDays(7)->format('Y-m-d');
        $batchDate2 = Carbon::now()->addDays(14)->format('Y-m-d');

        $poPayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'customer_email' => 'budi.hartono@gmail.com',
            'company_name' => 'PT Makmur Logistik Sentosa',
            'customer_po_number' => 'PO-MLS-2026-09-001',
            'shipping_address' => 'Gudang Pusat Jakarta',
            'payment_method_id' => $this->bankPayment->id,
            'notes' => 'Termin pembayaran Net 30 hari via invoice.',
            'items' => [
                [
                    'product_name' => 'Seragam Kerja Lapangan Drill',
                    'quantity' => 500,
                    'unit_price' => 120000,
                    'notes' => 'Bordir logo perusahaan di dada kiri',
                ],
            ],
            'batches' => [
                [
                    'scheduled_date' => $batchDate1,
                    'scheduled_time_slot' => '09:00 - 12:00',
                    'quantity' => 250,
                    'shipping_address' => 'Gudang Drop Point 1 Surabaya',
                ],
                [
                    'scheduled_date' => $batchDate2,
                    'scheduled_time_slot' => '13:00 - 16:00',
                    'quantity' => 250,
                    'shipping_address' => 'Gudang Drop Point 2 Jakarta',
                ],
            ],
        ];

        $poRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/customer-po", $poPayload);

        $poRes->assertStatus(200);
        $poRes->assertJsonPath('success', true);

        $createdOrder = CommerceOrder::where('business_id', $this->business->id)
            ->where('customer_po_number', 'PO-MLS-2026-09-001')
            ->first();

        $this->assertNotNull($createdOrder);
        $this->assertEquals(2, $createdOrder->batches()->count());
    }

    /**
     * Skenario 5: Toko Tutup / Hari Libur Operasional & Mode Preview Publikasi
     */
    public function test_scenario_5_store_closed_today_and_preview_mode_handling(): void
    {
        // 5.1 Toko berstatus draft / belum dipublikasikan
        $landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => false,
            'font_family' => 'Inter',
            'headline' => 'Pelopor Industri Kreatif',
            'theme_color' => '#007AFF',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'operating_days' => ['monday'], // Hanya buka hari senin
        ]);

        // 5.1 Pengunjung umum mengakses halaman draft tanpa hak akses -> 404
        $publicDraftRes = $this->get("/b/{$this->business->slug}");
        $publicDraftRes->assertStatus(404);

        // 5.2 Pemilik bisnis login mengakses dengan parameter ?preview=1 -> 200 & banner Mode Preview
        $ownerPreviewRes = $this->actingAs($this->owner)
            ->get("/b/{$this->business->slug}?preview=1");
        $ownerPreviewRes->assertStatus(200);
        $ownerPreviewRes->assertSee('Mode Preview Bisnis');

        // 5.3 Toko sudah dipublikasikan: pengunjung umum dapat mengakses, dan jika hari ini libur, banner tutup muncul
        $landingPage->update(['is_published' => true]);

        $publishedRes = $this->get("/b/{$this->business->slug}");
        $publishedRes->assertStatus(200);
        $publishedRes->assertDontSee('Mode Preview Bisnis');

        $currentDay = strtolower(Carbon::now()->englishDayOfWeek);
        if ($currentDay !== 'monday') {
            $publishedRes->assertSee('Outlet toko libur hari ini');
        }
    }

    /**
     * Skenario 6: Toko dengan Lead Time Ketat (H+2)
     */
    public function test_scenario_6_lead_time_hours_enforced(): void
    {
        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_scheduled_order' => true,
            'lead_time_hours' => 48, // Minimal H+2
        ]);

        $customCake = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->pcsUnit->id,
            'name' => 'Kue Pengantin 3 Tingkat',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 1500000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $customCake->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        // Tanggal terlalu cepat (besok, padahal minimal 48 jam)
        $invalidDatePayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => Carbon::now()->addHours(12)->format('Y-m-d'),
            'items' => [
                ['product_id' => $customCake->id, 'quantity' => 1],
            ],
        ];

        $invalidRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $invalidDatePayload);

        $invalidRes->assertStatus(422);

        // Tanggal valid (H+3 > 48 jam)
        $validDatePayload = [
            'customer_name' => 'Budi Hartono',
            'customer_phone' => '081299887766',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'items' => [
                ['product_id' => $customCake->id, 'quantity' => 1],
            ],
        ];

        $validRes = $this->actingAs($this->customer, 'customer')
            ->postJson("/b/{$this->business->slug}/checkout", $validDatePayload);

        $validRes->assertStatus(200);
        $validRes->assertJsonPath('success', true);
    }

    public function test_landing_page_renders_with_various_operational_hours_structures(): void
    {
        // Case 1: Associative array format (as in Citra Busana Konveksi error report)
        $landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => true,
            'headline' => 'Citra Busana Konveksi',
            'subheadline' => 'Konveksi terpercaya',
            'operational_hours' => [
                'monday' => ['open' => '07:00', 'close' => '21:00'],
                'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                'thursday' => ['open' => '07:00', 'close' => '21:00'],
                'friday' => ['open' => '07:00', 'close' => '21:00'],
                'saturday' => ['open' => '07:00', 'close' => '21:00'],
                'sunday' => ['open' => '07:00', 'close' => '21:00'],
            ],
        ]);

        $response = $this->get("/{$this->business->slug}");
        $response->assertStatus(200);
        $response->assertSee('Jadwal Operasional');
        $response->assertSee('Senin');

        // Case 2: Standard array format with 'day', 'hours', 'is_open'
        $landingPage->update([
            'operational_hours' => [
                ['day' => 'Senin', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
                ['day' => 'Minggu', 'hours' => 'Tutup', 'is_open' => false],
            ],
        ]);

        $response2 = $this->get("/{$this->business->slug}");
        $response2->assertStatus(200);
        $response2->assertSee('Senin');
        $response2->assertSee('Tutup');
    }
}
