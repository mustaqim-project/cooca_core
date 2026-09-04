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

class OwnerRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->owner = User::create(['name' => 'Owner', 'email' => 'owner-role@test.local', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Role Business']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'role_id' => Role::where('slug', 'owner')->value('id')]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->owner->id)->first());
    }

    public function test_owner_can_create_custom_role_with_selected_permissions(): void
    {
        $response = $this->actingAs($this->owner)->post(route('settings.roles.store'), [
            'name' => 'Kasir Terbatas',
            'description' => 'Kasir tanpa akses margin.',
            'permissions' => ['pos.terminal', 'invoices.create'],
        ]);

        $response->assertSessionHas('success');
        $role = Role::where('business_id', $this->business->id)->where('slug', 'kasir-terbatas')->firstOrFail();
        $this->assertTrue($role->permissions()->where('slug', 'pos.terminal')->exists());
        $this->assertTrue($role->permissions()->where('slug', 'invoices.create')->exists());
    }

    public function test_custom_role_is_tenant_scoped_and_employee_uses_its_permissions(): void
    {
        $role = Role::create(['business_id' => $this->business->id, 'name' => 'POS Only', 'slug' => 'pos-only']);
        $role->permissions()->sync(Permission::whereIn('slug', ['pos.terminal'])->pluck('id'));
        $employee = User::create(['name' => 'Cashier', 'email' => 'cashier-role@test.local', 'password' => 'password']);
        $this->business->users()->attach($employee->id, ['id' => (string) Str::uuid(), 'role' => 'staff', 'role_id' => $role->id]);

        Context::setBusiness($this->business, BusinessMembership::where('business_id', $this->business->id)->where('user_id', $employee->id)->first());
        $this->assertSame('pos-only', Context::role());
        $this->assertTrue(Context::hasPermission('pos.terminal'));
        $this->assertFalse(Context::hasPermission('costing.view_margin'));
    }
}
