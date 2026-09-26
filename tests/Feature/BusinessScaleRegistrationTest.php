<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BusinessScaleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
    }

    public function test_registration_view_renders_bento_segment_selector(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('Skala &amp; Model Operasional Bisnis', false);
        $response->assertSee('UMKM &amp; Toko Mandiri', false);
        $response->assertSee('Korporasi &amp; Multi-Cabang', false);
        $response->assertSee('value="umkm"', false);
        $response->assertSee('value="corporate"', false);
    }

    public function test_registration_as_umkm_persists_scale_and_disables_corporate_modules(): void
    {
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Pak Joko Warung',
                'email' => 'joko@warungberkah.test',
                'phone' => '6281299990001',
                'password' => Hash::make('rahasia123'),
                'business_name' => 'Warung Berkah Nusantara',
                'business_scale' => Business::SCALE_UMKM,
                'template_code' => null,
                'otp_hash' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->post(route('register.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        /** @var Business $business */
        $business = Business::where('name', 'Warung Berkah Nusantara')->firstOrFail();
        $this->assertSame(Business::SCALE_UMKM, $business->business_scale);
        $this->assertTrue($business->isUmkm());
        $this->assertFalse($business->isCorporate());

        // Corporate modules must be disabled for UMKM
        $disabled = $business->disabled_modules ?? [];
        $this->assertContains(ModuleRegistry::MODULE_B2B_SALES, $disabled);
        $this->assertContains(ModuleRegistry::MODULE_LABOR_MACHINES, $disabled);
        $this->assertContains(ModuleRegistry::MODULE_INVENTORY_WAREHOUSE, $disabled);
        $this->assertContains(ModuleRegistry::MODULE_CUSTOMER_PO, $disabled);
        $this->assertContains(ModuleRegistry::MODULE_ACCOUNTING_CORPORATE, $disabled);
    }

    public function test_registration_as_corporate_persists_scale_and_keeps_corporate_modules_active(): void
    {
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Direktur Utama',
                'email' => 'dirut@holdingcorp.test',
                'phone' => '6281299990002',
                'password' => Hash::make('rahasia123'),
                'business_name' => 'PT Holding Multi Jaya',
                'business_scale' => Business::SCALE_CORPORATE,
                'template_code' => null,
                'otp_hash' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->post(route('register.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        /** @var Business $business */
        $business = Business::where('name', 'PT Holding Multi Jaya')->firstOrFail();
        $this->assertSame(Business::SCALE_CORPORATE, $business->business_scale);
        $this->assertTrue($business->isCorporate());
        $this->assertFalse($business->isUmkm());

        // Corporate modules must NOT be disabled when registering as corporate without template
        $disabled = $business->disabled_modules ?? [];
        $this->assertNotContains(ModuleRegistry::MODULE_B2B_SALES, $disabled);
        $this->assertNotContains(ModuleRegistry::MODULE_LABOR_MACHINES, $disabled);
        $this->assertNotContains(ModuleRegistry::MODULE_INVENTORY_WAREHOUSE, $disabled);
        $this->assertNotContains(ModuleRegistry::MODULE_CUSTOMER_PO, $disabled);
        $this->assertNotContains(ModuleRegistry::MODULE_ACCOUNTING_CORPORATE, $disabled);
    }

    public function test_registration_defaults_to_umkm_when_business_scale_is_not_provided(): void
    {
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Pemilik Toko',
                'email' => 'toko@kelontong.test',
                'phone' => '6281299990003',
                'password' => Hash::make('rahasia123'),
                'business_name' => 'Toko Kelontong Sentosa',
                'business_scale' => null, // Omitted
                'template_code' => null,
                'otp_hash' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->post(route('register.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('dashboard'));

        /** @var Business $business */
        $business = Business::where('name', 'Toko Kelontong Sentosa')->firstOrFail();
        $this->assertSame(Business::SCALE_UMKM, $business->business_scale);
        $this->assertTrue($business->isUmkm());
    }

    public function test_google_registration_view_and_submission_persists_business_scale(): void
    {
        $this->withSession([
            'pending_google_registration' => [
                'name' => 'Google Merchant',
                'email' => 'merchant@gmail.com',
                'google_id' => 'goog-12345678',
                'avatar' => null,
            ],
        ])->get(route('register.google'))
            ->assertOk()
            ->assertSee('Skala &amp; Model Operasional Bisnis', false)
            ->assertSee('UMKM &amp; Toko Mandiri', false)
            ->assertSee('Korporasi &amp; Multi-Cabang', false);
    }
}
