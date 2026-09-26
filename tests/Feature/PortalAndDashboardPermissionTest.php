<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalAndDashboardPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_executive_dashboard(): void
    {
        $this->seed(RbacSeeder::class);

        $owner = User::create([
            'name' => 'Pak Budi Owner',
            'email' => 'owner@cooca-test.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'name' => 'Resto Berkah Nusantara',
            'status' => 'active',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $owner->update(['active_business_id' => $business->id]);
        Context::flush();

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_user_without_dashboard_view_permission_is_redirected_to_portal(): void
    {
        $this->seed(RbacSeeder::class);

        $staff = User::create([
            'name' => 'Siti Kasir',
            'email' => 'siti@cooca-test.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'name' => 'Resto Berkah Nusantara',
            'status' => 'active',
        ]);

        // Create a restricted role without dashboard.view
        $staffRole = Role::create([
            'business_id' => $business->id,
            'name' => 'Kasir Khusus',
            'slug' => 'custom-cashier',
        ]);
        $posPerm = Permission::where('slug', 'pos.terminal')->firstOrFail();
        $staffRole->permissions()->sync([$posPerm->id]);

        $business->users()->attach($staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
        ]);

        $staff->update(['active_business_id' => $business->id]);
        Context::flush();

        // 1. Accessing /dashboard must redirect to /portal
        $dashResponse = $this->actingAs($staff)->get(route('dashboard'));
        $dashResponse->assertRedirect(route('portal'));

        // 2. Accessing restricted menu (/materials) must redirect to /portal with warning flash
        $materialsResponse = $this->actingAs($staff)->get(route('materials.index'));
        $materialsResponse->assertRedirect(route('portal'));
        $materialsResponse->assertSessionHas('error');
    }

    public function test_staff_portal_renders_wib_clock_weather_user_and_seven_day_attendance_history(): void
    {
        $this->seed(RbacSeeder::class);

        $staff = User::create([
            'name' => 'Ahmad Barista',
            'email' => 'ahmad@cooca-test.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'name' => 'Kopi Kenangan Senja',
            'city' => 'Bandung',
            'status' => 'active',
        ]);

        $staffRole = Role::where('slug', 'staff')->firstOrFail();

        $business->users()->attach($staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
        ]);

        $staff->update(['active_business_id' => $business->id]);
        Context::flush();

        // Seed 7 days of attendance
        for ($i = 0; $i < 5; $i++) {
            $attDate = Carbon::now('Asia/Jakarta')->subDays($i)->toDateString();
            Attendance::create([
                'id' => (string) Str::uuid(),
                'business_id' => $business->id,
                'user_id' => $staff->id,
                'date' => $attDate,
                'clock_in_at' => Carbon::parse($attDate . ' 08:30:00'),
                'clock_out_at' => Carbon::parse($attDate . ' 17:00:00'),
                'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
                'work_duration_minutes' => 510,
                'status' => Attendance::STATUS_PRESENT,
            ]);
        }

        $response = $this->actingAs($staff)->get(route('portal'));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Barista');
        $response->assertSee('WIB');
        $response->assertSee('Histori Presensi 7 Hari Terakhir');
        $response->assertSee('Absen Masuk (Clock-In)');
        $response->assertSee('Absen Pulang (Clock-Out)');
        $response->assertSee('Kopi Kenangan Senja');
    }

    public function test_login_redirects_non_dashboard_user_to_portal(): void
    {
        $this->seed(RbacSeeder::class);

        $password = 'secret12345';
        $user = User::create([
            'name' => 'Budi Kasir',
            'email' => 'budi-login@test.local',
            'password' => bcrypt($password),
            'email_verified_at' => now(),
            'phone' => '6281234567890',
            'phone_verified_at' => now(),
        ]);

        $business = Business::create(['name' => 'Bisnis Kasir', 'status' => 'active']);
        $role = Role::create([
            'business_id' => $business->id,
            'name' => 'Kasir Non Dashboard',
            'slug' => 'kasir-non-dash',
        ]);
        $posPerm = Permission::where('slug', 'pos.terminal')->firstOrFail();
        $role->permissions()->sync([$posPerm->id]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $role->id,
        ]);
        $user->update(['active_business_id' => $business->id]);
        Context::flush();

        $response = $this->post('/login', [
            'email' => 'budi-login@test.local',
            'password' => $password,
        ]);

        $response->assertRedirect(route('portal'));
    }

    public function test_context_home_route_helper_and_breadcrumb_adaptation(): void
    {
        $this->seed(RbacSeeder::class);

        $owner = User::create([
            'name' => 'Pak Bos',
            'email' => 'bos@test.local',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $staff = User::create([
            'name' => 'Staf Biasa',
            'email' => 'staf@test.local',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $business = Business::create(['name' => 'Toko Maju', 'status' => 'active']);
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $staffRole = Role::create([
            'business_id' => $business->id,
            'name' => 'Staf Saja',
            'slug' => 'staf-saja',
        ]);

        $business->users()->attach($owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $business->users()->attach($staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
        ]);

        // 1. Check Owner Context Home Route
        $owner->update(['active_business_id' => $business->id]);
        $this->actingAs($owner);
        Context::setBusiness($business, BusinessMembership::where('business_id', $business->id)->where('user_id', $owner->id)->first());
        $this->assertEquals(route('dashboard'), Context::homeRoute());
        $this->assertEquals('Dashboard', Context::homeLabel());

        // 2. Check Non-Dashboard Staff Context Home Route
        $staff->update(['active_business_id' => $business->id]);
        $this->actingAs($staff);
        Context::setBusiness($business, BusinessMembership::where('business_id', $business->id)->where('user_id', $staff->id)->first());
        $this->assertEquals(route('portal'), Context::homeRoute());
        $this->assertEquals('Portal & Presensi', Context::homeLabel());

        // 3. Render Blade Component <x-breadcrumb> for Non-Dashboard User
        $rendered = (string) $this->blade('<x-breadcrumb :items="$items" />', [
            'items' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Pengaturan', 'url' => null],
            ],
        ]);

        $this->assertStringContainsString(route('portal'), $rendered);
        $this->assertStringContainsString('Portal', $rendered);
    }
}
