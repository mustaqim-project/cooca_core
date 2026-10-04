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

class LayoutModalAndContainerSizingTest extends TestCase
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
            'email' => 'budi.fase7@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Fase 7 Test Studio',
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

    public function test_modal_css_defines_clean_sizing_hierarchy(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert base modal dialog class is present
        $this->assertStringContainsString('.app-modal-dialog', $viewContent);

        // Assert sizing modifier classes exist with correct scales
        $this->assertStringContainsString('.app-modal-dialog-sm', $viewContent);
        $this->assertStringContainsString('--modal-max-width: 28rem;', $viewContent);

        $this->assertStringContainsString('.app-modal-dialog-md', $viewContent);
        $this->assertStringContainsString('--modal-max-width: 36rem;', $viewContent);

        $this->assertStringContainsString('.app-modal-dialog-lg', $viewContent);
        $this->assertStringContainsString('--modal-max-width: 48rem;', $viewContent);

        $this->assertStringContainsString('.app-modal-dialog-xl', $viewContent);
        $this->assertStringContainsString('--modal-max-width: 64rem;', $viewContent);

        $this->assertStringContainsString('.app-modal-dialog-xxl', $viewContent);
        $this->assertStringContainsString('.app-modal-dialog-2xl', $viewContent);
        $this->assertStringContainsString('min(95vw, 84.375rem)', $viewContent);

        // Assert that .fixed.inset-0 .glass-card and .glass-panel blanket override is removed
        $this->assertStringNotContainsString('.fixed.inset-0 .glass-card,', $viewContent);
        $this->assertStringNotContainsString('.fixed.inset-0 .glass-panel,', $viewContent);
    }

    public function test_main_layout_container_uses_standard_1440px_width(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert main container uses max-w-[1440px]
        $this->assertStringContainsString('max-w-[1440px] w-full mx-auto', $viewContent);
        // Assert old max-w-[1400px] is no longer used in main wrapper
        $this->assertStringNotContainsString('max-w-[1400px] w-full mx-auto', $viewContent);
    }

    public function test_artificial_countdown_timer_is_completely_eliminated(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert fake countdown functions and variables are eliminated
        $this->assertStringNotContainsString('window.coocaCountdown', $viewContent);
        $this->assertStringNotContainsString('coocaCountdown()', $viewContent);
        $this->assertStringNotContainsString('cooca_coming_soon_launch', $viewContent);
        $this->assertStringNotContainsString('Hitung Mundur Peluncuran', $viewContent);
        $this->assertStringNotContainsString('Target peluncuran:', $viewContent);
    }

    public function test_coming_soon_modal_uses_bento_apple_hig_v2_roadmap_preview(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert Bento Apple HIG modal structure & tokens
        $this->assertStringContainsString('app-modal-dialog app-modal-dialog-md', $viewContent);
        $this->assertStringContainsString('rounded-[24px]', $viewContent);
        $this->assertStringContainsString("{{ __('common.in_development') }}", $viewContent);
        $this->assertStringContainsString("{{ __('common.view_plans_quota') }}", $viewContent);
        $this->assertStringContainsString("{{ __('common.close') }}", $viewContent);
        $this->assertStringContainsString('Enterprise Quality & Safety', $viewContent);
        $this->assertStringContainsString('Cooca Roadmap', $viewContent);

        // Assert custom event dispatching remains intact
        $this->assertStringContainsString("window.dispatchEvent(new CustomEvent('cooca-coming-soon'", $viewContent);
    }

    public function test_multilingual_keys_for_roadmap_and_development_exist(): void
    {
        // Indonesian dictionary
        $this->assertEquals('Segera Hadir', __('common.coming_soon', [], 'id'));
        $this->assertEquals('Dalam Tahap Pengembangan', __('common.in_development', [], 'id'));
        $this->assertNotEmpty(__('common.in_development_desc', [], 'id'));
        $this->assertEquals('Lihat Paket & Kuota Saya', __('common.view_plans_quota', [], 'id'));

        // English dictionary
        $this->assertEquals('Coming Soon', __('common.coming_soon', [], 'en'));
        $this->assertEquals('In Active Development', __('common.in_development', [], 'en'));
        $this->assertNotEmpty(__('common.in_development_desc', [], 'en'));
        $this->assertEquals('View My Plans & Quotas', __('common.view_plans_quota', [], 'en'));
    }

    public function test_backoffice_page_renders_with_fase_7_modal_and_container_standards(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('max-w-[1440px]', false);
        $response->assertSee('.app-modal-dialog', false);
        $response->assertSee('.app-modal-dialog-xxl', false);
        $response->assertSee('Dalam Tahap Pengembangan', false);
        $response->assertDontSee('Hitung Mundur Peluncuran', false);
        $response->assertDontSee('cooca_coming_soon_launch', false);
    }
}
