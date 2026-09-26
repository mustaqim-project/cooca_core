<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NavigationRegistryTest extends TestCase
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

        $this->owner = User::create([
            'name'              => 'Owner Nav Test',
            'email'             => 'owner_nav_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Nav Registry Audit',
            'currency'         => 'IDR',
            'disabled_modules' => [
                ModuleRegistry::MODULE_CRM_LOYALTY,
            ],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_all_modules_are_defined_with_valid_structure(): void
    {
        $all = NavigationRegistry::all();

        $expectedKeys = [
            'finance',
            'accounting',
            'purchasing',
            'products',
            'materials',
            'inventory',
            'sales',
            'crm',
            'communication',
            'storefront',
            'approvals',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $all);
            $module = $all[$key];
            $this->assertArrayHasKey('label', $module);
            $this->assertArrayHasKey('icon', $module);
            $this->assertArrayHasKey('parent_breadcrumb', $module);
            $this->assertArrayHasKey('tabs', $module);
            $this->assertNotEmpty($module['tabs']);

            foreach ($module['tabs'] as $tab) {
                $this->assertArrayHasKey('key', $tab);
                $this->assertArrayHasKey('label', $tab);
                $this->assertArrayHasKey('route', $tab);
                $this->assertArrayHasKey('active_routes', $tab);
            }
        }
    }

    public function test_get_module_returns_expected_definition(): void
    {
        $materials = NavigationRegistry::getModule('materials');
        $this->assertNotNull($materials);
        $this->assertSame('Bahan Baku & Resep', $materials['label']);
        $this->assertCount(3, $materials['tabs']);
    }

    public function test_crm_loyalty_tabs_are_filtered_when_module_is_disabled(): void
    {
        $this->actingAs($this->owner, 'web');

        // crm_loyalty is disabled for $this->business
        $tabs = NavigationRegistry::getTabsForModule('crm');

        // Only the base 'customers' tab should be returned, 'members' and 'vouchers' should be excluded
        $tabKeys = array_column($tabs, 'key');
        $this->assertContains('customers', $tabKeys);
        $this->assertNotContains('members', $tabKeys);
        $this->assertNotContains('vouchers', $tabKeys);
    }

    public function test_tabs_become_available_when_module_is_enabled(): void
    {
        $this->actingAs($this->owner, 'web');

        // Enable crm_loyalty
        $this->business->enableModule(ModuleRegistry::MODULE_CRM_LOYALTY);
        $this->business->refresh();
        Context::setBusiness($this->business);

        $tabs = NavigationRegistry::getTabsForModule('crm');
        $tabKeys = array_column($tabs, 'key');

        $this->assertContains('customers', $tabKeys);
        $this->assertContains('members', $tabKeys);
        $this->assertContains('vouchers', $tabKeys);
    }

    public function test_can_access_tab_logic(): void
    {
        $this->actingAs($this->owner, 'web');

        $disabledTab = [
            'key'        => 'members',
            'module'     => ModuleRegistry::MODULE_CRM_LOYALTY,
            'permission' => 'crm.view',
        ];

        // Currently disabled
        $this->assertFalse(NavigationRegistry::canAccessTab($disabledTab));

        // Enable module
        $this->business->enableModule(ModuleRegistry::MODULE_CRM_LOYALTY);
        $this->business->refresh();
        Context::setBusiness($this->business);

        $this->assertTrue(NavigationRegistry::canAccessTab($disabledTab));
    }
}
