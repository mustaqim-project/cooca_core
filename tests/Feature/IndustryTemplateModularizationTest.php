<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessTypeTemplate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class IndustryTemplateModularizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
    }

    private function createUser(array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user_' . Str::random(8) . '@test.local',
            'phone' => '628' . rand(100000000, 999999999),
            'password' => 'password',
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_module_registry_definitions_and_presets_coverage(): void
    {
        $definitions = ModuleRegistry::definitions();
        $this->assertCount(9, $definitions);

        $this->assertArrayHasKey(ModuleRegistry::MODULE_POS_DINEIN, $definitions);
        $this->assertArrayHasKey(ModuleRegistry::MODULE_RECIPE_BOM, $definitions);
        $this->assertArrayHasKey(ModuleRegistry::MODULE_B2B_SALES, $definitions);

        // Check permission lookup
        $this->assertEquals(ModuleRegistry::MODULE_POS_DINEIN, ModuleRegistry::getModuleForPermission('pos.kitchen'));
        $this->assertEquals(ModuleRegistry::MODULE_POS_DINEIN, ModuleRegistry::getModuleForPermission('pos.tables'));
        $this->assertEquals(ModuleRegistry::MODULE_RECIPE_BOM, ModuleRegistry::getModuleForPermission('materials.view'));
        $this->assertNull(ModuleRegistry::getModuleForPermission('business.view')); // Core permission

        // Check template disabled modules mapping for 25 presets
        $allTemplates = BusinessTypeTemplate::all();
        $this->assertGreaterThanOrEqual(20, $allTemplates->count());

        foreach ($allTemplates as $tmpl) {
            $disabled = ModuleRegistry::getDisabledModulesForTemplate($tmpl->code);
            $this->assertIsArray($disabled);

            $summary = ModuleRegistry::getFeaturesSummaryForTemplate($tmpl->code);
            $this->assertArrayHasKey('enabled', $summary);
            $this->assertArrayHasKey('disabled', $summary);
        }
    }

    public function test_business_creation_with_service_template_disables_irrelevant_modules(): void
    {
        $user = $this->createUser([
            'name' => 'Agency Owner',
            'email' => 'agency@test.local',
            'phone' => '6281234567890',
        ]);

        $template = BusinessTypeTemplate::where('code', 'service_agency')->firstOrFail();
        $disabledModules = ModuleRegistry::getDisabledModulesForTemplate($template->code);

        $business = Business::create([
            'name' => 'Agency Digital Kreatif',
            'currency' => 'IDR',
            'currency_precision' => 0,
            'rounding_strategy' => Business::ROUNDING_ROUND_100,
            'industry_category' => $template->industry_category,
            'template_code' => $template->code,
            'disabled_modules' => $disabledModules,
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->assertEquals('service_agency', $business->template_code);
        $this->assertTrue($business->isModuleDisabled(ModuleRegistry::MODULE_POS_DINEIN));
        $this->assertTrue($business->isModuleDisabled(ModuleRegistry::MODULE_RECIPE_BOM));
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_B2B_SALES));

        // Check permission check helper
        $this->assertFalse($business->isPermissionEnabled('pos.kitchen'));
        $this->assertFalse($business->isPermissionEnabled('materials.view'));
        $this->assertTrue($business->isPermissionEnabled('invoices.view'));
        $this->assertTrue($business->isPermissionEnabled('business.view')); // core
    }

    public function test_business_owner_does_not_have_permissions_for_disabled_modules(): void
    {
        $user = $this->createUser([
            'name' => 'Owner Test',
            'email' => 'owner@test.local',
            'phone' => '6281234567890',
        ]);

        $business = Business::create([
            'name' => 'Konsultan Bisnis Sejahtera',
            'currency' => 'IDR',
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_DINEIN,
                ModuleRegistry::MODULE_RECIPE_BOM,
            ],
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);

        $this->actingAs($user, 'web');
        $membership = BusinessMembership::where('business_id', $business->id)->where('user_id', $user->id)->first();
        Context::setBusiness($business, $membership);

        $this->assertTrue(Context::isOwner());

        // CRITICAL CHECK: Owner MUST return FALSE for disabled module permissions!
        $this->assertFalse(Context::hasPermission('pos.kitchen'));
        $this->assertFalse(Context::hasPermission('pos.tables'));
        $this->assertFalse(Context::hasPermission('materials.view'));

        // But owner MUST retain true for enabled and core permissions
        $this->assertTrue(Context::hasPermission('business.view'));
        $this->assertTrue(Context::hasPermission('settings.view'));
        $this->assertTrue(Context::hasPermission('settings.edit'));
        $this->assertTrue(Context::hasPermission('invoices.view'));

        // Context::permissions() should exclude disabled permissions
        $activePermissions = Context::permissions();
        $this->assertNotContains('pos.kitchen', $activePermissions);
        $this->assertNotContains('pos.tables', $activePermissions);
        $this->assertNotContains('materials.view', $activePermissions);
        $this->assertContains('invoices.view', $activePermissions);
    }

    public function test_owner_can_toggle_modules_via_settings_endpoint(): void
    {
        $user = $this->createUser([
            'name' => 'Owner Retail',
            'email' => 'owner-retail@test.local',
            'phone' => '6281234567890',
        ]);

        $business = Business::create([
            'name' => 'Toko Retail & Fashion',
            'currency' => 'IDR',
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_DINEIN,
            ],
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);

        $membership = BusinessMembership::where('business_id', $business->id)->where('user_id', $user->id)->first();
        Context::setBusiness($business, $membership);

        // Before: pos_dinein is disabled
        $this->assertFalse(Context::hasPermission('pos.kitchen'));

        // Owner enables all modules including pos_dinein
        $allKeys = array_keys(ModuleRegistry::definitions());

        $response = $this->actingAs($user, 'web')
            ->from(route('settings.index', ['tab' => 'modules']))
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->put(route('settings.modules.update'), [
                'enabled_modules' => $allKeys,
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'modules']));
        $response->assertSessionHas('success');
        $response->assertSessionHas('active_tab', 'modules');

        $business->refresh();
        $this->assertEmpty($business->disabled_modules);
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));

        // After reload: Context hasPermission now returns true
        Context::flush();
        $membership = BusinessMembership::where('business_id', $business->id)->where('user_id', $user->id)->first();
        Context::setBusiness($business, $membership);
        $this->assertTrue(Context::hasPermission('pos.kitchen'));
    }

    public function test_non_owner_cannot_update_modules(): void
    {
        $owner = $this->createUser([
            'name' => 'Owner Bisnis',
            'email' => 'owner-staff@test.local',
            'phone' => '6281234567890',
        ]);
        $staff = $this->createUser([
            'name' => 'Staff Biasa',
            'email' => 'staff@test.local',
            'phone' => '6281234567891',
        ]);

        $business = Business::create([
            'name' => 'Usaha Bersama',
            'currency' => 'IDR',
        ]);

        $roleStaff = Role::where('slug', 'staff')->firstOrFail();
        $business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $business->users()->attach($staff->id, ['id' => (string) Str::uuid(), 'role' => 'staff', 'role_id' => $roleStaff->id]);

        $staff->update(['active_business_id' => $business->id]);

        $response = $this->actingAs($staff, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $staff->id,
            ])
            ->put(route('settings.modules.update'), [
                'enabled_modules' => [],
            ]);

        $response->assertForbidden();
    }

    public function test_roles_management_filters_out_disabled_permissions(): void
    {
        $user = $this->createUser([
            'name' => 'Owner Studio',
            'email' => 'studio@test.local',
            'phone' => '6281234567890',
        ]);

        $business = Business::create([
            'name' => 'Studio Foto & Desain',
            'currency' => 'IDR',
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_DINEIN,
                ModuleRegistry::MODULE_RECIPE_BOM,
            ],
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);

        // 1. Visit /roles
        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->get(route('roles.index'));

        $response->assertOk();
        $response->assertViewHas('permissions');
        $response->assertViewHas('hiddenPermissionsCount');

        $hiddenCount = $response->viewData('hiddenPermissionsCount');
        $this->assertGreaterThan(0, $hiddenCount);

        $categories = $response->viewData('permissions');
        foreach ($categories as $category => $perms) {
            foreach ($perms as $p) {
                $this->assertNotEquals('pos.kitchen', $p->slug);
                $this->assertNotEquals('pos.tables', $p->slug);
                $this->assertNotEquals('materials.view', $p->slug);
            }
        }

        // 2. Attempt to create a role with disabled permission
        $createResponse = $this->actingAs($user, 'web')
            ->from(route('roles.index'))
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->post(route('roles.store'), [
                'name' => 'Operator Studio',
                'description' => 'Operator tanpa izin dapur',
                'permissions' => [
                    'pos.kitchen', // Disabled module
                    'invoices.view', // Enabled module
                ],
            ]);

        $createResponse->assertRedirect(route('roles.index'));
        $role = Role::where('business_id', $business->id)->where('slug', 'operator-studio')->firstOrFail();
        $assignedSlugs = $role->permissions()->pluck('slug')->toArray();

        // pos.kitchen should NOT be assigned because pos_dinein is disabled
        $this->assertNotContains('pos.kitchen', $assignedSlugs);
        $this->assertContains('invoices.view', $assignedSlugs);
    }

    public function test_backward_compatibility_for_businesses_without_disabled_modules(): void
    {
        $user = $this->createUser([
            'name' => 'Owner Legacy',
            'email' => 'legacy@test.local',
            'phone' => '6281234567890',
        ]);

        // Legacy business with null disabled_modules
        $business = Business::create([
            'name' => 'Toko Lama Sejak 2020',
            'currency' => 'IDR',
            'disabled_modules' => null,
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        $this->actingAs($user, 'web');
        $membership = BusinessMembership::where('business_id', $business->id)->where('user_id', $user->id)->first();
        Context::setBusiness($business, $membership);

        // All modules remain enabled
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_RECIPE_BOM));
        $this->assertTrue($business->isPermissionEnabled('pos.kitchen'));

        // Owner retains full access
        $this->assertTrue(Context::hasPermission('pos.kitchen'));
        $this->assertTrue(Context::hasPermission('materials.view'));
    }
}
