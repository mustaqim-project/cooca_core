<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutFinalRemediationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $ownerRole = Role::where('slug', 'owner')->first();

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.fase10@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Fase 10 Final Acceptance Corp',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'rounding_strategy' => 'ROUND_100',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        $membership = BusinessMembership::where('business_id', $this->business->id)->where('user_id', $this->user->id)->first();
        Context::setBusiness($this->business, $membership);
    }

    /**
     * F-01 to F-04: Workflow Architecture & Event Bus
     */
    public function test_workflow_dimension_findings_f01_to_f04_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));

        // F-01: No raw database queries in view template
        $this->assertStringNotContainsString('Material::where', $appView);
        $this->assertStringContainsString("route('dashboard.quick-materials-list')", $appView);

        // F-02: Global Reactive Event Bus (CoocaBus) & Data Mutation Event
        $this->assertStringContainsString('window.CoocaBus = {', $appView);
        $this->assertStringContainsString('emitDataMutated(type, data = {})', $appView);
        $this->assertStringContainsString("'cooca-data-mutated'", $appView);

        // F-03: Scoped MutationObserver targeting dynamic container nodes
        $this->assertStringContainsString("document.getElementById('main-content')", $appView);
        $this->assertStringContainsString("document.getElementById('global-modals-container')", $appView);
        $this->assertStringContainsString('window.createCoocaIcons = function()', $appView);

        // F-04: Deduplicated flash notifications
        $this->assertStringContainsString('window.__coocaFlashHandled', $appView);
    }

    /**
     * F-05 to F-09: Security, Anti-Fraud & Human Error Mitigation
     */
    public function test_security_dimension_findings_f05_to_f09_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/partials/sidebar.blade.php'));
        $topbarView = File::get(resource_path('views/layouts/partials/topbar.blade.php'));

        // F-05: Double-submit lock on quick action forms
        $this->assertStringContainsString('@submit.prevent="submitQuickExpense"', $appView);
        $this->assertStringContainsString('@submit.prevent="submitQuickStockIn"', $appView);
        $this->assertStringContainsString('@submit.prevent="submitQuickMaterial"', $appView);
        $this->assertStringContainsString('if (this.isSubmitting) return;', $appView);
        $this->assertStringContainsString('X-Idempotency-Key', $appView);

        // F-06: Rupiah formatting / auto-masking inputs
        $this->assertStringContainsString('formatRupiah(val)', $appView);
        $this->assertStringContainsString('handleExpenseAmountInput($event)', $appView);

        // F-07: Maker-Checker Supervisor PIN on Quick Expense
        $this->assertStringContainsString('requiresExpenseSupervisorPin', $appView);
        $this->assertStringContainsString('supervisor_pin', $appView);

        // F-08: Cached audit logs counter on sidebar
        $this->assertStringContainsString('Cache::remember', $sidebarView);
        $this->assertStringContainsString('high_risk_logs_count', $sidebarView);

        // F-09: Apple Alert logout confirmation modal
        $this->assertStringContainsString('showLogoutConfirm', $topbarView);
        $this->assertStringContainsString("__('common.logout_confirm_title')", $topbarView);
        $this->assertStringContainsString("__('common.logout_confirm_msg')", $topbarView);
    }

    /**
     * F-10 to F-14: Multi-Industry Compliance & Context-Aware Auto-Hiding
     */
    public function test_multi_industry_dimension_findings_f10_to_f14_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/partials/sidebar.blade.php'));
        $topbarView = File::get(resource_path('views/layouts/partials/topbar.blade.php'));

        // F-10: Dynamic context auto-hiding for F&B modules
        $this->assertStringContainsString('MODULE_POS_DINEIN', $appView);

        // F-11: Context-aware adaptive label for POS
        $this->assertStringContainsString('isFoodIndustry()', $sidebarView);

        // F-12: Service & Workshop module conditional navigation
        $this->assertStringContainsString('MODULE_SERVICE_WORKSHOP', $sidebarView);
        $this->assertStringContainsString("route('services.index')", $sidebarView);

        // F-13: Dynamic Spotlight filtering by active modules
        $this->assertStringContainsString('MODULE_POS_DINEIN', $topbarView);
        $this->assertStringContainsString('MODULE_SERVICE_WORKSHOP', $topbarView);

        // F-14: Master data logistics separated into Master Data & Catalog cluster
        $this->assertStringContainsString('navigation.clusters.master_data', $sidebarView);
    }

    /**
     * F-15 to F-19: UI Consistency & Information Architecture
     */
    public function test_ui_consistency_and_ia_findings_f15_to_f19_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/partials/sidebar.blade.php'));
        $topbarView = File::get(resource_path('views/layouts/partials/topbar.blade.php'));

        // F-15: Canvas XXL Modal sizing unconstrained
        $this->assertStringContainsString('.app-modal-dialog-xxl', $appView);
        $this->assertStringNotContainsString('.glass-card { max-width: min(calc(100vw - 2rem), 32rem) !important; }', $appView);

        // F-16: Technical settings unified into Settings Hub cluster 4
        $this->assertStringContainsString('navigation.clusters.settings_hub', $sidebarView);
        $this->assertStringContainsString("route('settings.index')", $sidebarView);

        // F-17: Sidebar lines deduplicated
        $sidebarLines = count(explode("\n", $sidebarView));
        $this->assertLessThan(2400, $sidebarLines, "Sidebar lines ({$sidebarLines}) should be under 2400 lines.");

        // F-18: Topbar subtitle is concise and hidden on mobile (<sm:hidden)
        $this->assertStringContainsString('hidden sm:block', $topbarView);

        // F-19: Main content wrapper max width standard 1440px
        $this->assertStringContainsString('max-w-[1440px]', $appView);
    }

    /**
     * F-20 to F-23: Responsive UI/UX & Mobile-First Ergonomics
     */
    public function test_responsive_dimension_findings_f20_to_f23_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $topbarView = File::get(resource_path('views/layouts/partials/topbar.blade.php'));
        $typographyView = File::get(resource_path('views/layouts/partials/typography.blade.php'));

        // F-20: 48x48px touch target for center action button
        $this->assertStringContainsString('w-12 h-12 -mt-5 rounded-full', $appView);

        // F-21: iOS Safari auto-zoom prevention (min 16px on inputs on mobile)
        $this->assertStringContainsString('font-size: 16px !important;', $appView);
        $this->assertStringContainsString('text-base sm:text-sm', $appView);

        // F-22: Minimum 8px gap between topbar action buttons
        $this->assertStringContainsString('gap-2 sm:gap-3', $topbarView);

        // F-23: Fluid clamp typography
        $this->assertStringContainsString('font-size: clamp(1.25rem, 4vw, 2.125rem);', $typographyView);
    }

    /**
     * F-24 to F-27: Bento Apple HIG v2.0 & Real-Time Infrastructure
     */
    public function test_bento_and_realtime_findings_f24_to_f27_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/partials/sidebar.blade.php'));

        // F-24: Elimination of fake 30-day countdown timer
        $this->assertStringNotContainsString('coming-soon-launch-date', $appView);
        $this->assertStringContainsString('comingSoonOpen', $appView);

        // F-25: Smart AJAX Polling Engine (CoocaPoller) with Page Visibility API
        $this->assertStringContainsString('window.CoocaPoller = (function()', $appView);
        $this->assertStringContainsString("document.addEventListener('visibilitychange'", $appView);

        // F-26: Storage & Audit Pruning Preview modal
        $this->assertStringContainsString('storagePruningOpen', $appView);
        $this->assertStringContainsString("@click=\"\$dispatch('open-storage-pruning-modal')\"", $sidebarView);

        // F-27: Tap & gesture swipe-up to dismiss on floating toast banner
        $this->assertStringContainsString('@touchstart="touchStartY = $event.touches[0].clientY"', $appView);
        $this->assertStringContainsString('@click="toastList = toastList.filter(item => item.id !== t.id)"', $appView);
    }

    /**
     * F-28 to F-31: Multi-Language (i18n & l10n Full-Stack)
     */
    public function test_multi_language_findings_f28_to_f31_are_fully_resolved(): void
    {
        $appView = File::get(resource_path('views/layouts/app.blade.php'));
        $topbarView = File::get(resource_path('views/layouts/partials/topbar.blade.php'));

        // F-28: Root HTML tag is dynamic
        $this->assertStringContainsString('<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}"', $appView);

        // F-29: Modular dictionaries exist and contain navigation keys
        $idNav = include resource_path('../lang/id/navigation.php');
        $enNav = include resource_path('../lang/en/navigation.php');
        $this->assertArrayHasKey('cluster_daily_ops', $idNav);
        $this->assertArrayHasKey('cluster_daily_ops', $enNav);
        $this->assertArrayHasKey('cluster_master_data', $idNav);
        $this->assertArrayHasKey('cluster_master_data', $enNav);
        $this->assertArrayHasKey('cluster_finance_reports', $idNav);
        $this->assertArrayHasKey('cluster_finance_reports', $enNav);
        $this->assertArrayHasKey('cluster_settings_hub', $idNav);
        $this->assertArrayHasKey('cluster_settings_hub', $enNav);

        // F-30: Translatable Spotlight Command Palette with bilingual keywords
        $this->assertStringContainsString('x-model="query"', $topbarView);
        $this->assertStringContainsString('navigation.search_placeholder', $topbarView);
        $this->assertStringContainsString('locale.switch', $topbarView);

        // F-31: Bento Apple HIG Language Switcher dropdown
        $this->assertStringContainsString("__('common.switch_language')", $topbarView);
        $this->assertStringContainsString("route('locale.switch'", $topbarView);
    }
}
