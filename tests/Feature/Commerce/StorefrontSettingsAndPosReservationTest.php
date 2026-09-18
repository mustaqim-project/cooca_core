<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\CommerceReservation;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StorefrontSettingsAndPosReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Storefront Owner',
            'email' => 'owner_storefront@example.com',
            'email_verified_at' => now(),
            'phone' => '081234567899',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Resto & Toko Berkah',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'slug' => 'resto-toko-berkah',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_active' => true,
        ]);
    }

    public function test_storefront_settings_renders_with_bento_tabs_and_settlement_data(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('storefront.settings.index'));

        $response->assertOk();
        $response->assertViewHas('unsettledData');
        $response->assertViewHas('bankAccounts');
        $response->assertViewHas('recentSettlements');
        $response->assertViewHas('isGatewayConfigured');

        // Check for Apple HIG Bento tab titles & microcopy
        $response->assertSee('Status &amp; Visibilitas Toko', false);
        $response->assertSee('Saldo Gateway &amp; Penarikan Dana', false);
        $response->assertSee('Mode Transaksi Spesifik Industri', false);
        $response->assertSee('Opsi Penyerahan Pesanan', false);
    }

    public function test_storefront_settings_update_persists_settings(): void
    {
        $payload = [
            'is_storefront_enabled' => 1,
            'is_discoverable' => 1,
            'allow_pickup' => 1,
            'allow_delivery' => 1,
            'allow_request_order' => 1,
            'allow_scheduled_order' => 1,
            'allow_customer_po' => 1,
            'allow_reservation' => 0,
            'min_order_amount' => 50000,
            'order_auto_cancel_minutes' => 45,
            'announcement_text' => 'Selamat datang di Toko Kami!',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('storefront.settings.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commerce_store_settings', [
            'business_id' => $this->business->id,
            'allow_reservation' => false,
            'min_order_amount' => 50000,
            'order_auto_cancel_minutes' => 45,
            'announcement_text' => 'Selamat datang di Toko Kami!',
        ]);
    }

    public function test_pos_tables_index_loads_today_reservations_in_view_and_json(): void
    {
        $table = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'T-01',
            'name' => 'Meja VIP 1',
            'capacity' => 4,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $reservation = CommerceReservation::create([
            'business_id' => $this->business->id,
            'reservation_code' => 'RSV-2026-001',
            'customer_name' => 'Ibu Fatimah',
            'customer_phone' => '08123456789',
            'guest_count' => 4,
            'reservation_date' => Carbon::today()->toDateString(),
            'time_slot' => '19:00 - 21:00',
            'status' => CommerceReservation::STATUS_CONFIRMED,
            'pos_table_id' => $table->id,
        ]);

        // 1. Web HTML view test
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.tables.index'));

        $response->assertOk();
        $response->assertViewHas('todayReservations');
        $response->assertSee('Ibu Fatimah');
        $response->assertSee('Buku Reservasi');
        $response->assertSee('Reservasi Hari Ini');

        // 2. POS Terminal AJAX JSON test
        $jsonResponse = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('pos.tables.index'));

        $jsonResponse->assertOk();
        $jsonResponse->assertJsonPath('success', true);
        $jsonResponse->assertJsonPath('tables.0.today_reservation.reservation_code', 'RSV-2026-001');
        $jsonResponse->assertJsonPath('tables.0.today_reservation.customer_name', 'Ibu Fatimah');
    }
}
