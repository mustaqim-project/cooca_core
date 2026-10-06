<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessTypeTemplate;
use App\Models\Currency;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SettingWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);
        app()->setLocale('id');
    }

    private function createUser(array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Setting Test User',
            'email' => 'user_' . Str::random(8) . '@test.local',
            'phone' => '628' . rand(100000000, 999999999),
            'password' => 'password',
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createBusinessWithOwner(array $businessAttributes = []): array
    {
        $user = $this->createUser();
        $ownerRole = Role::where('name', 'owner')->first();

        $business = Business::create(array_merge([
            'name' => 'Toko Mitra Berkah',
            'currency' => 'IDR',
            'currency_precision' => 0,
            'rounding_strategy' => Business::ROUNDING_ROUND_100,
            'timezone' => 'Asia/Jakarta',
            'disabled_modules' => [],
            'pos_supervisor_pin' => Hash::make('1234'),
        ], $businessAttributes));

        $membership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business, $membership);

        return [$business, $user, $membership];
    }

    public function test_settings_index_loads_successfully_for_owner(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Currency::firstOrCreate(
            ['code' => 'IDR'],
            ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'precision' => 0]
        );

        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee(__('settings.header_title'));
        $response->assertSee(__('settings.tab_general'));
        $response->assertSee(__('settings.tab_operations'));
        $response->assertSee(__('settings.tab_templates'));
        $response->assertSee(__('settings.tab_modules'));

        // Assert critical keys are translated and never leak literal string
        $response->assertDontSee('settings.business_name', false);
        $response->assertDontSee('settings.tax_id', false);
        $response->assertSee(__('settings.business_name'), false);
        $response->assertSee(__('settings.tax_id'), false);
    }

    public function test_settings_dual_language_parity_and_zero_unrendered_keys(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Currency::firstOrCreate(
            ['code' => 'IDR'],
            ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'precision' => 0]
        );

        // 1. Test Indonesian Locale
        app()->setLocale('id');
        $idResponse = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->get(route('settings.index'));

        $idResponse->assertOk();
        $idResponse->assertSee('Nama Usaha / Perusahaan', false);
        $idResponse->assertSee('NPWP / Identitas Pajak Usaha', false);
        $idResponse->assertDontSee('settings.business_name', false);
        $idResponse->assertDontSee('settings.tax_id', false);

        // 2. Test English Locale
        app()->setLocale('en');
        $enResponse = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->get(route('settings.index'));

        $enResponse->assertOk();
        $enResponse->assertSee('Business / Company Name', false);
        $enResponse->assertSee('Tax ID / NPWP', false);
        $enResponse->assertDontSee('settings.business_name', false);
        $enResponse->assertDontSee('settings.tax_id', false);
    }

    public function test_unauthorized_user_without_owner_role_cannot_apply_industry_template(): void
    {
        [$business] = $this->createBusinessWithOwner();

        // Create a cashier/staff user
        $cashierUser = $this->createUser(['name' => 'Cashier User']);
        $cashierRole = Role::where('name', 'cashier')->first();

        $cashierMembership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id' => $cashierUser->id,
            'role' => 'cashier',
            'role_id' => $cashierRole?->id,
        ]);

        $cashierUser->update(['active_business_id' => $business->id]);
        Context::setBusiness($business, $cashierMembership);

        $template = BusinessTypeTemplate::firstOrFail();

        // 1. JSON / AJAX request receives HTTP 403 Forbidden
        $jsonResponse = $this->actingAs($cashierUser, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $cashierUser->id,
            ])
            ->postJson(route('settings.apply-template'), [
                'template_code' => $template->code,
            ]);

        $jsonResponse->assertForbidden();

        // 2. Standard Web request is redirected to portal with error flash message
        $webResponse = $this->actingAs($cashierUser, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $cashierUser->id,
            ])
            ->post(route('settings.apply-template'), [
                'template_code' => $template->code,
            ]);

        $webResponse->assertRedirect(route('portal'));
    }

    public function test_owner_can_apply_industry_template_and_creates_audit_log(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $template = BusinessTypeTemplate::where('code', 'service_agency')->firstOrFail();

        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->post(route('settings.apply-template'), [
                'template_code' => $template->code,
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'templates']));

        $business->refresh();
        $this->assertEquals('service_agency', $business->template_code);
        $this->assertEquals($template->industry_category, $business->industry_category);

        // Verify Audit Log was created
        $auditLog = AuditLog::where('business_id', $business->id)
            ->where('action', 'settings.template_applied')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals(AuditLog::RISK_MEDIUM, $auditLog->risk_level);
        $this->assertEquals($template->code, $auditLog->new_values['template_code']);
    }

    public function test_updating_bank_account_creates_immutable_audit_log_record(): void
    {
        [$business, $user] = $this->createBusinessWithOwner([
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT Mitra Lama',
        ]);

        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->put(route('settings.update'), [
                'name' => $business->name,
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'timezone' => 'Asia/Jakarta',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '9876543210',
                'bank_account_holder' => 'PT Mitra Berkah Baru',
                '_tab' => 'general',
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'general']));

        $business->refresh();
        $this->assertEquals('Bank Mandiri', $business->bank_name);
        $this->assertEquals('9876543210', $business->bank_account_number);

        // Verify Audit Log captured old vs new values diff
        $auditLog = AuditLog::where('business_id', $business->id)
            ->where('action', 'settings.security_updated')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals(AuditLog::RISK_HIGH, $auditLog->risk_level);
        $this->assertEquals('1234567890', $auditLog->old_values['bank_account_number']);
        $this->assertEquals('9876543210', $auditLog->new_values['bank_account_number']);
    }

    public function test_supervisor_pin_requires_digits_between_4_and_8(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        // 1. Validation fails with invalid PIN (alphanumeric or too short)
        $responseInvalid = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->put(route('settings.update'), [
                'name' => $business->name,
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'timezone' => 'Asia/Jakarta',
                'pos_supervisor_pin' => 'abc', // Invalid
                '_tab' => 'general',
            ]);

        $responseInvalid->assertSessionHasErrors(['pos_supervisor_pin']);

        // 2. Validation passes with valid 6-digit PIN and masks in audit log
        $responseValid = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->put(route('settings.update'), [
                'name' => $business->name,
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'timezone' => 'Asia/Jakarta',
                'pos_supervisor_pin' => '998877',
                '_tab' => 'general',
            ]);

        $responseValid->assertSessionHasNoErrors();
        $business->refresh();
        $this->assertTrue(Hash::check('998877', $business->pos_supervisor_pin));

        // Ensure masked in AuditLog
        $auditLog = AuditLog::where('business_id', $business->id)
            ->where('action', 'settings.security_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('••••••', $auditLog->new_values['pos_supervisor_pin']);
    }

    public function test_pos_and_wa_receipt_sections_hidden_when_pos_modules_disabled(): void
    {
        // Create business with POS modules disabled
        [$business, $user] = $this->createBusinessWithOwner([
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_RETAIL,
                ModuleRegistry::MODULE_POS_DINEIN,
            ],
        ]);

        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->get(route('settings.index'));

        $response->assertOk();
        // POS Cashier Discount section should be hidden
        $response->assertDontSee(__('settings.pos_discount_title'));
        // Supervisor PIN section should be hidden
        $response->assertDontSee(__('settings.supervisor_pin_title'));
        // WA Receipt Tab is hidden from active tabs
        $this->assertFalse($business->isModuleEnabled('pos_retail'));
        $this->assertFalse($business->isModuleEnabled('pos_dinein'));
    }

    public function test_wa_receipt_tab_renders_waba_architecture_and_dual_mode_preview(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Currency::firstOrCreate(
            ['code' => 'IDR'],
            ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'precision' => 0]
        );

        $response = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
                'locale' => 'id',
            ])
            ->get(route('settings.index', ['tab' => 'wa_receipt']));

        $response->assertOk();
        $response->assertSee(__('settings.waba_channel_architecture_title'), false);
        $response->assertSee(__('settings.waba_engine_badge'), false);
        $response->assertSee('cooca_pos_receipt', false);
        $response->assertSee(e(__('settings.receipt_footer_section_title')), false);
        $response->assertSee(__('settings.wa_manual_section_title'), false);
        $response->assertSee(__('settings.preview_mode_waba'), false);
        $response->assertSee(__('settings.preview_mode_manual'), false);
        $response->assertSee(e(__('settings.waba_header_text')), false);
        $response->assertSee(e(__('settings.waba_receipt_paper_title')), false);
        $response->assertSee('https://cooca.id/r/POS-20260910-0042', false);

        // Test updating receipt footer note and manual template
        $updateResponse = $this->actingAs($user, 'web')
            ->withSession([
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $user->id,
            ])
            ->put(route('settings.update'), [
                '_tab' => 'wa_receipt',
                'name' => $business->name,
                'pos_receipt_footer_note' => 'Barang yang sudah dibeli tidak dapat ditukar.',
                'pos_receipt_wa_template' => 'Halo {customer_name}, terima kasih telah berbelanja di {business_name}!',
            ]);

        $updateResponse->assertSessionHasNoErrors();
        $business->refresh();
        $this->assertSame('Barang yang sudah dibeli tidak dapat ditukar.', $business->pos_receipt_footer_note);
        $this->assertSame('Halo {customer_name}, terima kasih telah berbelanja di {business_name}!', $business->pos_receipt_wa_template);
    }
}
