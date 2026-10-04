<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutTopbarAndSpotlightTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Cache::flush();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Bento Owner',
            'email' => 'owner_topbar@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Bento Coffee Roastery',
            'business_type' => 'fnb',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_topbar_renders_bento_apple_hig_language_switcher_in_id(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('data-lucide="globe"', false);
        $response->assertSee(route('locale.switch', ['locale' => 'id']), false);
        $response->assertSee(route('locale.switch', ['locale' => 'en']), false);
        $response->assertSee('Bahasa Indonesia');
        $response->assertSee('English');
    }

    public function test_topbar_renders_bento_apple_hig_language_switcher_in_en(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('data-lucide="globe"', false);
        $response->assertSee(route('locale.switch', ['locale' => 'id']), false);
        $response->assertSee(route('locale.switch', ['locale' => 'en']), false);
        $response->assertSee('Switch Language');
        $response->assertSee('Light');
        $response->assertSee('Dark');
        $response->assertSee('System');
    }

    public function test_topbar_renders_spotlight_command_palette_and_bilingual_keywords(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('open-spotlight');
        $response->assertSee('Ctrl K');
        $response->assertSee('executive overview penjualan sales omset revenue laba profit summary');
        $response->assertSee('kasir pos cashier');
        $response->assertSee('gudang stok inventory warehouse');
    }

    public function test_topbar_renders_apple_alert_logout_confirmation_with_no_panic_microcopy(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('showLogoutConfirm = true', false);
        $response->assertSee('Keluar dari Sesi?');
        $response->assertSee('Tenang: Seluruh data dan transaksi Anda telah tersimpan aman di sistem.');
        $response->assertSee(route('logout'), false);
    }

    public function test_topbar_logout_confirmation_microcopy_in_english(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sign out from session?');
        $response->assertSee('Don&#039;t worry: All your data and transactions are safely stored in the cloud.', false);
    }
}
