<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutSidebarFourClustersTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $ownerRole = Role::where('slug', 'owner')->first();

        $this->ownerUser = User::create([
            'name' => 'Owner Cooca',
            'email' => 'owner@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Resto & Bakery Nusantara',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'rounding_strategy' => 'ROUND_100',
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->ownerUser->update(['active_business_id' => $this->business->id]);
        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->ownerUser->id)
            ->first();
        Context::setBusiness($this->business, $membership);
    }

    /**
     * Helper to create user with a specific role
     */
    private function createUserWithRole(string $roleSlug): User
    {
        $role = Role::where('slug', $roleSlug)->first();

        $user = User::create([
            'name' => ucfirst($roleSlug) . ' User',
            'email' => $roleSlug . '@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $roleSlug,
            'role_id' => $role?->id,
        ]);

        $user->update(['active_business_id' => $this->business->id]);

        return $user;
    }

    public function test_sidebar_renders_four_standard_clusters_for_owner(): void
    {
        $response = $this->actingAs($this->ownerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Klaster 1: Operasional Harian (Daily Ops)
        $response->assertSee(__('navigation.clusters.daily_ops'));
        $response->assertSee(__('navigation.dashboard_main'));
        $response->assertSee(__('navigation.portal_attendance'));
        $response->assertSee(__('navigation.pos_terminal'));
        $response->assertSee(__('navigation.b2b_sales'));

        // 2. Klaster 2: Master Data & Katalog (Master Data & Catalog)
        $response->assertSee(__('navigation.clusters.master_data'));
        $response->assertSee(__('navigation.products_catalog'));
        $response->assertSee(__('navigation.materials_recipes'));
        $response->assertSee(__('navigation.stocks_warehouse'));
        $response->assertSee(__('navigation.customers_data'));
        $response->assertSee(__('navigation.suppliers'));

        // 3. Klaster 3: Laporan & Keuangan (Reports & Finance)
        $response->assertSee(__('navigation.clusters.reports_finance'));
        $response->assertSee(__('navigation.cash_bank_books'));
        $response->assertSee(__('navigation.accounting_corporate'));
        $response->assertSee(__('navigation.hpp_cost_calculator'));
        $response->assertSee(__('navigation.reports_center'));

        // 4. Klaster 4: Pusat Pengaturan Terpadu (Settings Hub)
        $response->assertSee(__('navigation.clusters.settings_hub'));
        $response->assertSee(__('navigation.settings_general'));
        $response->assertSee(__('navigation.user_profile'));
        $response->assertSee(__('navigation.mar_rules'));
        $response->assertSee(__('navigation.audit_anti_fraud'));
        $response->assertSee(__('navigation.roles_permissions'));
    }

    public function test_sidebar_preserves_tour_attributes_and_aria(): void
    {
        $response = $this->actingAs($this->ownerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Verify Tour IDs exist in the DOM
        $response->assertSee('id="tour-nav-dashboard"', false);
        $response->assertSee('id="tour-nav-pos-terminal"', false);
        $response->assertSee('id="tour-group-pos"', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);
        $response->assertSee('id="tour-nav-suppliers"', false);
        $response->assertSee('id="tour-nav-calculator"', false);
        $response->assertSee('id="tour-nav-reports"', false);
        $response->assertSee('id="tour-nav-settings"', false);
    }

    public function test_cashier_role_has_restricted_visibility_in_four_clusters(): void
    {
        $cashier = $this->createUserWithRole('cashier');
        Context::flush();
        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $cashier->id)
            ->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Cashier sees Daily Ops -> Dashboard & POS Terminal links
        $response->assertSee(__('navigation.cluster_daily_ops'));
        $response->assertSee('id="tour-nav-pos-terminal"', false);

        // Cashier does not see Settings Hub management, audit logs, or approval rules in DOM
        $response->assertDontSee('id="tour-nav-settings"', false);
        $response->assertDontSee('id="tour-nav-audit-logs"', false);
        $response->assertDontSee('id="tour-nav-approval-rules"', false);
        $response->assertDontSee('href="' . route('roles.index') . '"', false);
    }

    public function test_warehouse_role_has_restricted_visibility_in_four_clusters(): void
    {
        $warehouse = $this->createUserWithRole('warehouse');
        Context::flush();
        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $warehouse->id)
            ->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($warehouse)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('products.index'));

        $response->assertStatus(200);

        // Warehouse sees Master Data -> Products & Materials
        $response->assertSee(__('navigation.cluster_master_data'));
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);

        // Warehouse does not see Settings Hub or Accounting rules
        $response->assertDontSee('id="tour-nav-settings"', false);
        $response->assertDontSee('id="tour-nav-audit-logs"', false);
        $response->assertDontSee('id="tour-nav-approval-rules"', false);
        $response->assertDontSee('href="' . route('roles.index') . '"', false);
    }

    public function test_navigation_dictionaries_contain_all_four_clusters_and_keys(): void
    {
        // Test ID dictionary
        app()->setLocale('id');
        $this->assertEquals('Operasional Harian', __('navigation.cluster_daily_ops'));
        $this->assertEquals('Master Data & Katalog', __('navigation.cluster_master_data'));
        $this->assertEquals('Laporan & Keuangan', __('navigation.cluster_finance_reports'));
        $this->assertEquals('Pusat Pengaturan', __('navigation.cluster_settings_hub'));

        // Test EN dictionary
        app()->setLocale('en');
        $this->assertEquals('Daily Operations', __('navigation.cluster_daily_ops'));
        $this->assertEquals('Master Data & Catalog', __('navigation.cluster_master_data'));
        $this->assertEquals('Reports & Finance', __('navigation.cluster_finance_reports'));
        $this->assertEquals('Settings Hub', __('navigation.cluster_settings_hub'));

        // Reset to ID
        app()->setLocale('id');
    }
}
