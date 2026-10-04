<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutRealTimeAndPollingInfrastructureTest extends TestCase
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
            'email' => 'budi.fase9@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Fase 9 Realtime Engine Co',
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

    public function test_cooca_poller_and_cooca_bus_javascript_utilities_are_injected_in_layout(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert Global Reactive Event Bus (CoocaBus) exists with required methods
        $this->assertStringContainsString('window.CoocaBus = {', $viewContent);
        $this->assertStringContainsString('emit(event, detail = {})', $viewContent);
        $this->assertStringContainsString('on(event, handler)', $viewContent);
        $this->assertStringContainsString('emitDataMutated(type, data = {})', $viewContent);
        $this->assertStringContainsString("'cooca-data-mutated'", $viewContent);

        // Assert Smart AJAX Polling Engine (CoocaPoller) exists with required methods
        $this->assertStringContainsString('window.CoocaPoller = (function()', $viewContent);
        $this->assertStringContainsString('register(key, callback, options = {})', $viewContent);
        $this->assertStringContainsString('unregister(key)', $viewContent);
        $this->assertStringContainsString('pause(key)', $viewContent);
        $this->assertStringContainsString('resume(key)', $viewContent);
        $this->assertStringContainsString('trigger(key)', $viewContent);
        $this->assertStringContainsString('triggerAll()', $viewContent);
        $this->assertStringContainsString('isDocumentVisible()', $viewContent);

        // Assert Page Visibility API integration
        $this->assertStringContainsString("document.addEventListener('visibilitychange'", $viewContent);
        $this->assertStringContainsString("document.visibilityState === 'visible'", $viewContent);

        // Assert reactive auto-sync on mutation event
        $this->assertStringContainsString("window.addEventListener('cooca-data-mutated'", $viewContent);
    }

    public function test_mutation_observer_is_scoped_to_dynamic_containers_to_prevent_cpu_throttling(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert MutationObserver targets dynamic containers rather than unconditional whole document.body
        $this->assertStringContainsString("document.getElementById('main-content')", $viewContent);
        $this->assertStringContainsString("document.getElementById('global-modals-container')", $viewContent);
        $this->assertStringContainsString("document.getElementById('alpine-toast-container')", $viewContent);
        $this->assertStringContainsString('window.createCoocaIcons = function()', $viewContent);

        // Main content and modals container must have respective IDs
        $this->assertStringContainsString('id="main-content"', $viewContent);
        $this->assertStringContainsString('id="global-modals-container"', $viewContent);
        $this->assertStringContainsString('id="alpine-toast-container"', $viewContent);
    }

    public function test_storage_and_audit_pruning_preview_modal_is_rendered_with_bento_cards_and_safe_guarantee(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert Alpine state and event listener
        $this->assertStringContainsString('storagePruningOpen: false', $viewContent);
        $this->assertStringContainsString("window.addEventListener('open-storage-pruning-modal'", $viewContent);

        // Assert Storage Pruning Modal markup
        $this->assertStringContainsString('x-show="storagePruningOpen"', $viewContent);
        $this->assertStringContainsString("data-lucide=\"hard-drive\"", $viewContent);
        $this->assertStringContainsString("__('common.storage_pruning_title')", $viewContent);
        $this->assertStringContainsString("__('common.storage_pruning_subtitle')", $viewContent);

        // Assert 3 Bento category cards
        $this->assertStringContainsString("__('common.storage_audit_logs_title')", $viewContent);
        $this->assertStringContainsString("__('common.storage_audit_logs_desc')", $viewContent);
        $this->assertStringContainsString("__('common.storage_sync_logs_title')", $viewContent);
        $this->assertStringContainsString("__('common.storage_sync_logs_desc')", $viewContent);
        $this->assertStringContainsString("__('common.storage_cache_index_title')", $viewContent);
        $this->assertStringContainsString("__('common.storage_cache_index_desc')", $viewContent);

        // Assert Safety Protection Guarantee
        $this->assertStringContainsString("__('common.storage_safe_guarantee')", $viewContent);
        $this->assertStringContainsString("__('common.storage_open_settings')", $viewContent);
    }

    public function test_sidebar_subscription_card_renders_storage_pruning_modal_triggers(): void
    {
        $viewContent = File::get(resource_path('views/layouts/partials/sidebar.blade.php'));

        // Assert trigger button is present for opening the storage pruning modal
        $this->assertStringContainsString("@click=\"\$dispatch('open-storage-pruning-modal')\"", $viewContent);
        $this->assertStringContainsString("__('common.storage_pruning_preview_btn')", $viewContent);
        $this->assertStringContainsString("data-lucide=\"hard-drive\"", $viewContent);
    }

    public function test_storage_pruning_and_realtime_translations_exist_in_both_locales(): void
    {
        // Indonesian dictionary verification
        $idCommon = include resource_path('../lang/id/common.php');
        $this->assertArrayHasKey('storage_pruning_title', $idCommon);
        $this->assertArrayHasKey('storage_pruning_subtitle', $idCommon);
        $this->assertArrayHasKey('storage_audit_logs_title', $idCommon);
        $this->assertArrayHasKey('storage_audit_logs_desc', $idCommon);
        $this->assertArrayHasKey('storage_sync_logs_title', $idCommon);
        $this->assertArrayHasKey('storage_sync_logs_desc', $idCommon);
        $this->assertArrayHasKey('storage_cache_index_title', $idCommon);
        $this->assertArrayHasKey('storage_cache_index_desc', $idCommon);
        $this->assertArrayHasKey('storage_safe_guarantee', $idCommon);
        $this->assertArrayHasKey('storage_open_settings', $idCommon);
        $this->assertArrayHasKey('storage_pruning_preview_btn', $idCommon);
        $this->assertArrayHasKey('realtime_synced', $idCommon);

        // English dictionary verification
        $enCommon = include resource_path('../lang/en/common.php');
        $this->assertArrayHasKey('storage_pruning_title', $enCommon);
        $this->assertArrayHasKey('storage_pruning_subtitle', $enCommon);
        $this->assertArrayHasKey('storage_audit_logs_title', $enCommon);
        $this->assertArrayHasKey('storage_audit_logs_desc', $enCommon);
        $this->assertArrayHasKey('storage_sync_logs_title', $enCommon);
        $this->assertArrayHasKey('storage_sync_logs_desc', $enCommon);
        $this->assertArrayHasKey('storage_cache_index_title', $enCommon);
        $this->assertArrayHasKey('storage_cache_index_desc', $enCommon);
        $this->assertArrayHasKey('storage_safe_guarantee', $enCommon);
        $this->assertArrayHasKey('storage_open_settings', $enCommon);
        $this->assertArrayHasKey('storage_pruning_preview_btn', $enCommon);
        $this->assertArrayHasKey('realtime_synced', $enCommon);
    }
}
