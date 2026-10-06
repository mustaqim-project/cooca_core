<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BranchSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $primaryBranch;

    protected function setUp(): void
    {
        parent::setUp();

        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner.branch@test.local',
            'phone' => '6281298765432',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Nusantara Abadi',
            'currency' => 'IDR',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
            'is_active' => true,
        ]);

        $this->primaryBranch = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Kantor Pusat & Outlet Sudirman',
            'slug' => 'kantor-pusat-sudirman',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_settings_branches_tab(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->get(route('settings.index', ['tab' => 'branches']));

        $response->assertOk();
        $response->assertSee('Manajemen Cabang &amp; Toko', false);
        $response->assertSee('Kantor Pusat &amp; Outlet Sudirman', false);
        $response->assertSee('Arsitektur 1 Cabang Banyak Gudang');
        $response->assertSee('+ Tambah Cabang Baru');
    }

    public function test_owner_can_create_new_branch_from_settings(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->post(route('settings.branches.store'), [
                'name' => 'Cabang Senopati',
                'code' => 'SNP-01',
                'phone' => '081234567890',
                'address' => 'Jl. Senopati No. 45',
                'city' => 'Jakarta Selatan',
                'latitude' => -6.2297,
                'longitude' => 106.8074,
                'geofence_radius_meters' => 150,
                'is_online_fulfillment' => 1,
                'allow_storefront_pickup' => 1,
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'business_id' => $this->business->id,
            'name' => 'Cabang Senopati',
            'type' => 'outlet',
            'code' => 'SNP-01',
            'is_primary' => false,
            'geofence_radius_meters' => 150,
        ]);
    }

    public function test_owner_can_update_branch_from_settings(): void
    {
        $branch = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Tebet Lama',
            'slug' => 'cabang-tebet-lama',
            'type' => 'outlet',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->put(route('settings.branches.update', $branch), [
                'name' => 'Cabang Tebet Baru',
                'code' => 'TBT-02',
                'phone' => '081987654321',
                'address' => 'Jl. Tebet Raya No. 10',
                'city' => 'Jakarta Selatan',
                'geofence_radius_meters' => 200,
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'id' => $branch->id,
            'name' => 'Cabang Tebet Baru',
            'code' => 'TBT-02',
            'geofence_radius_meters' => 200,
        ]);
    }

    public function test_owner_cannot_delete_primary_branch(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->delete(route('settings.branches.destroy', $this->primaryBranch));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('locations', [
            'id' => $this->primaryBranch->id,
        ]);
    }

    public function test_owner_cannot_delete_branch_with_active_child_warehouses(): void
    {
        $branch = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Bandung',
            'slug' => 'cabang-bandung',
            'type' => 'outlet',
            'is_primary' => false,
            'is_active' => true,
        ]);

        // Child warehouse under branch
        Location::create([
            'business_id' => $this->business->id,
            'parent_id' => $branch->id,
            'name' => 'Gudang Stok Bandung Belakang',
            'slug' => 'gudang-stok-bandung-belakang',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->delete(route('settings.branches.destroy', $branch));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('locations', [
            'id' => $branch->id,
        ]);
    }

    public function test_owner_can_delete_leaf_branch_without_dependencies(): void
    {
        $branch = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Cirebon Uji',
            'slug' => 'cabang-cirebon-uji',
            'type' => 'outlet',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->delete(route('settings.branches.destroy', $branch));

        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('locations', [
            'id' => $branch->id,
        ]);
    }

    public function test_warehouse_index_redirects_add_outlet_param_to_settings(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
            ])
            ->get(route('warehouse.index', ['add' => 'outlet']));

        $response->assertRedirect(route('settings.index', ['tab' => 'branches', 'add' => 1]));
    }
}
