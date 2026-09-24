<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

final class GoogleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
    }

    public function test_google_registration_page_is_accessible_with_pending_session(): void
    {
        $response = $this->withSession([
            'pending_google_registration' => [
                'name' => 'Agung Mustaqim',
                'email' => 'agungmustaqim15@gmail.com',
                'google_id' => 'google-uid-123456',
                'avatar' => 'https://lh3.googleusercontent.com/a/test-avatar',
            ],
        ])->get(route('register.google'));

        $response->assertStatus(200);
        $response->assertSee('agungmustaqim15@gmail.com');
        $response->assertSeeText('Lengkapi Pendaftaran Bisnis');
        $response->assertSee('name="business_name"', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="template_code"', false);
        $response->assertSee('name="business_scale"', false);
        $response->assertDontSee('name="enabled_modules[]"', false);
    }

    public function test_google_registration_redirects_if_no_pending_session(): void
    {
        $response = $this->get(route('register.google'));
        $response->assertRedirect(route('register'));
    }

    public function test_google_registration_submission_records_customized_module_toggles(): void
    {
        $this->withoutExceptionHandling();
        $this->mock(AdminWhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendOtp')->andReturn([
                'success' => true,
            ]);
        });

        $selectedModules = [
            ModuleRegistry::MODULE_POS_RETAIL,
            ModuleRegistry::MODULE_CRM_LOYALTY,
            ModuleRegistry::MODULE_CHANNELS_MARKETING,
        ];

        $response = $this->withSession([
            'pending_google_registration' => [
                'name' => 'Agung Mustaqim',
                'email' => 'agungmustaqim15@gmail.com',
                'google_id' => 'google-uid-123456',
                'avatar' => 'https://lh3.googleusercontent.com/a/test-avatar',
            ],
        ])->post(route('register.google.submit'), [
            'business_name' => 'Toko Agung Digital',
            'business_scale' => 'umkm',
            'phone' => '081234567890',
            'template_code' => 'retail_reseller',
            'has_module_selection' => '1',
            'enabled_modules' => $selectedModules,
        ]);

        $response->assertRedirect(route('register.verify'));
        $response->assertSessionHas('pending_registration');

        $pending = session('pending_registration');
        $this->assertEquals('Toko Agung Digital', $pending['business_name']);
        $this->assertEquals('retail_reseller', $pending['template_code']);
        $this->assertTrue($pending['custom_modules']);

        // All module keys except the 3 chosen must be in disabled_modules
        $allKeys = array_keys(ModuleRegistry::definitions());
        $expectedDisabled = array_values(array_diff($allKeys, $selectedModules));
        $this->assertEquals($expectedDisabled, $pending['disabled_modules']);
    }

    public function test_custom_module_selection_is_persisted_upon_otp_verification(): void
    {
        $allKeys = array_keys(ModuleRegistry::definitions());
        $enabled = [
            ModuleRegistry::MODULE_POS_RETAIL,
            ModuleRegistry::MODULE_CHANNELS_MARKETING,
        ];
        $disabled = array_values(array_diff($allKeys, $enabled));

        $otp = '123456';
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Agung Mustaqim',
                'email' => 'agungmustaqim15@gmail.com',
                'google_id' => 'google-uid-123456',
                'phone' => '6281234567890',
                'business_name' => 'Warung Kopi Agung',
                'business_scale' => 'umkm',
                'template_code' => 'fnb_cafe',
                'disabled_modules' => $disabled,
                'custom_modules' => true,
                'password' => Hash::make('password123'),
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->post(route('register.verify.submit'), [
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('dashboard'));

        $business = Business::where('name', 'Warung Kopi Agung')->firstOrFail();
        $this->assertEquals('Warung Kopi Agung', $business->name);
        $this->assertEquals('fnb_cafe', $business->template_code);
        $this->assertEquals($disabled, $business->disabled_modules);
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_POS_RETAIL));
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_CHANNELS_MARKETING));
        $this->assertTrue($business->isModuleDisabled(ModuleRegistry::MODULE_POS_DINEIN));
    }
}
