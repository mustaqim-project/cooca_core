<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
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

final class SettingsModulesAutoSaveTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner.modules@test.local',
            'phone' => '6281298765432',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Kopi Maju',
            'currency' => 'IDR',
            'template_code' => 'coffee_shop',
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_DINEIN,
            ],
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($this->business, $membership);
    }

    public function test_settings_modules_tab_renders_successfully(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertOk();
        $response->assertSee('toggleModule');
        $response->assertSee('modulesState');
        $response->assertSee('moduleSaveStatus');
        $response->assertSee(__('settings.module_autosave_saved'));
    }

    public function test_owner_can_auto_save_enable_single_module_via_ajax(): void
    {
        $this->assertFalse($this->business->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));

        $allKeys = array_keys(ModuleRegistry::definitions());

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->putJson(route('settings.modules.update'), [
                'module_key' => ModuleRegistry::MODULE_POS_DINEIN,
                'enabled' => true,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'module_key' => ModuleRegistry::MODULE_POS_DINEIN,
            'is_enabled' => true,
            'total_count' => count($allKeys),
        ]);

        $this->business->refresh();
        $this->assertTrue($this->business->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));
        $this->assertNotContains(ModuleRegistry::MODULE_POS_DINEIN, $this->business->disabled_modules ?? []);

        // Assert AuditLog was created
        $log = AuditLog::where('business_id', $this->business->id)
            ->where('action', 'settings.modules_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString(ModuleRegistry::MODULE_POS_DINEIN, $log->notes);
    }

    public function test_owner_can_auto_save_disable_single_module_via_ajax(): void
    {
        // First ensure pos_retail is enabled
        $this->assertTrue($this->business->isModuleEnabled(ModuleRegistry::MODULE_POS_RETAIL));

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->putJson(route('settings.modules.update'), [
                'module_key' => ModuleRegistry::MODULE_POS_RETAIL,
                'enabled' => false,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'module_key' => ModuleRegistry::MODULE_POS_RETAIL,
            'is_enabled' => false,
        ]);

        $this->business->refresh();
        $this->assertFalse($this->business->isModuleEnabled(ModuleRegistry::MODULE_POS_RETAIL));
        $this->assertContains(ModuleRegistry::MODULE_POS_RETAIL, $this->business->disabled_modules ?? []);
    }

    public function test_owner_can_auto_save_bulk_modules_via_ajax(): void
    {
        $allKeys = array_keys(ModuleRegistry::definitions());

        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->putJson(route('settings.modules.update'), [
                'enabled_modules' => $allKeys,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'enabled_count' => count($allKeys),
            'total_count' => count($allKeys),
        ]);

        $this->business->refresh();
        $this->assertEmpty($this->business->disabled_modules);
    }

    public function test_non_owner_cannot_auto_save_module(): void
    {
        $staff = User::create([
            'name' => 'Staf Kasir',
            'email' => 'staff.modules@test.local',
            'phone' => '6281298765433',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $staffRole = Role::where('slug', 'staff')->first();
        $this->business->users()->attach($staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole?->id,
        ]);

        $staff->update(['active_business_id' => $this->business->id]);

        $response = $this->actingAs($staff, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $staff->id,
            ])
            ->putJson(route('settings.modules.update'), [
                'module_key' => ModuleRegistry::MODULE_POS_DINEIN,
                'enabled' => true,
            ]);

        $response->assertForbidden();
    }

    public function test_invalid_module_key_is_rejected(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->owner->id,
            ])
            ->putJson(route('settings.modules.update'), [
                'module_key' => 'invalid_non_existent_module',
                'enabled' => true,
            ]);

        $response->assertUnprocessable();
    }
}
