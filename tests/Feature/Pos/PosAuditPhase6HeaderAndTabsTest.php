<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Test suite for verifying Phase 6 Header & Tabs unification across POS submodules.
 *
 * @covers \App\Support\Navigation\NavigationRegistry
 */
class PosAuditPhase6HeaderAndTabsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test NavigationRegistry contains all POS tabs including printers and correct routes.
     */
    public function test_navigation_registry_contains_complete_pos_tabs(): void
    {
        $module = NavigationRegistry::getModule('pos');

        $this->assertNotNull($module, 'POS module must be registered in NavigationRegistry.');
        $this->assertArrayHasKey('tabs', $module, 'POS module must have tabs array.');

        $tabKeys = array_column($module['tabs'], 'key');
        $expectedTabs = ['orders', 'shifts', 'reports', 'tables', 'kitchen', 'modifiers', 'printers'];

        foreach ($expectedTabs as $expectedTab) {
            $this->assertContains($expectedTab, $tabKeys, "POS module tabs must contain '{$expectedTab}' tab.");
        }

        // Verify printers tab structure
        $printersTab = collect($module['tabs'])->firstWhere('key', 'printers');
        $this->assertNotNull($printersTab, 'Printers tab must exist in POS module.');
        $this->assertEquals('pos.printers.index', $printersTab['route']);
        $this->assertContains('pos.printers.*', $printersTab['active_routes']);
        $this->assertContains('settings.pos.printers.*', $printersTab['active_routes']);
        $this->assertEquals('pos.terminal', $printersTab['permission']);
    }

    /**
     * Test getTabsForModule returns authorized tabs when user has permissions.
     */
    public function test_get_tabs_for_pos_module_resolves_tabs(): void
    {
        $user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $business = Business::create([
            'name' => 'Cooca Coffee & Resto',
            'email' => 'business@example.com',
            'phone' => '081234567890',
            'enabled_modules' => ['pos_retail', 'pos_dinein'],
        ]);

        BusinessMembership::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $this->actingAs($user);
        Context::setBusiness($business);

        $tabs = NavigationRegistry::getTabsForModule('pos');

        $this->assertIsArray($tabs);
        $this->assertNotEmpty($tabs);

        $keys = array_column($tabs, 'key');
        $this->assertContains('orders', $keys);
        $this->assertContains('shifts', $keys);
        $this->assertContains('reports', $keys);
        $this->assertContains('tables', $keys);
        $this->assertContains('kitchen', $keys);
        $this->assertContains('printers', $keys);
    }

    /**
     * Test all authenticated POS views have unified x-module-header and x-module-tabs.
     */
    public function test_all_pos_views_have_unified_module_header_and_tabs(): void
    {
        $views = [
            'orders' => resource_path('views/app/pos/orders.blade.php'),
            'shifts' => resource_path('views/app/pos/shifts.blade.php'),
            'reports' => resource_path('views/app/pos/reports.blade.php'),
            'tables' => resource_path('views/app/pos/tables.blade.php'),
            'kitchen' => resource_path('views/app/pos/kitchen.blade.php'),
            'prep_sheet' => resource_path('views/app/pos/prep_sheet.blade.php'),
            'printers' => resource_path('views/app/pos/printers/index.blade.php'),
        ];

        foreach ($views as $name => $path) {
            $content = File::get($path);

            $this->assertStringContainsString(
                '<x-module-header',
                $content,
                "View {$name} must include <x-module-header> component."
            );

            $this->assertStringContainsString(
                'module="pos"',
                $content,
                "View {$name} must specify module=\"pos\" in <x-module-header>."
            );

            $this->assertStringContainsString(
                '<x-module-tabs module="pos"',
                $content,
                "View {$name} must include <x-module-tabs module=\"pos\" /> component."
            );
        }
    }

    /**
     * Test shifts.blade.php does not contain duplicate headerTitle or headerSubtitle in @extends.
     */
    public function test_shifts_blade_does_not_duplicate_header_arguments(): void
    {
        $content = File::get(resource_path('views/app/pos/shifts.blade.php'));

        $this->assertStringNotContainsString('headerTitle', $content);
        $this->assertStringNotContainsString('headerSubtitle', $content);
    }
}
