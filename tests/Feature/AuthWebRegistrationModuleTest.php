<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Business;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthWebRegistrationModuleTest extends TestCase
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

    public function test_register_page_renders_apple_hig_bento_grid_and_module_toggles(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSeeText('Daftarkan Bisnis Anda');
        $response->assertSeeText('Penataan Modul & Fitur Bisnis');
        $response->assertSeeText('Kasir POS & Struk Cepat');
        $response->assertSeeText('Pengadaan PO & Hutang Supplier');
        $response->assertSeeText('Aktifkan Semua');
        $response->assertSeeText('Reset Preset');
        $response->assertSee('enabled_modules[]', false);
        $response->assertSee('has_module_selection', false);
    }

    public function test_registration_with_custom_module_selection_saves_to_session(): void
    {
        $this->mock(AdminWhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendOtp')->andReturn([
                'success' => true,
            ]);
        });

        $chosenModules = [
            ModuleRegistry::MODULE_POS_RETAIL,
            ModuleRegistry::MODULE_CRM_LOYALTY,
            ModuleRegistry::MODULE_PROCUREMENT,
        ];

        $response = $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@tokoberkah.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'business_name' => 'Toko Berkah Kelontong',
            'business_scale' => 'umkm',
            'phone' => '081298765432',
            'template_code' => 'retail_reseller',
            'has_module_selection' => '1',
            'enabled_modules' => $chosenModules,
        ]);

        $response->assertRedirect(route('register.verify'));
        $response->assertSessionHas('pending_registration');

        $pending = session('pending_registration');
        $this->assertEquals('Budi Santoso', $pending['name']);
        $this->assertEquals('budi.santoso@tokoberkah.com', $pending['email']);
        $this->assertEquals('Toko Berkah Kelontong', $pending['business_name']);
        $this->assertEquals('retail_reseller', $pending['template_code']);
        $this->assertTrue($pending['custom_modules']);

        // Check disabled modules
        $allKeys = array_keys(ModuleRegistry::definitions());
        $expectedDisabled = array_values(array_diff($allKeys, $chosenModules));
        $this->assertEquals($expectedDisabled, $pending['disabled_modules']);
    }

    public function test_verifying_registration_otp_persists_custom_disabled_modules_to_business(): void
    {
        $allKeys = array_keys(ModuleRegistry::definitions());
        $enabled = [
            ModuleRegistry::MODULE_POS_RETAIL,
            ModuleRegistry::MODULE_CRM_LOYALTY,
        ];
        $disabled = array_values(array_diff($allKeys, $enabled));

        $otp = '654321';
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@tokoberkah.com',
                'phone' => '6281298765432',
                'business_name' => 'Toko Berkah Kelontong',
                'business_scale' => 'umkm',
                'template_code' => 'retail_reseller',
                'disabled_modules' => $disabled,
                'custom_modules' => true,
                'password' => Hash::make('rahasia123'),
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->post(route('register.verify.submit'), [
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $business = Business::where('name', 'Toko Berkah Kelontong')->firstOrFail();
        $this->assertEquals('Toko Berkah Kelontong', $business->name);
        $this->assertEquals('retail_reseller', $business->template_code);
        $this->assertEquals($disabled, $business->disabled_modules);

        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_POS_RETAIL));
        $this->assertTrue($business->isModuleEnabled(ModuleRegistry::MODULE_CRM_LOYALTY));
        $this->assertTrue($business->isModuleDisabled(ModuleRegistry::MODULE_POS_DINEIN));
        $this->assertTrue($business->isModuleDisabled(ModuleRegistry::MODULE_CUSTOMER_PO));
    }
}
