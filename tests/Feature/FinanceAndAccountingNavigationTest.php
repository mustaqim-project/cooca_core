<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FinanceAndAccountingNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Finance Nav Test',
            'email'             => 'owner_finance_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Keuangan Nav Audit',
            'currency'         => 'IDR',
            'currency_code'    => 'IDR',
            'currency_symbol'  => 'Rp',
            'business_scale'   => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        (new AutoJournalService())->ensureStandardAccounts($this->business);

        CashAccount::create([
            'business_id'     => $this->business->id,
            'name'            => 'Kas Utama Toko',
            'account_number'  => 'CASH-001',
            'bank_name'       => 'Tunai',
            'current_balance' => 1000000,
            'is_active'       => true,
        ]);
    }

    // ==========================================
    // 1. FINANCE HUB TESTS (7 VIEWS)
    // ==========================================

    public function test_cash_bank_index_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.cash-bank.index'));

        $response->assertOk();
        $response->assertSee('Kas & Rekening Bank');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_cash_bank_ledger_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.cash-bank.ledger'));

        $response->assertOk();
        $response->assertSee('Buku Kas & Mutasi');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_expenses_index_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.expenses.index'));

        $response->assertOk();
        $response->assertSee('Beban Operasional');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_journals_index_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.journals.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Akuntansi');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_receivables_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.receivables'));

        $response->assertOk();
        $response->assertSee('Piutang Usaha (AR Aging)');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_payables_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.payables'));

        $response->assertOk();
        $response->assertSee('Hutang Usaha (AP Aging)');
        $this->assertFinanceTabsPresent($response);
    }

    public function test_settlements_index_renders_module_header_and_persistent_finance_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.settlements.index'));

        $response->assertOk();
        $response->assertSee('Rekonsiliasi &amp; Settlement Gateway', false);
        $this->assertFinanceTabsPresent($response);
    }

    // ==========================================
    // 2. ACCOUNTING HUB TESTS (5 VIEWS)
    // ==========================================

    public function test_coa_index_renders_module_header_and_persistent_accounting_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.coa.index'));

        $response->assertOk();
        $response->assertSee('Bagan Akun (Chart of Accounts)');
        $this->assertAccountingTabsPresent($response);
    }

    public function test_general_ledger_renders_module_header_and_persistent_accounting_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.general-ledger'));

        $response->assertOk();
        $response->assertSee('Buku Besar Umum (General Ledger)');
        $this->assertAccountingTabsPresent($response);
    }

    public function test_trial_balance_renders_module_header_and_persistent_accounting_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.trial-balance'));

        $response->assertOk();
        $response->assertSee('Neraca Saldo (Trial Balance)');
        $this->assertAccountingTabsPresent($response);
    }

    public function test_balance_sheet_renders_module_header_and_persistent_accounting_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.balance-sheet'));

        $response->assertOk();
        $response->assertSee('Laporan Posisi Keuangan (Neraca)');
        $this->assertAccountingTabsPresent($response);
    }

    public function test_reconciliations_index_renders_module_header_and_persistent_accounting_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.reconciliations.index'));

        $response->assertOk();
        $response->assertSee('Rekonsiliasi Bank');
        $this->assertAccountingTabsPresent($response);
    }

    // ==========================================
    // HELPER ASSERTIONS
    // ==========================================

    private function assertFinanceTabsPresent($response): void
    {
        $response->assertSee('Kas & Rekening');
        $response->assertSee(route('finance.cash-bank.index'));

        $response->assertSee('Buku Kas & Mutasi');
        $response->assertSee(route('finance.cash-bank.ledger'));

        $response->assertSee('Beban Operasional');
        $response->assertSee(route('finance.expenses.index'));

        $response->assertSee('Jurnal Akuntansi');
        $response->assertSee(route('finance.journals.index'));

        $response->assertSee('Piutang (AR)');
        $response->assertSee(route('finance.receivables'));

        $response->assertSee('Hutang (AP)');
        $response->assertSee(route('finance.payables'));

        $response->assertSee('Settlement Gateway');
        $response->assertSee(route('finance.settlements.index'));
    }

    private function assertAccountingTabsPresent($response): void
    {
        $response->assertSee('Bagan Akun (COA)');
        $response->assertSee(route('finance.coa.index'));

        $response->assertSee('Buku Besar Umum');
        $response->assertSee(route('finance.general-ledger'));

        $response->assertSee('Neraca Saldo');
        $response->assertSee(route('finance.trial-balance'));

        $response->assertSee('Neraca Keuangan SAK');
        $response->assertSee(route('finance.balance-sheet'));

        $response->assertSee('Rekonsiliasi Bank');
        $response->assertSee(route('finance.reconciliations.index'));
    }
}
