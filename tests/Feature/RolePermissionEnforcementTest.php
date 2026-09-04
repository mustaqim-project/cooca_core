<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RolePermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_is_denied_from_hpp_and_member_management(): void
    {
        $this->seed(RbacSeeder::class);
        $owner = User::create(['name' => 'Owner', 'email' => 'owner-enforcement@test.local', 'password' => 'password']);
        $employee = User::create(['name' => 'Cashier', 'email' => 'cashier-enforcement@test.local', 'password' => 'password']);
        $business = Business::create(['name' => 'Enforcement Business']);
        $role = Role::where('slug', 'cashier')->firstOrFail();
        $business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'role_id' => Role::where('slug', 'owner')->value('id')]);
        $business->users()->attach($employee->id, ['id' => (string) Str::uuid(), 'role' => 'cashier', 'role_id' => $role->id]);
        $employee->update(['active_business_id' => $business->id]);
        Context::flush();

        $this->actingAs($employee)->get('/calculator')->assertRedirect(route('dashboard'));
        $this->actingAs($employee)->post('/settings/members', [
            'email' => 'new@test.local', 'role_id' => $role->id,
        ])->assertRedirect(route('dashboard'));
    }

    public function test_custom_role_can_be_assigned_only_from_active_business(): void
    {
        $this->seed(RbacSeeder::class);
        $owner = User::create(['name' => 'Owner', 'email' => 'owner-scope@test.local', 'password' => 'password']);
        $business = Business::create(['name' => 'Scope Business']);
        $business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'role_id' => Role::where('slug', 'owner')->value('id')]);
        Context::setBusiness($business, BusinessMembership::where('business_id', $business->id)->where('user_id', $owner->id)->first());
        $otherBusiness = Business::create(['name' => 'Other Business']);
        $otherRole = Role::create(['business_id' => $otherBusiness->id, 'name' => 'Other Role', 'slug' => 'other-role']);
        $otherRole->permissions()->sync(Permission::where('slug', 'pos.terminal')->pluck('id'));

        $this->actingAs($owner)->post(route('settings.roles.store'), [
            'name' => 'Imported Role', 'permissions' => ['pos.terminal'],
        ])->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['business_id' => $business->id, 'slug' => 'other-role']);
    }
}
