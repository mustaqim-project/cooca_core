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

class LayoutMultiLanguageAndLocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Cache::flush();

        $this->owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner_test@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Bento Test',
            'business_type' => 'fnb',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_locale_switch_endpoint_switches_session_and_cookie_to_en(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('locale.switch', ['locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('cooca_locale', 'en');
    }

    public function test_locale_switch_endpoint_switches_to_id(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'en'])
            ->get(route('locale.switch', ['locale' => 'id']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'id');
        $response->assertCookie('cooca_locale', 'id');
    }

    public function test_locale_switch_safely_falls_back_to_id_for_unsupported_locale(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('locale.switch', ['locale' => 'fr']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'id');
        $response->assertCookie('cooca_locale', 'id');
    }

    public function test_translations_render_correctly_in_id_and_en(): void
    {
        // Indonesian (ID)
        app()->setLocale('id');
        Carbon::setLocale('id');

        $this->assertSame('Catat Pengeluaran Cepat', __('quick_actions.expense.title'));
        $this->assertSame('Operasional Harian', __('navigation.clusters.daily_ops'));
        $this->assertSame('Simpan', __('common.save'));
        $this->assertSame('Beli Stok Masuk Cepat', __('quick_actions.stock_in.title'));
        $this->assertSame('Tambah Bahan Baku Cepat', __('quick_actions.material.title'));

        // English (EN)
        app()->setLocale('en');
        Carbon::setLocale('en');

        $this->assertSame('Record Quick Expense', __('quick_actions.expense.title'));
        $this->assertSame('Daily Operations', __('navigation.clusters.daily_ops'));
        $this->assertSame('Save', __('common.save'));
        $this->assertSame('Quick Stock-In Purchase', __('quick_actions.stock_in.title'));
        $this->assertSame('Add Raw Material', __('quick_actions.material.title'));
    }

    public function test_layout_renders_with_dynamic_html_lang_and_injected_i18n_script(): void
    {
        // Request in English session
        $responseEn = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
                'locale' => 'en',
            ])
            ->get(route('dashboard'));

        $responseEn->assertOk();
        $responseEn->assertSee('<html lang="en"', false);
        $responseEn->assertSee('window.COOCA_LOCALE = \'en\';', false);
        $responseEn->assertSee('window.COOCA_I18N = {', false);
        $responseEn->assertSee('Record Quick Expense', false);

        // Request in Indonesian session
        $responseId = $this->actingAs($this->owner)
            ->withSession([
                'active_business_id' => $this->business->id,
                'locale' => 'id',
            ])
            ->get(route('dashboard'));

        $responseId->assertOk();
        $responseId->assertSee('<html lang="id"', false);
        $responseId->assertSee('window.COOCA_LOCALE = \'id\';', false);
        $responseId->assertSee('window.COOCA_I18N = {', false);
        $responseId->assertSee('Catat Pengeluaran Cepat', false);
    }
}
