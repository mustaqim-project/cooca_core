<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseLocationGeofenceBiteshipTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'owner@cooca.id',
        ]);

        $this->business = Business::create([
            'owner_id' => $this->user->id,
            'name'     => 'Cooca Main Enterprise',
        ]);

        $this->user->businesses()->attach($this->business->id, [
            'role'   => 'owner',
            'status' => 'active',
        ]);

        Context::setBusiness($this->business);
    }

    public function test_can_create_outlet_with_gps_geofence_and_biteship_area(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('warehouse.store'), [
                'name'                   => 'Cabang Senopati Jakarta',
                'type'                   => 'outlet',
                'code'                   => 'CBG-SNP',
                'phone'                  => '081234567890',
                'address'                => 'Jl. Senopati No. 45, Kebayoran Baru',
                'province'               => 'DKI Jakarta',
                'city'                   => 'Jakarta Selatan',
                'district'               => 'Kebayoran Baru',
                'village'                => 'Senayan',
                'postal_code'            => '12190',
                'biteship_area_id'       => 'IDNP11JB12190',
                'latitude'               => -6.2088,
                'longitude'              => 106.8456,
                'geofence_radius_meters' => 100,
                'is_online_fulfillment'  => 1,
                'allow_storefront_pickup'=> 1,
                'is_primary'             => 1,
            ]);

        $response->assertRedirect(route('warehouse.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'business_id'            => $this->business->id,
            'name'                   => 'Cabang Senopati Jakarta',
            'type'                   => 'outlet',
            'biteship_area_id'       => 'IDNP11JB12190',
            'postal_code'            => '12190',
            'latitude'               => -6.2088,
            'longitude'              => 106.8456,
            'geofence_radius_meters' => 100,
            'is_primary'             => true,
        ]);

        // Verify auto-sync to CommerceStoreSetting for online store pickup
        $storeSetting = CommerceStoreSetting::where('business_id', $this->business->id)->first();
        $this->assertNotNull($storeSetting);
        $this->assertEquals('IDNP11JB12190', $storeSetting->origin_area_id);
        $this->assertEquals(-6.2088, (float) $storeSetting->origin_latitude);
        $this->assertEquals(106.8456, (float) $storeSetting->origin_longitude);
    }

    public function test_can_update_warehouse_geofence_and_biteship(): void
    {
        $location = Location::create([
            'business_id'            => $this->business->id,
            'name'                   => 'Gudang Pusat',
            'slug'                   => 'gudang-pusat',
            'type'                   => 'warehouse',
            'is_active'              => true,
            'geofence_radius_meters' => 50,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('warehouse.update', $location), [
                'name'                   => 'Gudang Logistik Cikarang',
                'type'                   => 'warehouse',
                'code'                   => 'GDG-CKR',
                'phone'                  => '082199887766',
                'address'                => 'Kawasan Industri Jababeka Blok C',
                'province'               => 'Jawa Barat',
                'city'                   => 'Bekasi',
                'district'               => 'Cikarang Selatan',
                'postal_code'            => '17530',
                'biteship_area_id'       => 'IDNP12BK17530',
                'latitude'               => -6.3200,
                'longitude'              => 107.1500,
                'geofence_radius_meters' => 200,
                'is_primary'             => 1,
            ]);

        $response->assertRedirect(route('warehouse.index'));

        $this->assertDatabaseHas('locations', [
            'id'                     => $location->id,
            'name'                   => 'Gudang Logistik Cikarang',
            'biteship_area_id'       => 'IDNP12BK17530',
            'latitude'               => -6.3200,
            'longitude'              => 107.1500,
            'geofence_radius_meters' => 200,
            'is_primary'             => true,
        ]);
    }
}
