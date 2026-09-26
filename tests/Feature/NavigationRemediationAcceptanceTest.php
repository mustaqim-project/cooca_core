<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * End-to-End Acceptance Tests for PRD-09:
 * Persistent Tabs, Breadcrumbs, Module Route Protection, and Global Layout Architecture.
 */
final class NavigationRemediationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Acceptance Nav Test',
            'email'             => 'owner_acceptance_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Acceptance Test PRD-09',
            'currency'         => 'IDR',
            'currency_code'    => 'IDR',
            'currency_symbol'  => 'Rp',
            'business_scale'   => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Utama Acceptance',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);
    }

    /**
     * AC-1: Tab persistence across route transitions.
     * Moving between materials catalog -> categories -> units preserves the complete tab collection.
     */
    public function test_ac1_tabs_persist_across_route_transitions_in_materials_module(): void
    {
        // 1. Visit materials catalog
        $response1 = $this->actingAs($this->owner, 'web')->get(route('materials.index'));
        $response1->assertOk();
        $response1->assertSee('Katalog Bahan Baku');
        $response1->assertSee('Kategori Bahan');
        $response1->assertSee('Satuan Ukur');

        // 2. Visit material categories
        $response2 = $this->actingAs($this->owner, 'web')->get(route('material-categories.index'));
        $response2->assertOk();
        $response2->assertSee('Katalog Bahan Baku');
        $response2->assertSee('Kategori Bahan');
        $response2->assertSee('Satuan Ukur');

        // 3. Visit units with contextual wrapper
        $response3 = $this->actingAs($this->owner, 'web')->get(route('units.index', ['from' => 'materials']));
        $response3->assertOk();
        $response3->assertSee('Katalog Bahan Baku');
        $response3->assertSee('Kategori Bahan');
        $response3->assertSee('Satuan Ukur');
    }

    /**
     * AC-2: Visual layout hierarchy (Breadcrumb -> Title/Action -> Tabs -> Content).
     */
    public function test_ac2_visual_layout_hierarchy_rendered_properly(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('purchase-orders.index'));

        $response->assertOk();
        $content = $response->getContent();

        $mainContent = strstr($content, '<main');
        $this->assertNotFalse($mainContent, 'Main layout container must exist');

        $breadcrumbPos = strpos($mainContent, 'aria-label="Breadcrumb"');
        $titlePos = strpos($mainContent, 'Manajemen Purchase Order (PO)');
        $tabsPos = strpos($mainContent, 'data-module-tabs="purchasing"');

        $this->assertNotFalse($breadcrumbPos, 'Breadcrumb must be present in main content');
        $this->assertNotFalse($titlePos, 'Title must be present in main content');
        $this->assertNotFalse($tabsPos, 'Module tabs container must be present in main content');

        // Verify top-to-bottom hierarchy: Breadcrumb -> Title -> Tabs
        $this->assertTrue($breadcrumbPos < $titlePos, 'Breadcrumb must appear before Title');
        $this->assertTrue($titlePos < $tabsPos, 'Title must appear before Tabs');
    }

    /**
     * AC-3: Mobile horizontal scrollability classes on module tabs.
     */
    public function test_ac3_module_tabs_have_mobile_horizontal_scroll_classes(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.cash-bank.index'));

        $response->assertOk();
        $response->assertSee('overflow-x-auto');
        $response->assertSee('flex-nowrap');
        $response->assertSee('whitespace-nowrap');
        $response->assertSee('no-scrollbar');
    }

    /**
     * AC-4: Module protection when a module is disabled via CheckModuleEnabled middleware.
     */
    public function test_ac4_disabled_module_redirects_with_alert(): void
    {
        // Disable CRM loyalty module for this tenant
        $this->business->update([
            'disabled_modules' => [ModuleRegistry::MODULE_CRM_LOYALTY],
        ]);

        $response = $this->actingAs($this->owner, 'web')->get(route('crm.members.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    /**
     * AC-5: Tab-level RBAC gating. Unauthorized tabs are hidden from DOM.
     */
    public function test_ac5_tab_level_rbac_omits_unauthorized_tabs_from_dom(): void
    {
        // Create user with cashier role (does not have sales.returns permission)
        $cashier = User::create([
            'name'              => 'Cashier Limited Nav Test',
            'email'             => 'cashier_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business->users()->attach($cashier->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'cashier',
        ]);

        $cashier->update(['active_business_id' => $this->business->id]);

        // Attempting to visit sales orders page
        $response = $this->actingAs($cashier, 'web')->get(route('sales.orders.index'));

        // If cashier has sales.view, verify sales.returns is NOT in module tabs
        if ($response->status() === 200) {
            preg_match('/<nav[^>]*data-module-tabs="sales"[^>]*>(.*?)<\/nav>/s', $response->getContent(), $matches);
            $tabsHtml = $matches[1] ?? '';
            $this->assertStringNotContainsString('Retur Penjualan', $tabsHtml);
            $this->assertStringContainsString('Pesanan Penjualan (SO)', $tabsHtml);
        } else {
            // Or access is forbidden at route level
            $this->assertContains($response->status(), [403, 302]);
        }
    }

    /**
     * AC-6: Global Layout & Sidebar synchronization with active module.
     */
    public function test_ac6_sidebar_synchronizes_with_active_module(): void
    {
        // 1. Visiting WhatsApp opens marketing group
        $resWa = $this->actingAs($this->owner, 'web')->get(route('whatsapp.index'));
        $resWa->assertOk();
        $resWa->assertSee('marketingOpen: true', false);

        // 2. Visiting Quotations opens sales group
        $resQuote = $this->actingAs($this->owner, 'web')->get(route('sales.quotations.index'));
        $resQuote->assertOk();
        $resQuote->assertSee('salesOpen: true', false);

        // 3. Visiting Balance Sheet opens finance group
        $resBal = $this->actingAs($this->owner, 'web')->get(route('finance.balance-sheet'));
        $resBal->assertOk();
        $resBal->assertSee('financeOpen: true', false);
    }
}
