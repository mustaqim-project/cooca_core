<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LayoutMultiIndustryAutoHidingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Multi Industry Test',
            'email'             => 'owner_industry_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }

    private function createTenant(string $name, string $templateCode, array $disabledModules = []): Business
    {
        $ownerRole = \App\Models\Role::where('slug', 'owner')->first();

        $business = Business::create([
            'name'              => $name,
            'slug'              => Str::slug($name) . '-' . Str::random(5),
            'email'             => 'biz_' . Str::random(6) . '@test.local',
            'phone'             => '62813' . rand(10000000, 99999999),
            'status'            => 'active',
            'template_code'     => $templateCode,
            'disabled_modules'  => !empty($disabledModules) ? $disabledModules : ModuleRegistry::getDisabledModulesForTemplate($templateCode),
        ]);

        $this->owner->businesses()->attach($business->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->owner->update(['active_business_id' => $business->id]);

        return $business;
    }

    public function test_fnb_restaurant_tenant_sees_kds_tables_and_adaptive_pos_resto_label(): void
    {
        $fnbBiz = $this->createTenant('Cafe Kopi Nusantara', 'fnb_resto');
        $this->assertTrue($fnbBiz->isFoodIndustry());
        $this->assertTrue($fnbBiz->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $fnbBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($fnbBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $fnbBiz->id,
                'business_id'        => $fnbBiz->id,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Adaptive Label is "Kasir & POS Resto" for F&B
        $response->assertSee(__('navigation.pos_resto'));

        // 2. Dine-in KDS and Tables must be rendered in Sidebar
        $response->assertSee('id="tour-nav-pos-kitchen"', false);
        $response->assertSee('id="tour-nav-pos-tables"', false);

        // 3. Dine-in KDS and Tables must be rendered in Mobile Action Sheet
        $response->assertSee(route('pos.kitchen.index'));
        $response->assertSee(route('pos.tables.index'));
        $response->assertSee(__('quick_actions.sheet.kds_title'));
        $response->assertSee(__('quick_actions.sheet.tables_title'));
    }

    public function test_retail_reseller_tenant_hides_kds_tables_and_displays_pos_terminal_label(): void
    {
        $retailBiz = $this->createTenant('Toko Sinar Fashion Retail', 'retail_reseller');
        $this->assertFalse($retailBiz->isFoodIndustry());
        $this->assertFalse($retailBiz->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $retailBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($retailBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $retailBiz->id,
                'business_id'        => $retailBiz->id,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Adaptive Label is "Terminal Kasir POS" for non-F&B
        $response->assertSee(__('navigation.pos_terminal'));

        // 2. Dine-in KDS & Tables must NOT be rendered in Sidebar
        $response->assertDontSee('id="tour-nav-pos-kitchen"', false);
        $response->assertDontSee('id="tour-nav-pos-tables"', false);

        // 3. Dine-in KDS & Tables links must NOT be rendered in Mobile Action Sheet
        $response->assertDontSee(route('pos.kitchen.index'));
        $response->assertDontSee(route('pos.tables.index'));
    }

    public function test_service_workshop_tenant_injects_workshop_services_navigation_and_action_sheet(): void
    {
        $workshopBiz = $this->createTenant('Bengkel Motor Maju Jaya', 'service_workshop');
        $this->assertTrue($workshopBiz->isWorkshop());
        $this->assertTrue($workshopBiz->isServiceSector());
        $this->assertFalse($workshopBiz->isFoodIndustry());

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $workshopBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($workshopBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $workshopBiz->id,
                'business_id'        => $workshopBiz->id,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Workshop Navigation in Klaster 1 Daily Ops
        $response->assertSee('id="tour-nav-services"', false);
        $response->assertSee(__('navigation.services_workshop'));
        $response->assertSee('SPK');

        // 2. Workshop shortcut in Mobile Action Sheet
        $response->assertSee(route('services.index'));
        $response->assertSee(__('quick_actions.sheet.services_title'));
        $response->assertSee(__('quick_actions.sheet.services_desc'));

        // 3. Non-F&B check: no KDS or Tables
        $response->assertDontSee('id="tour-nav-pos-kitchen"', false);
        $response->assertDontSee('id="tour-nav-pos-tables"', false);
    }

    public function test_disabled_modules_dynamically_auto_hide_navigation(): void
    {
        // Business starts with POS DINEIN enabled
        $biz = $this->createTenant('Resto Nusantara Dynamic', 'fnb_resto', []);
        $this->assertTrue($biz->isModuleEnabled(ModuleRegistry::MODULE_POS_DINEIN));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $biz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($biz, $membership);

        $resp1 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $biz->id, 'business_id' => $biz->id])
            ->get(route('dashboard'));
        $resp1->assertSee('id="tour-nav-pos-kitchen"', false);

        // Now dynamically disable POS DINEIN
        $biz->disableModule(ModuleRegistry::MODULE_POS_DINEIN);
        $this->assertTrue($biz->isModuleDisabled(ModuleRegistry::MODULE_POS_DINEIN));

        Context::flush();
        Context::setBusiness($biz->fresh(), $membership);

        $resp2 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $biz->id, 'business_id' => $biz->id])
            ->get(route('dashboard'));
        $resp2->assertDontSee('id="tour-nav-pos-kitchen"', false);
        $resp2->assertDontSee('id="tour-nav-pos-tables"', false);
    }

    public function test_spotlight_command_palette_respects_industry_and_module_entitlements(): void
    {
        $fnbBiz = $this->createTenant('Cafe Kopi Spotlight', 'fnb_resto');

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $fnbBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($fnbBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $fnbBiz->id, 'business_id' => $fnbBiz->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        // Spotlight has KDS and Meja for F&B
        $response->assertSee(route('pos.kitchen.index'), false);
        $response->assertSee(route('pos.tables.index'), false);

        // Now test Retail in Spotlight
        $retailBiz = $this->createTenant('Apotek Sehat Pharmacy', 'retail_pharmacy');
        Context::flush();
        $membershipRetail = \App\Models\BusinessMembership::where('business_id', $retailBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($retailBiz, $membershipRetail);

        $responseRetail = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $retailBiz->id, 'business_id' => $retailBiz->id])
            ->get(route('dashboard'));

        $responseRetail->assertStatus(200);
        // Spotlight does NOT include KDS or Meja for Pharmacy
        $responseRetail->assertDontSee(route('pos.kitchen.index'), false);
        $responseRetail->assertDontSee(route('pos.tables.index'), false);
    }

    public function test_b2b_sales_navigation_auto_hides_when_module_disabled(): void
    {
        $fnbBiz = $this->createTenant('Cafe Kopi Nusantara', 'fnb_resto');
        $this->assertTrue($fnbBiz->isModuleDisabled(ModuleRegistry::MODULE_B2B_SALES));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $fnbBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($fnbBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $fnbBiz->id, 'business_id' => $fnbBiz->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('id="tour-group-b2b"', false);
        $response->assertDontSee('id="tour-nav-sales-orders"', false);
    }

    public function test_recipe_bom_navigation_and_spotlight_auto_hide_for_reseller(): void
    {
        $retailBiz = $this->createTenant('Toko Retail Reseller', 'retail_reseller');
        $this->assertTrue($retailBiz->isModuleDisabled(ModuleRegistry::MODULE_RECIPE_BOM));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $retailBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($retailBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $retailBiz->id, 'business_id' => $retailBiz->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        // Sidebar checks
        $response->assertDontSee('id="tour-nav-materials"', false);
        $response->assertDontSee(route('material-categories.index'), false);
    }

    public function test_costing_labor_machines_tab_auto_hides_for_non_manufacturing(): void
    {
        $fnbBiz = $this->createTenant('Restoran Rasa Enak', 'fnb_resto');
        $this->assertTrue($fnbBiz->isModuleDisabled(ModuleRegistry::MODULE_LABOR_MACHINES));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $fnbBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($fnbBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $fnbBiz->id, 'business_id' => $fnbBiz->id])
            ->get(route('calculator.index'));

        $response->assertStatus(200);
        // "Upah Kerja & Mesin" tab must NOT be visible for non-manufacturing
        $response->assertDontSee(route('labor-machines.index'), false);

        // Now test manufacturing tenant (e.g. mfg_garment)
        $mfgBiz = $this->createTenant('Pabrik Konveksi Garment', 'mfg_garment');
        $this->assertTrue($mfgBiz->isModuleEnabled(ModuleRegistry::MODULE_LABOR_MACHINES));

        Context::flush();
        $mfgMembership = \App\Models\BusinessMembership::where('business_id', $mfgBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($mfgBiz, $mfgMembership);

        $mfgResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $mfgBiz->id, 'business_id' => $mfgBiz->id])
            ->get(route('calculator.index'));

        $mfgResponse->assertStatus(200);
        $mfgResponse->assertSee(route('labor-machines.index'), false);
    }

    public function test_warehouse_spotlight_auto_hides_when_module_disabled(): void
    {
        $agencyBiz = $this->createTenant('Digital Marketing Agency', 'service_agency');
        $this->assertTrue($agencyBiz->isModuleDisabled(ModuleRegistry::MODULE_INVENTORY_WAREHOUSE));

        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $agencyBiz->id)
            ->where('user_id', $this->owner->id)
            ->first();
        Context::setBusiness($agencyBiz, $membership);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $agencyBiz->id, 'business_id' => $agencyBiz->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Ringkasan Gudang &amp; Persediaan', false);
        $response->assertDontSee('Lokasi Gudang', false);
    }
}

