<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Crm\LoyaltyService;
use App\Models\AuditLog;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\FeatureRequest;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrmAndCustomerComprehensiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // Setup Tenant A
        $this->ownerA = User::create([
            'name' => 'Owner Tenant A',
            'email' => 'owner-a@cooca.test',
            'email_verified_at' => now(),
            'phone' => '081111111111',
            'phone_verified_at' => now(),
            'password' => 'password',
        ]);
        $this->businessA = Business::create(['name' => 'Bisnis Alpha Sejahtera', 'slug' => 'bisnis-alpha']);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Setup Tenant B
        $this->ownerB = User::create([
            'name' => 'Owner Tenant B',
            'email' => 'owner-b@cooca.test',
            'email_verified_at' => now(),
            'phone' => '082222222222',
            'phone_verified_at' => now(),
            'password' => 'password',
        ]);
        $this->businessB = Business::create(['name' => 'Bisnis Beta Jaya', 'slug' => 'bisnis-beta']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    public function test_customer_route_binding_sql_precedence_isolation(): void
    {
        // Customer in Tenant A
        $customerA = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Budi Alpha',
            'slug' => 'budi-customer',
            'phone' => '081234567891',
        ]);

        // Customer in Tenant B
        $customerB = Customer::create([
            'business_id' => $this->businessB->id,
            'name' => 'Budi Beta',
            'slug' => 'budi-customer',
            'phone' => '081234567892',
        ]);

        // Context set to Tenant A
        Context::setBusiness($this->businessA);

        // Binding by ID for Tenant A customer should resolve
        $resolvedA = (new Customer())->resolveRouteBinding($customerA->id);
        $this->assertNotNull($resolvedA);
        $this->assertEquals($customerA->id, $resolvedA->id);

        // Binding by Slug for Tenant A customer should resolve to Tenant A only
        $resolvedSlugA = (new Customer())->resolveRouteBinding('budi-customer');
        $this->assertNotNull($resolvedSlugA);
        $this->assertEquals($customerA->id, $resolvedSlugA->id);

        // Cross-Tenant attempt: Resolving Tenant B's ID while in Tenant A MUST return null
        $crossTenantResolve = (new Customer())->resolveRouteBinding($customerB->id);
        $this->assertNull($crossTenantResolve);
    }

    public function test_credit_repayment_records_cash_transaction_and_audit_log(): void
    {
        Context::setBusiness($this->businessA);

        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Hutang Pelanggan',
            'phone' => '081333333333',
            'current_credit_balance' => 500000,
        ]);

        $service = app(LoyaltyService::class);
        $result = $service->recordCustomerCreditPayment(
            $customer,
            200000,
            'Pelunasan kasbon via tunai kasir',
            $this->ownerA
        );

        // 1. Assert balance updated
        $customer->refresh();
        $this->assertEquals(300000, (float) $customer->current_credit_balance);
        $this->assertEquals(200000, (float) $result->amount);

        // 2. Assert CashTransaction created (Anti-Fraud auto-journaling)
        $this->assertDatabaseHas('cash_transactions', [
            'business_id' => $this->businessA->id,
            'type' => CashTransaction::TYPE_IN,
            'amount' => 200000,
            'reference_type' => 'customer_credit_repayment',
        ]);

        // 3. Assert AuditLog created
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'customer.credit_payment_recorded',
            'user_id' => $this->ownerA->id,
        ]);
    }

    public function test_voucher_code_uniqueness_per_business(): void
    {
        Context::setBusiness($this->businessA);

        // Tenant A creates voucher DISKON10
        $response1 = $this->actingAs($this->ownerA)->post(route('crm.vouchers.store'), [
            'code' => 'DISKON10',
            'name' => 'Diskon 10 Persen',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $response1->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'business_id' => $this->businessA->id,
            'code' => 'DISKON10',
        ]);

        // Tenant A tries to create duplicate DISKON10 -> should fail validation
        $responseDuplicate = $this->actingAs($this->ownerA)->post(route('crm.vouchers.store'), [
            'code' => 'DISKON10',
            'name' => 'Diskon 10 Persen Duplikat',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $responseDuplicate->assertSessionHasErrors('code');

        // Tenant B creates DISKON10 -> should succeed because it's a different business
        Context::flush();
        Context::setBusiness($this->businessB);
        $responseTenantB = $this->actingAs($this->ownerB)->post(route('crm.vouchers.store'), [
            'code' => 'DISKON10',
            'name' => 'Diskon 10 Persen Tenant B',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $responseTenantB->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'business_id' => $this->businessB->id,
            'code' => 'DISKON10',
        ]);
    }

    public function test_navigation_registry_registers_feedback_submodules(): void
    {
        $allModules = NavigationRegistry::all();

        $this->assertArrayHasKey('feedback', $allModules);
        $feedbackModule = $allModules['feedback'];
        $this->assertArrayHasKey('tabs', $feedbackModule);

        $tabKeys = array_column($feedbackModule['tabs'], 'key');
        $this->assertContains('bugs', $tabKeys);
        $this->assertContains('features', $tabKeys);
    }

    public function test_feedback_bug_and_feature_workflow(): void
    {
        Storage::fake('public');
        Context::setBusiness($this->businessA);

        $fakeFile = UploadedFile::fake()->create('error-screenshot.png', 500, 'image/png');

        // 1. Submit Bug Report with attachment
        $bugResponse = $this->actingAs($this->ownerA)->post(route('feedback.bugs.store'), [
            'title' => 'Tombol Cetak Struk Macet',
            'category' => 'bug',
            'severity' => 'high',
            'description' => 'Ketika menekan tombol cetak struk di kasir POS, dialog print tidak muncul.',
            'steps_to_reproduce' => '1. Buka kasir\n2. Checkout transaksi\n3. Tekan cetak',
            'expected_behavior' => 'Struk langsung tercetak di printer thermal',
            'actual_behavior' => 'Tidak ada respon',
            'environment' => 'Chrome 126 Windows 11',
            'attachment' => $fakeFile,
        ]);

        $bugResponse->assertRedirect();
        $bug = BugReport::where('business_id', $this->businessA->id)->firstOrFail();
        $this->assertEquals('Tombol Cetak Struk Macet', $bug->title);
        $this->assertNotNull($bug->attachment_path);

        // 2. View Bug Show page
        $showBugResponse = $this->actingAs($this->ownerA)->get(route('feedback.bugs.show', $bug));
        $showBugResponse->assertOk();
        $showBugResponse->assertSee('Tombol Cetak Struk Macet');
        $showBugResponse->assertSee(__('feedback.progress_work'));

        // 3. Submit Feature Request
        $featResponse = $this->actingAs($this->ownerA)->post(route('feedback.features.store'), [
            'title' => 'Integrasi Barcode Scanner Bluetooth',
            'category' => 'reporting',
            'priority' => 'high',
            'description' => 'Dukungan scan barcode nirkabel via Bluetooth di Android POS.',
            'business_value' => 'Mempercepat antrian kasir hingga 40%.',
            'use_case' => 'Kasir men-scan barang langsung dari troli pembeli.',
        ]);

        $featResponse->assertRedirect();
        $feature = FeatureRequest::where('business_id', $this->businessA->id)->firstOrFail();
        $this->assertEquals('Integrasi Barcode Scanner Bluetooth', $feature->title);

        // 4. View Feature Show page
        $showFeatResponse = $this->actingAs($this->ownerA)->get(route('feedback.features.show', $feature));
        $showFeatResponse->assertOk();
        $showFeatResponse->assertSee('Integrasi Barcode Scanner Bluetooth');
        $showFeatResponse->assertSee('Mempercepat antrian kasir hingga 40%');
    }

    public function test_i18n_dictionaries_integrity(): void
    {
        $idCustomers = include base_path('lang/id/customers.php');
        $enCustomers = include base_path('lang/en/customers.php');
        $this->assertIsArray($idCustomers);
        $this->assertIsArray($enCustomers);
        $this->assertEquals([], array_diff_key($idCustomers, $enCustomers), 'Missing keys in lang/en/customers.php');
        $this->assertEquals([], array_diff_key($enCustomers, $idCustomers), 'Missing keys in lang/id/customers.php');

        $idCrm = include base_path('lang/id/crm.php');
        $enCrm = include base_path('lang/en/crm.php');
        $this->assertIsArray($idCrm);
        $this->assertIsArray($enCrm);
        $this->assertEquals([], array_diff_key($idCrm, $enCrm), 'Missing keys in lang/en/crm.php');
        $this->assertEquals([], array_diff_key($enCrm, $idCrm), 'Missing keys in lang/id/crm.php');

        $idFeedback = include base_path('lang/id/feedback.php');
        $enFeedback = include base_path('lang/en/feedback.php');
        $this->assertIsArray($idFeedback);
        $this->assertIsArray($enFeedback);
        $this->assertEquals([], array_diff_key($idFeedback, $enFeedback), 'Missing keys in lang/en/feedback.php');
        $this->assertEquals([], array_diff_key($enFeedback, $idFeedback), 'Missing keys in lang/id/feedback.php');
    }

    public function test_customers_and_crm_bilingual_locale_rendering(): void
    {
        Context::setBusiness($this->businessA);

        // 1. Indonesian Locale (Default)
        app()->setLocale('id');
        $resCustId = $this->actingAs($this->ownerA)->withSession(['locale' => 'id'])->get(route('customers.index'));
        $resCustId->assertOk();
        $resCustId->assertSee('Buku Pelanggan');
        $resCustId->assertSee('Tambah Pelanggan');

        $resCrmId = $this->actingAs($this->ownerA)->withSession(['locale' => 'id'])->get(route('crm.members.index'));
        $resCrmId->assertOk();
        $resCrmId->assertSee('Member & Loyalitas Poin');
        $resCrmId->assertSee('Total Member Terdaftar');

        $resFeedId = $this->actingAs($this->ownerA)->withSession(['locale' => 'id'])->get(route('feedback.bugs.index'));
        $resFeedId->assertOk();
        $resFeedId->assertSee('Laporan Kendala & Bug');

        // 2. English Locale
        app()->setLocale('en');
        $resCustEn = $this->actingAs($this->ownerA)->withSession(['locale' => 'en'])->get(route('customers.index'));
        $resCustEn->assertOk();
        $resCustEn->assertSee('Customer Directory');
        $resCustEn->assertSee('Add Customer');

        $resCrmEn = $this->actingAs($this->ownerA)->withSession(['locale' => 'en'])->get(route('crm.members.index'));
        $resCrmEn->assertOk();
        $resCrmEn->assertSee('Member & Loyalty Points');
        $resCrmEn->assertSee('Total Registered Members');

        $resFeedEn = $this->actingAs($this->ownerA)->withSession(['locale' => 'en'])->get(route('feedback.bugs.index'));
        $resFeedEn->assertOk();
        $resFeedEn->assertSee('Bug Reports & Issues');

        // Reset back to id
        app()->setLocale('id');
    }

    public function test_crm_controller_tenant_guardrail_assertions(): void
    {
        Context::setBusiness($this->businessA);

        $customerB = Customer::create([
            'business_id' => $this->businessB->id,
            'name' => 'Customer Milik Tenant B',
            'phone' => '089999999991',
            'current_credit_balance' => 500000,
        ]);

        $voucherB = Voucher::create([
            'business_id' => $this->businessB->id,
            'code' => 'VOUCHERB',
            'name' => 'Voucher Tenant B',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'is_active' => true,
        ]);

        // 1. Tenant A calls pointHistories on Tenant B's customer -> Must be blocked (403/404)
        $resPoint = $this->actingAs($this->ownerA)->getJson(route('crm.customers.points', $customerB));
        $this->assertTrue(in_array($resPoint->status(), [403, 404], true));

        // 2. Tenant A calls recordCreditPayment on Tenant B's customer -> Must be blocked (403/404)
        $resPayment = $this->actingAs($this->ownerA)->post(route('crm.customers.credit-payment', $customerB), [
            'amount' => 100000,
            'notes' => 'Coba bayar hutang tenant lain',
        ]);
        $this->assertTrue(in_array($resPayment->status(), [403, 404], true));

        // 3. Tenant A calls toggleVoucher on Tenant B's voucher -> Must be blocked (403/404)
        $resToggle = $this->actingAs($this->ownerA)->post(route('crm.vouchers.toggle', $voucherB));
        $this->assertTrue(in_array($resToggle->status(), [403, 404], true));
    }

    public function test_customer_code_uniqueness_scoped_to_business(): void
    {
        Context::setBusiness($this->businessA);

        // 1. Create customer in Tenant A with code CUST-ALPHA
        $res1 = $this->actingAs($this->ownerA)->post(route('customers.store'), [
            'name' => 'Customer 1 Alpha',
            'code' => 'CUST-ALPHA',
            'phone' => '081234500001',
        ]);
        $res1->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->businessA->id,
            'code' => 'CUST-ALPHA',
        ]);

        // 2. Tenant A cannot create duplicate code CUST-ALPHA
        $resDuplicate = $this->actingAs($this->ownerA)->post(route('customers.store'), [
            'name' => 'Customer 2 Alpha Duplikat',
            'code' => 'CUST-ALPHA',
            'phone' => '081234500002',
        ]);
        $resDuplicate->assertSessionHasErrors('code');

        // 3. Tenant B CAN create customer with same code CUST-ALPHA
        Context::flush();
        Context::setBusiness($this->businessB);
        $resTenantB = $this->actingAs($this->ownerB)->post(route('customers.store'), [
            'name' => 'Customer 1 Beta Same Code',
            'code' => 'CUST-ALPHA',
            'phone' => '081234500003',
        ]);
        $resTenantB->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->businessB->id,
            'code' => 'CUST-ALPHA',
        ]);
    }

    public function test_credit_payment_overpayment_is_strictly_rejected_by_controller_and_service(): void
    {
        Context::setBusiness($this->businessA);

        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Debitur Alpha',
            'phone' => '081234567899',
            'current_credit_balance' => 300000,
        ]);

        // 1. Controller validation rejects payment amount > current_credit_balance (HTTP 422 for JSON)
        $resOverpayment = $this->actingAs($this->ownerA)->postJson(route('crm.customers.credit-payment', $customer), [
            'amount' => 350000,
            'notes' => 'Mencoba overpayment melebihi sisa piutang',
        ]);
        $resOverpayment->assertStatus(422);
        $resOverpayment->assertJsonValidationErrors('amount');

        // 2. Service level throws InvalidArgumentException if called directly with overpayment
        $service = app(LoyaltyService::class);
        $this->expectException(\InvalidArgumentException::class);
        $service->recordCustomerCreditPayment($customer, 500000);
    }

    public function test_credit_payment_returns_whatsapp_receipt_url_for_anti_lapping_shield(): void
    {
        Context::setBusiness($this->businessA);

        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Debitur Resik',
            'phone' => '081987654321',
            'current_credit_balance' => 400000,
        ]);

        // Valid partial payment
        $response = $this->actingAs($this->ownerA)->postJson(route('crm.customers.credit-payment', $customer), [
            'amount' => 150000,
            'notes' => 'Cicilan 1 Kasir',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Pembayaran piutang berhasil dicatat.',
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('wa_receipt_url', $responseData);
        $waUrl = $responseData['wa_receipt_url'];

        // Assert WhatsApp URL format and content
        $this->assertStringStartsWith('https://wa.me/6281987654321?text=', $waUrl);
        $decodedText = rawurldecode(substr($waUrl, strpos($waUrl, '?text=') + 6));
        $this->assertStringContainsString('BUKTI PEMBAYARAN PIUTANG', $decodedText);
        $this->assertStringContainsString('Bisnis Alpha Sejahtera', $decodedText);
        $this->assertStringContainsString('Debitur Resik', $decodedText);
        $this->assertStringContainsString('150.000', $decodedText);
        $this->assertStringContainsString('250.000', $decodedText);

        // Verify balance after is 250,000
        $customer->refresh();
        $this->assertEquals(250000, (float) $customer->current_credit_balance);
    }

    public function test_customer_index_deep_links_tab_redirects(): void
    {
        Context::setBusiness($this->businessA);

        // 1. Deep-linking ?tab=members redirects to canonical crm.members.index
        $resMembers = $this->actingAs($this->ownerA)->get(route('customers.index', ['tab' => 'members']));
        $resMembers->assertRedirect(route('crm.members.index'));

        // 2. Deep-linking ?tab=vouchers redirects to canonical crm.vouchers.index
        $resVouchers = $this->actingAs($this->ownerA)->get(route('customers.index', ['tab' => 'vouchers']));
        $resVouchers->assertRedirect(route('crm.vouchers.index'));

        // 3. Regular customer directory request returns 200 OK without redirect
        $resCustomers = $this->actingAs($this->ownerA)->get(route('customers.index'));
        $resCustomers->assertOk();
        $resCustomers->assertViewIs('app.customers.index');
        $resCustomers->assertSee('Direktori Klien &amp; Pelanggan', false);
    }

    public function test_crm_submodule_pages_render_cleanly_with_canonical_tabs(): void
    {
        Context::setBusiness($this->businessA);

        // Member CRM page
        $resMembers = $this->actingAs($this->ownerA)->get(route('crm.members.index'));
        $resMembers->assertOk();
        $resMembers->assertViewIs('app.crm.members');
        $resMembers->assertSee('CRM &amp; Membership Pelanggan', false);
        $resMembers->assertSee('Member &amp; Tingkatan', false);

        // Vouchers CRM page
        $resVouchers = $this->actingAs($this->ownerA)->get(route('crm.vouchers.index'));
        $resVouchers->assertOk();
        $resVouchers->assertViewIs('app.crm.vouchers');
        $resVouchers->assertSee('Voucher &amp; Kode Promo Kasir', false);
        $resVouchers->assertSee('Voucher Promo', false);
    }

    public function test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business(): void
    {
        $fnbUser = User::create([
            'name' => 'Fnb Owner',
            'email' => 'fnb-owner@cooca.test',
            'password' => 'password',
        ]);
        $fnbBusiness = Business::create([
            'name' => 'Kopi Senja Utama',
            'slug' => 'kopi-senja-utama',
            'template_code' => 'fnb_cafe',
            'disabled_modules' => ['b2b_sales'],
        ]);
        $fnbBusiness->users()->attach($fnbUser->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $fnbUser->update(['active_business_id' => $fnbBusiness->id]);
        Context::setBusiness($fnbBusiness);

        Customer::create([
            'business_id' => $fnbBusiness->id,
            'name' => 'Rina Melati',
            'phone' => '081234567800',
        ]);

        $response = $this->actingAs($fnbUser)->get(route('customers.index'));
        $response->assertOk();

        // B2B fields must be auto-hidden
        $response->assertDontSee('NPWP Perusahaan');
        $response->assertDontSee('Termin Tempo (Hari)');
        $response->assertDontSee('Termin Tempo');

        // Non-B2B metrics & customer shown
        $response->assertSee('Rina Melati');
        $response->assertSee('Kontak Aktif');
    }

    public function test_customer_ui_shows_and_persists_vehicle_fields_for_workshop_business(): void
    {
        $workshopUser = User::create([
            'name' => 'Workshop Owner',
            'email' => 'workshop-owner@cooca.test',
            'password' => 'password',
        ]);
        $workshopBusiness = Business::create([
            'name' => 'Bengkel Mobil Maju Jaya',
            'slug' => 'bengkel-maju-jaya',
            'template_code' => 'service_workshop',
        ]);
        $workshopBusiness->users()->attach($workshopUser->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $workshopUser->update(['active_business_id' => $workshopBusiness->id]);
        Context::setBusiness($workshopBusiness);

        // 1. UI renders workshop vehicle inputs
        $response = $this->actingAs($workshopUser)->get(route('customers.index'));
        $response->assertOk();
        $response->assertSee('Data Kendaraan Pelanggan (Sektor Bengkel &amp; Otomotif)', false);
        $response->assertSee('Nomor Polisi (Plat Nomor)');
        $response->assertSee('Merk &amp; Tipe Kendaraan', false);

        // 2. Storing customer with vehicle attributes persists cleanly
        $storeRes = $this->actingAs($workshopUser)->post(route('customers.store'), [
            'name' => 'Hendra Gunawan',
            'phone' => '081299887766',
            'vehicle_license_plate' => 'b 1234 xyz',
            'vehicle_model' => 'Toyota Avanza 1.3 G',
            'vehicle_mileage' => 45000,
        ]);
        $storeRes->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'business_id' => $workshopBusiness->id,
            'name' => 'Hendra Gunawan',
            'vehicle_license_plate' => 'B 1234 XYZ',
            'vehicle_model' => 'Toyota Avanza 1.3 G',
            'vehicle_mileage' => 45000,
        ]);

        // 3. UI renders vehicle plate badge in the table
        $indexAfterStore = $this->actingAs($workshopUser)->get(route('customers.index'));
        $indexAfterStore->assertOk();
        $indexAfterStore->assertSee('B 1234 XYZ');
        $indexAfterStore->assertSee('Toyota Avanza 1.3 G');

        // 4. Searching by vehicle license plate works
        $searchRes = $this->actingAs($workshopUser)->get(route('customers.index', ['search' => '1234 XYZ']));
        $searchRes->assertOk();
        $searchRes->assertSee('Hendra Gunawan');
        $searchRes->assertSee('B 1234 XYZ');
    }

    public function test_customers_and_crm_members_render_responsive_mobile_cards_with_thumb_zone_actions(): void
    {
        Context::setBusiness($this->businessA);

        Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'current_credit_balance' => 50000,
            'points_balance' => 120,
        ]);

        // 1. Customers Master Page: verify dual desktop table and mobile cards
        $resCust = $this->actingAs($this->ownerA)->get(route('customers.index'));
        $resCust->assertOk();
        $resCust->assertSee('hidden lg:block overflow-x-auto', false);
        $resCust->assertSee('block lg:hidden divide-y', false);
        $resCust->assertSee('Chat WA');
        $resCust->assertSee('Profil');
        $resCust->assertSee('openDetailModal', false);

        // 2. CRM Members Page: verify dual desktop table and mobile cards
        $resMem = $this->actingAs($this->ownerA)->get(route('crm.members.index'));
        $resMem->assertOk();
        $resMem->assertSee('hidden lg:block overflow-x-auto', false);
        $resMem->assertSee('block lg:hidden divide-y', false);
        $resMem->assertSee('Poin Belanja');
        $resMem->assertSee('Total Belanja');
        $resMem->assertSee('Riwayat Poin');
    }
}

