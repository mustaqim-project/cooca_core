<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceReservation;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
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

class CommerceReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $merchantUser;
    private Business $business;
    private Location $location;
    private PosTable $table1;
    private PosTable $table2;
    private CommerceStoreSetting $setting;
    private GlobalCustomer $globalCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Owner Kafe & Resto',
            'email' => 'owner@resto.com',
            'phone' => '081234343434',
            'password' => bcrypt('password123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Aroma Bistro & Cafe',
            'slug' => 'aroma-bistro',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->merchantUser->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_primary' => true,
        ]);

        $this->table1 = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => '01',
            'name' => 'Meja VIP Jendela',
            'capacity' => 4,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $this->table2 = PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => '02',
            'name' => 'Meja Outdoor Terbuka',
            'capacity' => 6,
            'status' => PosTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $this->setting = CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'allow_reservation' => true,
        ]);

        $this->globalCustomer = GlobalCustomer::create([
            'google_id' => 'google-user-reservation',
            'name' => 'Rina Wijaya',
            'email' => 'rina@gmail.com',
            'phone' => '081298765432',
            'phone_verified_at' => now(),
        ]);

        Context::setBusiness($this->business);
    }

    public function test_unauthenticated_reservation_fails_with_401(): void
    {
        $response = $this->postJson(route('public.storefront.reservation.submit', $this->business->slug), []);
        $response->assertStatus(401);
    }

    public function test_customer_can_submit_table_reservation(): void
    {
        $payload = [
            'customer_name' => 'Rina Wijaya',
            'customer_phone' => '081298765432',
            'customer_email' => 'rina@gmail.com',
            'reservation_date' => Carbon::now()->addDays(2)->toDateString(),
            'time_slot' => '19:00 - 21:00',
            'guest_count' => 4,
            'pos_table_id' => $this->table1->id,
            'notes' => 'Acara makan malam ulang tahun keluarga.',
        ];

        $response = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson(route('public.storefront.reservation.submit', $this->business->slug), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('reservation.customer_name', 'Rina Wijaya')
            ->assertJsonPath('reservation.guest_count', 4)
            ->assertJsonPath('reservation.status', CommerceReservation::STATUS_PENDING_CONFIRMATION)
            ->assertJsonPath('reservation.table.table_number', '01');

        $this->assertDatabaseHas('commerce_reservations', [
            'business_id' => $this->business->id,
            'customer_name' => 'Rina Wijaya',
            'customer_phone' => '6281298765432',
            'pos_table_id' => $this->table1->id,
            'status' => CommerceReservation::STATUS_PENDING_CONFIRMATION,
        ]);
    }

    public function test_collision_prevention_rejects_double_booking_same_table_and_slot(): void
    {
        $targetDate = Carbon::now()->addDays(3)->toDateString();
        $targetSlot = '18:30 - 20:30';

        // 1. Initial reservation on Table 1
        $rsvService = app(\App\Domain\Commerce\Storefront\ReservationBookingService::class);
        $rsvService->createReservation(
            business: $this->business,
            customerData: ['name' => 'Budi Santoso', 'phone' => '081255551111'],
            date: $targetDate,
            timeSlot: $targetSlot,
            guestCount: 2,
            tableId: $this->table1->id
        );

        // 2. Second customer attempts to book same table and slot
        $payload2 = [
            'customer_name' => 'Andi Pratama',
            'customer_phone' => '081266662222',
            'reservation_date' => $targetDate,
            'time_slot' => $targetSlot,
            'guest_count' => 3,
            'pos_table_id' => $this->table1->id,
        ];

        $response2 = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson(route('public.storefront.reservation.submit', $this->business->slug), $payload2);

        $response2->assertStatus(422)
            ->assertJsonPath('success', false);

        // However, Table 2 on the same slot should be accepted!
        $payload3 = [
            'customer_name' => 'Andi Pratama',
            'customer_phone' => '081266662222',
            'reservation_date' => $targetDate,
            'time_slot' => $targetSlot,
            'guest_count' => 3,
            'pos_table_id' => $this->table2->id,
        ];

        $response3 = $this->actingAs($this->globalCustomer, 'customer')
            ->postJson(route('public.storefront.reservation.submit', $this->business->slug), $payload3);
        $response3->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_merchant_can_confirm_seat_and_complete_reservation(): void
    {
        $rsvService = app(\App\Domain\Commerce\Storefront\ReservationBookingService::class);
        $reservation = $rsvService->createReservation(
            business: $this->business,
            customerData: ['name' => 'Dewi Lestari', 'phone' => '081277773333'],
            date: Carbon::now()->addDay()->toDateString(),
            timeSlot: '12:00 - 14:00',
            guestCount: 4,
            tableId: $this->table1->id
        );

        $this->assertEquals(CommerceReservation::STATUS_PENDING_CONFIRMATION, $reservation->status);
        $this->assertEquals(PosTable::STATUS_AVAILABLE, $this->table1->fresh()->status);

        // 1. Merchant confirms reservation
        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.reservations.status', $reservation), ['status' => 'confirmed'])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertEquals(CommerceReservation::STATUS_CONFIRMED, $reservation->status);

        // 2. Guests arrive -> merchant seats them
        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.reservations.status', $reservation), ['status' => 'seated'])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertEquals(CommerceReservation::STATUS_SEATED, $reservation->status);
        $this->assertEquals(PosTable::STATUS_OCCUPIED, $this->table1->fresh()->status);

        // 3. Service finishes -> merchant completes reservation
        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.reservations.status', $reservation), ['status' => 'completed'])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertEquals(CommerceReservation::STATUS_COMPLETED, $reservation->status);
        $this->assertEquals(PosTable::STATUS_AVAILABLE, $this->table1->fresh()->status);
    }

    public function test_merchant_can_assign_table_to_open_reservation(): void
    {
        $rsvService = app(\App\Domain\Commerce\Storefront\ReservationBookingService::class);
        $reservation = $rsvService->createReservation(
            business: $this->business,
            customerData: ['name' => 'Open Booking Customer', 'phone' => '081288884444'],
            date: Carbon::now()->addDays(4)->toDateString(),
            timeSlot: '20:00 - 22:00',
            guestCount: 5,
            tableId: null // No table selected initially
        );

        $this->assertNull($reservation->pos_table_id);

        $this->actingAs($this->merchantUser)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->merchantUser->id,
            ])
            ->post(route('storefront.reservations.assign_table', $reservation), [
                'pos_table_id' => $this->table2->id,
            ])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertEquals($this->table2->id, $reservation->pos_table_id);
    }

    public function test_check_availability_endpoint_returns_available_tables(): void
    {
        $targetDate = Carbon::now()->addDays(5)->toDateString();
        $targetSlot = '13:00 - 15:00';

        // Table 1 is booked
        $rsvService = app(\App\Domain\Commerce\Storefront\ReservationBookingService::class);
        $rsvService->createReservation(
            business: $this->business,
            customerData: ['name' => 'Booked Client', 'phone' => '081200001111'],
            date: $targetDate,
            timeSlot: $targetSlot,
            guestCount: 2,
            tableId: $this->table1->id
        );

        // Query check availability
        $response = $this->getJson(route('public.storefront.reservation.check', [
            'slug' => $this->business->slug,
            'date' => $targetDate,
            'time_slot' => $targetSlot,
            'guest_count' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_available', true);

        $tables = $response->json('available_tables');
        $this->assertCount(1, $tables);
        $this->assertEquals('02', $tables[0]['table_number']); // Only Table 2 is available
    }

    public function test_tenant_isolation_prevents_unauthorized_merchant_from_updating_other_business_reservations(): void
    {
        $otherMerchant = User::create([
            'name' => 'Other Cafe Owner',
            'email' => 'other@cafe.com',
            'phone' => '081277779999',
            'password' => bcrypt('password123'),
        ]);
        $otherMerchant->forceFill(['email_verified_at' => now()])->save();

        $otherBiz = Business::create([
            'name' => 'Other Cafe',
            'slug' => 'other-cafe',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);
        $otherBiz->users()->attach($otherMerchant->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        $rsvService = app(\App\Domain\Commerce\Storefront\ReservationBookingService::class);
        $reservation = $rsvService->createReservation(
            business: $this->business,
            customerData: ['name' => 'Client A', 'phone' => '081299990000'],
            date: Carbon::now()->addDay()->toDateString(),
            timeSlot: '11:00 - 13:00',
            guestCount: 2
        );

        // Other merchant attempts to update status
        $response = $this->actingAs($otherMerchant)
            ->withSession([
                'active_business_id' => $otherBiz->id,
                'auth_wa_otp_verified_user_id' => $otherMerchant->id,
            ])
            ->post(route('storefront.reservations.status', $reservation), [
                'status' => 'confirmed',
            ]);

        $response->assertForbidden();
    }
}
