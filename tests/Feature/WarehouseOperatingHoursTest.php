<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use App\Support\TimezoneHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseOperatingHoursTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Toko',
            'email' => 'owner@tokogudang.test',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Gudang Sukses Makmur',
            'slug' => 'gudang-sukses-makmur',
            'timezone' => 'Asia/Jakarta',
            'operating_hours' => TimezoneHelper::defaultOperatingHours(),
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $membership = \App\Models\BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->owner->id)
            ->first();

        Context::setBusiness($this->business, $membership);
    }

    public function test_can_create_location_with_inherit_settings(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->post('/warehouse', [
                'name' => 'Gudang Pusat Jakarta',
                'type' => 'warehouse',
                'address' => 'Jl. Kebon Jeruk No. 12',
                'timezone_mode' => 'inherit',
                'operating_hours_mode' => 'inherit',
            ]);

        $response->assertRedirect('/warehouse');

        $location = Location::where('name', 'Gudang Pusat Jakarta')->first();
        $this->assertNotNull($location);
        $this->assertSame('inherit', $location->timezone_mode);
        $this->assertSame('inherit', $location->operating_hours_mode);
        $this->assertSame('Asia/Jakarta', $location->getEffectiveTimezone());
        $this->assertEquals($this->business->operating_hours, $location->getEffectiveOperatingHours());
    }

    public function test_can_create_and_update_location_with_custom_timezone_and_hours(): void
    {
        $customHours = TimezoneHelper::defaultOperatingHours();
        $customHours['monday']['periods'] = [
            ['start' => '08:00', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '22:00'],
        ];

        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->post('/warehouse', [
                'name' => 'Cabang Bali Denpasar',
                'type' => 'outlet',
                'address' => 'Jl. Sunset Road No. 88',
                'timezone_mode' => 'custom',
                'timezone' => 'Asia/Makassar',
                'operating_hours_mode' => 'custom',
                'operating_hours_json' => json_encode($customHours),
            ]);

        $response->assertRedirect('/warehouse');

        $location = Location::where('name', 'Cabang Bali Denpasar')->first();
        $this->assertNotNull($location);
        $this->assertSame('custom', $location->timezone_mode);
        $this->assertSame('Asia/Makassar', $location->timezone);
        $this->assertSame('Asia/Makassar', $location->getEffectiveTimezone());
        $this->assertSame('custom', $location->operating_hours_mode);

        $effectiveHours = $location->getEffectiveOperatingHours();
        $this->assertCount(2, $effectiveHours['monday']['periods']);
        $this->assertSame('08:00', $effectiveHours['monday']['periods'][0]['start']);
        $this->assertSame('13:00', $effectiveHours['monday']['periods'][1]['start']);

        // Test update back to inherit
        $updateResponse = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->put("/warehouse/{$location->id}", [
                'name' => 'Cabang Bali Denpasar Updated',
                'type' => 'outlet',
                'address' => 'Jl. Sunset Road No. 88 B',
                'timezone_mode' => 'inherit',
                'operating_hours_mode' => 'inherit',
            ]);

        $updateResponse->assertRedirect();
        $location->refresh();

        $this->assertSame('inherit', $location->timezone_mode);
        $this->assertSame('inherit', $location->operating_hours_mode);
        $this->assertSame('Asia/Jakarta', $location->getEffectiveTimezone());
    }

    public function test_settings_page_renders_cleanly_with_locations_and_edit_links(): void
    {
        $loc1 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Bandung Juanda',
            'slug' => 'outlet-bandung-juanda',
            'type' => 'outlet',
            'timezone_mode' => 'inherit',
            'operating_hours_mode' => 'inherit',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $loc2 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Logistik Surabaya',
            'slug' => 'gudang-logistik-surabaya',
            'type' => 'warehouse',
            'timezone_mode' => 'custom',
            'timezone' => 'Asia/Jakarta',
            'operating_hours_mode' => 'custom',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'current_business_id' => $this->business->id,
                'active_business_id' => $this->business->id,
            ])
            ->get('/settings');

        $response->assertOk();
        $response->assertSee('Outlet Bandung Juanda');
        $response->assertSee('Gudang Logistik Surabaya');
        $response->assertSee(route('warehouse.edit', $loc1));
        $response->assertSee(route('warehouse.edit', $loc2));
        $response->assertSee('Ubah Jadwal');
    }

    public function test_warehouse_edit_route_redirects_to_show_with_edit_flag(): void
    {
        $location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Medan Polonia',
            'slug' => 'cabang-medan-polonia',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'current_business_id' => $this->business->id,
                'active_business_id' => $this->business->id,
            ])
            ->get(route('warehouse.edit', $location));

        $response->assertRedirect(route('warehouse.show', ['location' => $location, 'edit' => 1]));
    }

    public function test_warehouse_edit_route_enforces_tenant_isolation(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Toko Sebelah',
            'slug' => 'toko-sebelah',
        ]);

        $foreignLocation = Location::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Cabang Toko Sebelah',
            'slug' => 'cabang-toko-sebelah',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'current_business_id' => $this->business->id,
                'active_business_id' => $this->business->id,
            ])
            ->get(route('warehouse.edit', $foreignLocation));

        $response->assertNotFound();
    }
}

