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

class LayoutMobileErgonomicsAndSafariComplianceTest extends TestCase
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
            'email' => 'budi.fase8@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Fase 8 Mobile Ergonomics Studio',
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

    public function test_mobile_bottom_bar_center_action_meets_48px_touch_target(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert 48x48px button sizing and elevated ring
        $this->assertStringContainsString('w-12 h-12 -mt-5 rounded-full', $viewContent);
        $this->assertStringContainsString('shadow-[0_6px_20px_rgba(0,122,255,0.4)] ring-4', $viewContent);
        $this->assertStringContainsString('<i data-lucide="plus" class="w-6 h-6 stroke-[2.5]"></i>', $viewContent);

        // Old 40x40px sizing must be eliminated
        $this->assertStringNotContainsString('w-10 h-10 -mt-4 rounded-full', $viewContent);
    }

    public function test_ios_safari_input_auto_zoom_prevention_rules_are_enforced(): void
    {
        $appViewContent = File::get(resource_path('views/layouts/app.blade.php'));
        $topbarViewContent = File::get(resource_path('views/layouts/partials/topbar.blade.php'));

        // Assert global media query in layout CSS enforces min 16px font on mobile
        $this->assertStringContainsString('@media screen and (max-width: 639px)', $appViewContent);
        $this->assertStringContainsString('font-size: 16px !important;', $appViewContent);

        // Assert Quick Action inputs use text-base sm:text-sm
        $this->assertStringContainsString('text-base sm:text-sm text-black dark:text-white', $appViewContent);

        // Assert Spotlight search input uses text-base sm:text-[15px]
        $this->assertStringContainsString('text-base sm:text-[15px]', $topbarViewContent);
    }

    public function test_fluid_clamp_typography_is_configured_for_responsive_headings(): void
    {
        $typographyContent = File::get(resource_path('views/layouts/partials/typography.blade.php'));

        // Assert H1 fluid clamp typography
        $this->assertStringContainsString('font-size: clamp(1.25rem, 4vw, 2.125rem);', $typographyContent);

        // Assert H2 fluid clamp typography
        $this->assertStringContainsString('font-size: clamp(1.125rem, 3vw, 1.625rem);', $typographyContent);
    }

    public function test_toast_notifications_support_tap_close_and_swipe_dismiss(): void
    {
        $viewContent = File::get(resource_path('views/layouts/app.blade.php'));

        // Assert swipe-up touch handlers
        $this->assertStringContainsString('@touchstart="touchStartY = $event.touches[0].clientY"', $viewContent);
        $this->assertStringContainsString('@touchend="if (touchStartY - $event.changedTouches[0].clientY > 25)', $viewContent);

        // Assert explicit close (X) button
        $this->assertStringContainsString('@click="toastList = toastList.filter(item => item.id !== t.id)"', $viewContent);
        $this->assertStringContainsString('<i data-lucide="x" class="w-3.5 h-3.5"', $viewContent);
    }

    public function test_backoffice_page_renders_fase_8_mobile_first_standards(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('w-12 h-12 -mt-5', false);
        $response->assertSee('font-size: 16px !important;', false);
        $response->assertSee('clamp(1.25rem, 4vw, 2.125rem)', false);
        $response->assertSee('touchStartY', false);
        $response->assertDontSee('w-10 h-10 -mt-4', false);
    }
}
