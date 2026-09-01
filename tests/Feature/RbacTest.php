<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);
    }

    public function test_rbac_seeder_populates_default_roles_and_permissions(): void
    {
        $this->assertDatabaseHas('roles', ['slug' => 'owner']);
        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
        $this->assertDatabaseHas('roles', ['slug' => 'cashier']);
        $this->assertDatabaseHas('roles', ['slug' => 'warehouse']);
        $this->assertDatabaseHas('roles', ['slug' => 'finance']);
        $this->assertDatabaseHas('roles', ['slug' => 'staff']);
        $this->assertDatabaseHas('roles', ['slug' => 'viewer']);

        $this->assertDatabaseHas('permissions', ['slug' => 'users.manage']);
        $this->assertDatabaseHas('permissions', ['slug' => 'costing.view_margin']);
        $this->assertDatabaseHas('permissions', ['slug' => 'costing.manage']);
        $this->assertDatabaseHas('permissions', ['slug' => 'pos.terminal']);
        $this->assertDatabaseHas('permissions', ['slug' => 'billing.manage']);
    }

    public function test_owner_can_add_member_and_update_role(): void
    {
        $owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner_rbac@example.com',
            'password' => 'password123',
        ]);

        $calonMember = User::create([
            'name' => 'Calon Karyawan',
            'email' => 'karyawan@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Usaha Bersama']);
        $business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $owner->update(['active_business_id' => $business->id]);

        // 1. Free plan: Attempting to add 2nd user should be blocked by Entitlement
        $freeResponse = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/v1/businesses/{$business->slug}/members", [
                'email' => 'karyawan@example.com',
                'role' => 'staff',
            ]);

        $freeResponse->assertForbidden()
            ->assertJsonPath('error_code', 'RESOURCE_LIMIT_EXCEEDED');

        // 2. Upgrade business to Core plan
        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($business);

        // 3. Add Member now succeeds
        $response = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/v1/businesses/{$business->slug}/members", [
                'email' => 'karyawan@example.com',
                'role' => 'staff',
            ]);

        $response->assertCreated()
            ->assertJsonPath('member.email', 'karyawan@example.com')
            ->assertJsonPath('member.role', 'staff');

        $this->assertDatabaseHas('business_users', [
            'business_id' => $business->id,
            'user_id' => $calonMember->id,
            'role' => 'staff',
        ]);

        // Update Role
        $updateResponse = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->patchJson("/api/v1/businesses/{$business->slug}/members/{$calonMember->id}", [
                'role' => 'admin',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('role', 'admin');

        $this->assertDatabaseHas('business_users', [
            'business_id' => $business->id,
            'user_id' => $calonMember->id,
            'role' => 'admin',
        ]);
    }

    public function test_staff_cannot_add_or_modify_members(): void
    {
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff_rbac@example.com',
            'password' => 'password123',
        ]);

        $anotherUser = User::create([
            'name' => 'User Lain',
            'email' => 'another@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Bengkel Sejahtera']);
        $business->users()->attach($staff->id, ['id' => (string) Str::uuid(), 'role' => 'staff']);
        $staff->update(['active_business_id' => $business->id]);

        $response = $this->actingAs($staff, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->postJson("/api/v1/businesses/{$business->slug}/members", [
                'email' => 'another@example.com',
                'role' => 'viewer',
            ]);

        $response->assertForbidden()
            ->assertJsonPath('error_code', 'FORBIDDEN_ROLE');
    }
}
