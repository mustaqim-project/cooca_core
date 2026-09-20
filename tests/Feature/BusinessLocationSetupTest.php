<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\GeoLocationService;
use App\Models\Business;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BusinessLocationSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
    }

    public function test_complete_profile_page_renders_bento_location_and_leaflet_map(): void
    {
        $user = User::create([
            'name' => 'Pak Bambang',
            'email' => 'bambang@bengkel.test',
            'password' => 'password123',
        ]);
        $business = Business::create(['name' => 'Bengkel Mobil Berkah']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $business->id]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('profile.complete'));

        $response->assertOk();
        $response->assertSee('Setup Profil &amp; Lokasi Usaha', false);
        $response->assertSee('leaflet.js', false);
        $response->assertSee('id="business-map"', false);
        $response->assertSee('Gunakan GPS Saya', false);
    }

    public function test_geo_search_areas_endpoint_returns_json_suggestions(): void
    {
        $user = User::create([
            'name' => 'Pak Joko',
            'email' => 'joko@warung.test',
            'password' => 'password123',
        ]);

        // Fake Biteship / OSM API
        Http::fake([
            'https://api.biteship.com/v1/maps/areas*' => Http::response([
                'success' => true,
                'areas' => [
                    [
                        'id' => 'area_12190',
                        'name' => 'Senayan, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190',
                        'country_name' => 'Indonesia',
                        'administrative_division_level_1_name' => 'DKI Jakarta',
                        'administrative_division_level_2_name' => 'Kota Jakarta Selatan',
                        'administrative_division_level_3_name' => 'Kebayoran Baru',
                        'administrative_division_level_4_name' => 'Senayan',
                        'postal_code' => 12190,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson(route('geo.search-areas', ['query' => '12190']));

        $response->assertOk();
        $response->assertJsonFragment([
            'postal_code' => '12190',
            'village' => 'Senayan',
            'district' => 'Kebayoran Baru',
            'city' => 'Kota Jakarta Selatan',
            'province' => 'DKI Jakarta',
        ]);
    }

    public function test_complete_profile_validation_requires_mandatory_location_fields(): void
    {
        $user = User::create([
            'name' => 'Ibu Ratna',
            'email' => 'ratna@butik.test',
            'password' => 'password123',
        ]);
        $business = Business::create(['name' => 'Butik Ratna Mode']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $business->id]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->post(route('profile.complete.save'), [
                'name' => 'Ibu Ratna Dewi',
                'phone' => '6281122334455',
                'business_name' => 'Butik Ratna Mode',
                // address and regional fields omitted
            ]);

        $response->assertSessionHasErrors([
            'address',
            'province',
            'city',
            'district',
            'village',
            'postal_code',
        ]);
    }

    public function test_complete_profile_successfully_creates_primary_location_and_syncs_store(): void
    {
        $user = User::create([
            'name' => 'Pak Hendra',
            'email' => 'hendra@distro.test',
            'password' => 'password123',
        ]);
        $business = Business::create(['name' => 'Distro Denim Bandung']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $business->id]);

        $storeSetting = CommerceStoreSetting::create([
            'business_id' => $business->id,
            'store_name' => 'Distro Denim Official',
            'slug' => 'distro-denim',
        ]);

        $payload = [
            'name' => 'Pak Hendra Gunawan',
            'phone' => '081234567890',
            'business_name' => 'Distro Denim Bandung',
            'address' => 'Jl. R.E. Martadinata No. 55, Citarum',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Bandung Wetan',
            'village' => 'Citarum',
            'postal_code' => '40115',
            'latitude' => -6.9082345,
            'longitude' => 107.6189123,
            'biteship_area_id' => 'IDNP6JB107618',
        ];

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->post(route('profile.complete.save'), $payload);

        $response->assertRedirect(route('dashboard'));

        // Assert user profile is updated
        $user->refresh();
        $this->assertSame('Pak Hendra Gunawan', $user->name);
        $this->assertSame('081234567890', $user->phone);

        // Assert primary location was created and correctly populated
        $location = Location::where('business_id', $business->id)->first();
        $this->assertNotNull($location);
        $this->assertSame('Jl. R.E. Martadinata No. 55, Citarum', $location->address);
        $this->assertSame('Jawa Barat', $location->province);
        $this->assertSame('Kota Bandung', $location->city);
        $this->assertSame('Bandung Wetan', $location->district);
        $this->assertSame('Citarum', $location->village);
        $this->assertSame('40115', $location->postal_code);
        $this->assertEqualsWithDelta(-6.9082345, (float) $location->latitude, 0.0001);
        $this->assertEqualsWithDelta(107.6189123, (float) $location->longitude, 0.0001);
        $this->assertSame('IDNP6JB107618', $location->biteship_area_id);
        $this->assertTrue((bool) $location->is_primary);

        // Assert store setting was synced
        $storeSetting->refresh();
        $this->assertSame('Jl. R.E. Martadinata No. 55, Citarum', $storeSetting->origin_address);
        $this->assertSame('40115', $storeSetting->origin_postal_code);
        $this->assertSame($location->id, $storeSetting->origin_location_id);
    }

    public function test_complete_profile_tenant_isolation_does_not_affect_other_businesses(): void
    {
        // Business A
        $userA = User::create(['name' => 'Owner A', 'email' => 'a@test.com', 'password' => 'secret']);
        $bizA = Business::create(['name' => 'Bisnis A']);
        $bizA->users()->attach($userA->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $userA->update(['active_business_id' => $bizA->id]);

        // Business B with existing location
        $userB = User::create(['name' => 'Owner B', 'email' => 'b@test.com', 'password' => 'secret']);
        $bizB = Business::create(['name' => 'Bisnis B']);
        $bizB->users()->attach($userB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $userB->update(['active_business_id' => $bizB->id]);

        $locB = Location::create([
            'business_id' => $bizB->id,
            'name' => 'Pusat Bisnis B',
            'code' => 'LOC-B-01',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'district' => 'Wonokromo',
            'village' => 'Darmo',
            'postal_code' => '60241',
            'address' => 'Jl. Raya Darmo No. 10',
        ]);

        // User A completes profile
        $payloadA = [
            'name' => 'Owner A Updated',
            'phone' => '081999888777',
            'business_name' => 'Bisnis A Baru',
            'address' => 'Jl. Malioboro No. 1',
            'province' => 'DI Yogyakarta',
            'city' => 'Kota Yogyakarta',
            'district' => 'Danurejan',
            'village' => 'Suryatmajan',
            'postal_code' => '55213',
            'latitude' => -7.7925,
            'longitude' => 110.3658,
        ];

        $this->actingAs($userA)
            ->withSession(['active_business_id' => $bizA->id])
            ->post(route('profile.complete.save'), $payloadA)
            ->assertRedirect(route('dashboard'));

        // Verify Business B location is completely untouched
        $locB->refresh();
        $this->assertSame('Kota Surabaya', $locB->city);
        $this->assertSame('60241', $locB->postal_code);
        $this->assertSame($bizB->id, $locB->business_id);

        // Verify Business A has its own distinct location
        $locA = Location::where('business_id', $bizA->id)->first();
        $this->assertNotNull($locA);
        $this->assertSame('Kota Yogyakarta', $locA->city);
        $this->assertSame('55213', $locA->postal_code);
    }
}

