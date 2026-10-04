<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosShift;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosTerminalIndustryGatingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner POS Gating Test',
            'email'             => 'owner_pos_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }

    private function createTenant(string $name, string $templateCode, array $disabledModules = []): array
    {
        $ownerRole = Role::where('slug', 'owner')->first();

        $business = Business::create([
            'name'              => $name,
            'slug'              => Str::slug($name) . '-' . Str::random(5),
            'email'             => 'biz_' . Str::random(6) . '@test.local',
            'phone'             => '62813' . rand(10000000, 99999999),
            'status'            => 'active',
            'template_code'     => $templateCode,
            'disabled_modules'  => !empty($disabledModules) ? $disabledModules : ModuleRegistry::getDisabledModulesForTemplate($templateCode),
        ]);

        $location = Location::create([
            'business_id' => $business->id,
            'name'        => 'Outlet Utama ' . $name,
            'slug'        => Str::slug($name) . '-loc',
            'type'        => 'outlet',
            'is_primary'  => true,
            'is_active'   => true,
        ]);

        $this->owner->businesses()->attach($business->id, [
            'id'                  => (string) Str::uuid(),
            'role'                => 'owner',
            'role_id'             => $ownerRole?->id,
            'primary_location_id' => $location->id,
        ]);

        $this->owner->update(['active_business_id' => $business->id]);

        $shift = PosShift::create([
            'business_id'   => $business->id,
            'location_id'   => $location->id,
            'user_id'       => $this->owner->id,
            'shift_number'  => 'SHF-' . strtoupper(Str::random(6)),
            'opening_cash'  => 100000,
            'status'        => 'open',
            'opened_at'     => now(),
        ]);

        return [$business, $location, $shift];
    }

    public function test_retail_reseller_terminal_hides_fnb_features_completely(): void
    {
        [$retailBiz, $location, $shift] = $this->createTenant('Toko Retail Pakaian', 'retail_reseller');
        $this->assertFalse($retailBiz->isFoodIndustry());
        $this->assertFalse($retailBiz->hasDineInFeature());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $retailBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($retailBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $retailBiz->id,
                'business_id'        => $retailBiz->id,
            ])
            ->get(route('pos.terminal'));

        $response->assertStatus(200);

        // 1. Topbar and drawer F&B buttons must NOT exist
        $response->assertDontSee('Order QR');
        $response->assertDontSee('Pesanan Masuk dari Meja QR');
        $response->assertDontSee('Daftar Meja Resto');
        $response->assertDontSee('Buku Reservasi Storefront');

        // 2. Cart F&B buttons must NOT exist
        $response->assertDontSee('Makan di Tempat / Dine In');
        $response->assertDontSee('Pilih / Ganti Meja');
        $response->assertDontSee('Bungkus / Takeaway');

        // 3. Multi-channel online delivery selector bar must NOT exist
        $response->assertDontSee('gofood');
        $response->assertDontSee('grabfood');
        $response->assertDontSee('shopeefood');

        // 4. Modals for F&B must NOT be rendered in HTML
        $response->assertDontSee('MODAL: INCOMING QR TABLE ORDERS DRAWER', false);
        $response->assertDontSee('MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS', false);
        $response->assertDontSee('Pembayaran Pesanan Meja');

        // 5. Alpine JS state must be initialized for non-food
        $response->assertSee('hasDineIn: false', false);
        $response->assertSee('isFoodIndustry: false', false);
        $response->assertSee("orderType: 'takeaway'", false);

        // 6. Service vertical modal must NOT be rendered for Retail
        $response->assertDontSee('MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY)', false);

        // 7. Item detail modal title must be generic notes, NOT pharmacy medicine dosage
        $response->assertSee('Detail &amp; Catatan Item', false);
        $response->assertDontSee('Detail &amp; Dosis Obat', false);
    }

    public function test_service_workshop_terminal_shows_spk_and_hides_fnb(): void
    {
        [$workshopBiz, $location, $shift] = $this->createTenant('Bengkel Mobil Sejahtera', 'service_workshop');
        $this->assertTrue($workshopBiz->isWorkshop());
        $this->assertFalse($workshopBiz->isFoodIndustry());
        $this->assertFalse($workshopBiz->hasDineInFeature());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $workshopBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($workshopBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $workshopBiz->id,
                'business_id'        => $workshopBiz->id,
            ])
            ->get(route('pos.terminal'));

        $response->assertStatus(200);

        // 1. Workshop SPK feature must be present
        $response->assertSee('SPK Bengkel');

        // 2. Service vertical modal MUST be rendered for Workshop
        $response->assertSee('MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY)', false);
        $response->assertSee('SPK Bengkel &amp; Kendaraan', false);

        // 3. F&B Resto features must NOT exist
        $response->assertDontSee('Order QR');
        $response->assertDontSee('Pesanan Masuk dari Meja QR');
        $response->assertDontSee('Makan di Tempat / Dine In');
        $response->assertDontSee('MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS', false);
    }

    public function test_retail_pharmacy_terminal_shows_pharmacy_mode_and_hides_fnb(): void
    {
        [$pharmacyBiz, $location, $shift] = $this->createTenant('Apotek Berkah Sehat', 'retail_pharmacy');
        $this->assertTrue($pharmacyBiz->isPharmacy());
        $this->assertFalse($pharmacyBiz->isFoodIndustry());
        $this->assertFalse($pharmacyBiz->hasDineInFeature());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $pharmacyBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($pharmacyBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $pharmacyBiz->id,
                'business_id'        => $pharmacyBiz->id,
            ])
            ->get(route('pos.terminal'));

        $response->assertStatus(200);

        // 1. Pharmacy badge must be present
        $response->assertSee('Mode Apotek');

        // 2. Pharmacy specific item detail modal title & fields must exist
        $response->assertSee('Detail &amp; Dosis Obat', false);
        $response->assertSee(__('pos.batch_number'));
        $response->assertSee(__('pos.dosage_instructions'));

        // 3. Service vertical modal must NOT be rendered for Pharmacy
        $response->assertDontSee('MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY)', false);

        // 4. F&B Resto features must NOT exist
        $response->assertDontSee('Order QR');
        $response->assertDontSee('Makan di Tempat / Dine In');
        $response->assertDontSee('MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS', false);
    }

    public function test_laundry_terminal_shows_laundry_service_modal(): void
    {
        [$laundryBiz, $location, $shift] = $this->createTenant('Klinik Cuci Laundry', 'service_laundry');
        $this->assertTrue($laundryBiz->isLaundry());
        $this->assertFalse($laundryBiz->isFoodIndustry());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $laundryBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($laundryBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $laundryBiz->id,
                'business_id'        => $laundryBiz->id,
            ])
            ->get(route('pos.terminal'));

        $response->assertStatus(200);

        // 1. Laundry trigger button
        $response->assertSee('Timbangan Cucian &amp; Lokasi Rak', false);

        // 2. Service modal rendered for laundry
        $response->assertSee('MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY)', false);
        $response->assertSee('Layanan Laundry Kiloan', false);
    }

    public function test_fnb_resto_terminal_shows_all_resto_features_properly(): void
    {
        [$restoBiz, $location, $shift] = $this->createTenant('Restoran Rasa Nusantara', 'fnb_resto');
        $this->assertTrue($restoBiz->isFoodIndustry());
        $this->assertTrue($restoBiz->hasDineInFeature());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $restoBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($restoBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $restoBiz->id,
                'business_id'        => $restoBiz->id,
            ])
            ->get(route('pos.terminal'));

        $response->assertStatus(200);

        // 1. Topbar and drawer F&B buttons must exist
        $response->assertSee('Order QR');
        $response->assertSee('Pesanan Masuk dari Meja QR');
        $response->assertSee('Daftar Meja Resto');
        $response->assertSee('Buku Reservasi Storefront');

        // 2. Cart F&B buttons must exist
        $response->assertSee('Makan di Tempat / Dine In');
        $response->assertSee('Bungkus / Takeaway');

        // 3. Multi-channel online delivery selector bar must exist
        $response->assertSee('gofood');
        $response->assertSee('grabfood');
        $response->assertSee('shopeefood');

        // 4. Modals for F&B must be rendered in HTML
        $response->assertSee('MODAL: INCOMING QR TABLE ORDERS DRAWER', false);
        $response->assertSee('MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS', false);

        // 5. Alpine JS state must be initialized for food dine-in
        $response->assertSee('hasDineIn: true', false);
        $response->assertSee('isFoodIndustry: true', false);
        $response->assertSee("orderType: 'dine_in'", false);
    }

    public function test_pos_orders_view_renders_resto_table_badge_and_detail_card(): void
    {
        [$restoBiz, $location, $shift] = $this->createTenant('Restoran Rasa Nusantara', 'fnb_resto');
        $this->assertTrue($restoBiz->isFoodIndustry());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $restoBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($restoBiz, $membership);

        $order = PosOrder::create([
            'business_id'        => $restoBiz->id,
            'location_id'        => $location->id,
            'user_id'            => $this->owner->id,
            'order_number'       => 'ORD-TABLE-012',
            'order_date'         => now()->toDateString(),
            'ordered_at'         => now(),
            'status'             => PosOrder::STATUS_COMPLETED,
            'payment_status'     => 'paid',
            'order_type'         => 'dine_in',
            'table_or_reference' => '12',
            'subtotal'           => 150000,
            'total_amount'       => 150000,
            'paid_amount'        => 150000,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $restoBiz->id,
                'business_id'        => $restoBiz->id,
            ])
            ->get(route('pos.orders.index'));

        $response->assertStatus(200);

        // 1. Table badge rendered in list view
        $response->assertSee('Meja 12');

        // 2. Resto Dine-In card template rendered in detail modal
        $response->assertSee('Restoran &amp; Dine-In', false);
    }
}

