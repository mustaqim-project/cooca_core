<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StorefrontPopupCmsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private BusinessLandingPage $landingPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::factory()->create([
            'name' => 'Owner Kedai Kopi',
            'email' => 'owner.popup@cooca.id',
            'phone' => '081234567890',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Artisan Popup',
            'slug' => 'kopi-artisan-popup',
            'is_active' => true,
            'currency' => 'IDR',
            'email' => 'kopi.popup@cooca.id',
            'phone' => '081234567890',
            'address' => 'Jl. Malioboro No. 10, Yogyakarta',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => Role::where('slug', 'owner')->value('id'),
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Kopi Spesialis Sangrai Nusantara',
            'subheadline' => 'Kopi arabika asli nusantara dipanggang segar.',
            'is_published' => true,
            'theme_preset' => 'artisan_brew',
            'whatsapp_number' => '081234567890',
            'popup_enabled' => false,
            'popup_frequency' => 'once_per_day',
        ]);

        Context::flush();
    }

    public function test_owner_can_view_popup_cms_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('landing-page.popup.edit'));

        $response->assertOk();
        $response->assertViewIs('app.landing_page.popup');
        $response->assertSee('CMS Pop-Up Promo Storefront');
        $response->assertSee('Live Mobile Preview');
        $response->assertSee('Frekuensi Tampil untuk Pengunjung');
    }

    public function test_owner_can_update_popup_settings(): void
    {
        $payload = [
            'popup_enabled' => '1',
            'popup_title' => 'Diskon Kilat 30% Spesial Weekend',
            'popup_badge' => 'FLASH SALE',
            'popup_content' => 'Gunakan kode promo KOPI30 saat checkout untuk potongan harga langsung!',
            'popup_cta_text' => 'Beli Sekarang',
            'popup_cta_url' => 'https://wa.me/6281234567890',
            'popup_frequency' => 'once_per_day',
            'popup_starts_at' => now()->subMinute()->format('Y-m-d\TH:i'),
            'popup_ends_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($this->owner)
            ->put(route('landing-page.popup.update'), $payload);

        $response->assertRedirect(route('landing-page.popup.edit'));
        $response->assertSessionHas('success');

        $this->landingPage->refresh();
        $this->assertTrue($this->landingPage->popup_enabled);
        $this->assertSame('Diskon Kilat 30% Spesial Weekend', $this->landingPage->popup_title);
        $this->assertSame('FLASH SALE', $this->landingPage->popup_badge);
        $this->assertSame('once_per_day', $this->landingPage->popup_frequency);
        $this->assertTrue($this->landingPage->isPopupActive());
    }

    public function test_owner_can_upload_and_remove_popup_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('promo_banner.jpg', 600, 400);

        $payload = [
            'popup_enabled' => '1',
            'popup_title' => 'Promo Dengan Foto Banner',
            'popup_frequency' => 'once_per_session',
            'popup_image' => $image,
        ];

        $response = $this->actingAs($this->owner)
            ->put(route('landing-page.popup.update'), $payload);

        $response->assertRedirect(route('landing-page.popup.edit'));
        $this->landingPage->refresh();

        $this->assertNotNull($this->landingPage->popup_image_path);
        $this->assertNotEmpty($this->landingPage->popup_image_url);

        // Now remove the uploaded image
        $removePayload = [
            'popup_enabled' => '1',
            'popup_title' => 'Promo Tanpa Banner',
            'popup_frequency' => 'once_per_session',
            'remove_popup_image' => '1',
        ];

        $removeResponse = $this->actingAs($this->owner)
            ->put(route('landing-page.popup.update'), $removePayload);

        $removeResponse->assertRedirect(route('landing-page.popup.edit'));
        $this->landingPage->refresh();
        $this->assertNull($this->landingPage->popup_image_path);
    }

    public function test_unauthorized_user_without_permission_is_denied(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cashier.popup@cooca.id',
            'email_verified_at' => now(),
        ]);

        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();

        $this->business->users()->attach($cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);
        $cashier->update(['active_business_id' => $this->business->id]);

        Context::flush();

        // Cashier visits GET /landing-page/popup -> should be redirected to dashboard
        $response = $this->actingAs($cashier)->get(route('landing-page.popup.edit'));
        $response->assertRedirect(route('dashboard'));

        // Cashier attempts PUT /landing-page/popup -> should also be redirected
        $putResponse = $this->actingAs($cashier)->put(route('landing-page.popup.update'), [
            'popup_enabled' => '1',
            'popup_frequency' => 'always',
        ]);
        $putResponse->assertRedirect(route('dashboard'));
    }

    public function test_public_storefront_home_renders_popup_when_active(): void
    {
        $this->landingPage->update([
            'popup_enabled' => true,
            'popup_badge' => 'SPECIAL DISCOUNT',
            'popup_title' => 'Selamat Datang di Kedai Kopi Kami!',
            'popup_content' => 'Nikmati diskon 20% untuk semua menu manual brew hari ini.',
            'popup_cta_text' => 'Pesan Sekarang',
            'popup_cta_url' => 'https://wa.me/6281234567890',
            'popup_frequency' => 'once_per_day',
            'popup_starts_at' => null,
            'popup_ends_at' => null,
        ]);

        $response = $this->get('/' . $this->business->slug);

        $response->assertOk();
        $response->assertSee('storefrontPopupModal');
        $response->assertSee('SPECIAL DISCOUNT');
        $response->assertSee('Selamat Datang di Kedai Kopi Kami!');
        $response->assertSee('Nikmati diskon 20% untuk semua menu manual brew hari ini.');
        $response->assertSee('Pesan Sekarang');
    }

    public function test_public_storefront_home_hides_popup_when_disabled_or_expired(): void
    {
        // 1. When disabled
        $this->landingPage->update([
            'popup_enabled' => false,
            'popup_title' => 'Promo Rahasia yang Belum Terbit',
        ]);

        $responseDisabled = $this->get('/' . $this->business->slug);
        $responseDisabled->assertOk();
        $responseDisabled->assertDontSee('storefrontPopupModal');
        $responseDisabled->assertDontSee('Promo Rahasia yang Belum Terbit');

        // 2. When expired
        $this->landingPage->update([
            'popup_enabled' => true,
            'popup_title' => 'Promo Kadaluarsa Kemarin',
            'popup_ends_at' => now()->subHour(),
        ]);

        $responseExpired = $this->get('/' . $this->business->slug);
        $responseExpired->assertOk();
        $responseExpired->assertDontSee('storefrontPopupModal');
        $responseExpired->assertDontSee('Promo Kadaluarsa Kemarin');
    }
}
