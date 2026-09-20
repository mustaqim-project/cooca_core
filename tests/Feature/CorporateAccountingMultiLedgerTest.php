<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AccountingReportService;
use App\Domain\Accounting\AutoJournalService;
use App\Domain\Accounting\BankReconciliationService;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CorporateAccountingMultiLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);

        $ownerRole = Role::where('slug', 'owner')->first();

        $this->user = User::create([
            'name' => 'Direktur Keuangan',
            'email' => 'finance@corp.cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Retail Corpora',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->user->id)
            ->first();

        Context::setBusiness($this->business, $membership);
    }

    public function test_auto_journal_service_seeds_standard_corporate_accounts_including_equity(): void
    {
        $service = new AutoJournalService();
        $service->ensureStandardAccounts($this->business);

        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '1-1001',
            'type' => ChartOfAccount::TYPE_ASSET,
        ]);

        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '3-3001',
            'name' => 'Modal Pemilik / Disetor',
            'type' => ChartOfAccount::TYPE_EQUITY,
        ]);

        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '3-3002',
            'name' => 'Laba Ditahan',
            'type' => ChartOfAccount::TYPE_EQUITY,
        ]);
    }

    public function test_chart_of_account_supports_multi_tier_parent_child_hierarchy(): void
    {
        $parent = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '1-1100',
            'name' => 'Kas & Setara Kas',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
        ]);

        $child = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'parent_id' => $parent->id,
            'code' => '1-1101',
            'name' => 'Kasir Shift Pagi',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
        ]);

        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child->isRoot());
        $this->assertSame($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_chart_of_account_deletion_guard_blocks_deleting_protected_accounts(): void
    {
        $parent = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '1-1200',
            'name' => 'Bank Operasional',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
        ]);

        $child = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'parent_id' => $parent->id,
            'code' => '1-1201',
            'name' => 'BCA Rekening Operasional',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
        ]);

        // Parent has children -> canBeDeleted() must be false
        $this->assertFalse($parent->canBeDeleted());

        // Attempting to delete parent via controller should fail and preserve account
        $this->actingAs($this->user)
            ->delete(route('finance.coa.destroy', $parent))
            ->assertRedirect();

        $this->assertDatabaseHas('chart_of_accounts', ['id' => $parent->id]);

        // Child has no children and no journal entries -> canBeDeleted() is true
        $this->assertTrue($child->canBeDeleted());
        $this->actingAs($this->user)
            ->delete(route('finance.coa.destroy', $child))
            ->assertRedirect();

        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $child->id]);
    }

    public function test_balance_sheet_calculates_sak_emkm_and_verifies_balance_identity(): void
    {
        (new AutoJournalService())->ensureStandardAccounts($this->business);

        $kasAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1-1001')->firstOrFail();
        $modalAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '3-3001')->firstOrFail();
        $revenueAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '4-4001')->firstOrFail();
        $expenseAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '6-6002')->firstOrFail();

        // 1. Initial capital injection: Debit Kas 1.000.000, Credit Modal 1.000.000
        $journal1 = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_date' => now()->subDays(5)->toDateString(),
            'entry_number' => 'JV-001',
            'description' => 'Setoran Modal Awal Pemilik',
            'total_debit' => 1000000,
            'total_credit' => 1000000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal1->id,
            'account_id' => $kasAccount->id,
            'type' => 'debit',
            'amount' => 1000000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal1->id,
            'account_id' => $modalAccount->id,
            'type' => 'credit',
            'amount' => 1000000,
        ]);

        // 2. Sales revenue: Debit Kas 500.000, Credit Pendapatan 500.000
        $journal2 = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_date' => now()->subDays(3)->toDateString(),
            'entry_number' => 'JV-002',
            'description' => 'Penjualan Kas',
            'total_debit' => 500000,
            'total_credit' => 500000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal2->id,
            'account_id' => $kasAccount->id,
            'type' => 'debit',
            'amount' => 500000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal2->id,
            'account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 500000,
        ]);

        // 3. Operational expense: Debit Beban 100.000, Credit Kas 100.000
        $journal3 = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_date' => now()->subDay()->toDateString(),
            'entry_number' => 'JV-003',
            'description' => 'Beban Operasional Listrik',
            'total_debit' => 100000,
            'total_credit' => 100000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal3->id,
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 100000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal3->id,
            'account_id' => $kasAccount->id,
            'type' => 'credit',
            'amount' => 100000,
        ]);

        $reportService = new AccountingReportService();
        $bs = $reportService->getBalanceSheet($this->business);

        // Assets = 1.000.000 + 500.000 - 100.000 = 1.400.000
        $this->assertEquals(1400000.0, $bs['summary']['total_assets']);
        // Current earnings = 500.000 - 100.000 = 400.000
        $this->assertEquals(400000.0, $bs['equity']['current_earnings']);
        // Liabilities = 0, Equity = 1.000.000 + 400.000 = 1.400.000
        $this->assertEquals(1400000.0, $bs['summary']['total_liabilities_and_equity']);
        // Perfectly balanced!
        $this->assertTrue($bs['summary']['is_balanced']);
        $this->assertEquals(0.0, $bs['summary']['difference']);
    }

    public function test_trial_balance_verifies_debits_equal_credits(): void
    {
        (new AutoJournalService())->ensureStandardAccounts($this->business);

        $kasAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1-1001')->firstOrFail();
        $modalAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '3-3001')->firstOrFail();

        $journal = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_date' => now()->toDateString(),
            'entry_number' => 'JV-TEST-TB',
            'description' => 'Test Trial Balance Entry',
            'total_debit' => 750000,
            'total_credit' => 750000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $kasAccount->id,
            'type' => 'debit',
            'amount' => 750000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $modalAccount->id,
            'type' => 'credit',
            'amount' => 750000,
        ]);

        $reportService = new AccountingReportService();
        $tb = $reportService->getTrialBalance($this->business);

        $this->assertEquals(750000.0, $tb['totals']['ending_debit']);
        $this->assertEquals(750000.0, $tb['totals']['ending_credit']);
        $this->assertTrue($tb['totals']['is_balanced']);
    }

    public function test_general_ledger_returns_chronological_running_balance(): void
    {
        (new AutoJournalService())->ensureStandardAccounts($this->business);

        $kasAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1-1001')->firstOrFail();
        $modalAccount = ChartOfAccount::where('business_id', $this->business->id)->where('code', '3-3001')->firstOrFail();

        $journal = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_date' => now()->toDateString(),
            'entry_number' => 'JV-GL-01',
            'description' => 'Test Ledger Entry',
            'total_debit' => 250000,
            'total_credit' => 250000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $kasAccount->id,
            'type' => 'debit',
            'amount' => 250000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $modalAccount->id,
            'type' => 'credit',
            'amount' => 250000,
        ]);

        $reportService = new AccountingReportService();
        $gl = $reportService->getGeneralLedger($this->business, '1-1001');

        $this->assertNotNull($gl['selected_account']);
        $this->assertCount(1, $gl['lines']);
        $this->assertEquals(250000.0, $gl['ending_balance']);
    }

    public function test_bank_reconciliation_imports_and_auto_matches_cash_transactions(): void
    {
        $cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'BCA Giro Utama',
            'account_number' => '888-001-9922',
            'bank_name' => 'Bank Central Asia',
            'current_balance' => 10000000,
            'is_active' => true,
        ]);

        // Existing internal cash transaction (customer transfer)
        $tx = CashTransaction::create([
            'business_id' => $this->business->id,
            'cash_account_id' => $cashAccount->id,
            'transaction_date' => now()->toDateString(),
            'type' => 'in',
            'amount' => 500000,
            'balance_after' => 10500000,
            'description' => 'Transfer Pembayaran Pelanggan Invoice #102',
            'created_by' => $this->user->id,
        ]);

        $reconciliationService = new BankReconciliationService();

        // Bank statement with incoming credit mutation matching $tx
        $statement = $reconciliationService->importStatement(
            $this->business,
            $cashAccount,
            'rekening_koran_bca_test.csv',
            Carbon::today(),
            [
                [
                    'date' => now()->toDateString(),
                    'description' => 'TRSF E-BANKING CR 500.000',
                    'amount' => 500000,
                    'type' => 'credit',
                    'reference_number' => 'REF-999812',
                ],
                [
                    'date' => now()->toDateString(),
                    'description' => 'BIAYA ADM BULANAN DB 15.000',
                    'amount' => 15000,
                    'type' => 'debit',
                    'reference_number' => 'REF-ADM-01',
                ],
            ],
            $this->user
        );

        $this->assertEquals(2, $statement->total_lines);

        $lineMatched = $statement->lines->firstWhere('amount', 500000);
        $lineUnmatched = $statement->lines->firstWhere('amount', 15000);

        $this->assertNotNull($lineMatched);
        $this->assertSame(BankStatementLine::STATUS_MATCHED, $lineMatched->status);
        $this->assertSame($tx->id, $lineMatched->matched_transaction_id);

        $this->assertNotNull($lineUnmatched);
        $this->assertSame(BankStatementLine::STATUS_UNMATCHED, $lineUnmatched->status);

        // Reconcile line
        $reconciliationService->reconcileLine($lineMatched, $this->user, 'Verifikasi cocok');
        $lineMatched->refresh();
        $this->assertSame(BankStatementLine::STATUS_RECONCILED, $lineMatched->status);
        $this->assertTrue($lineMatched->isReconciled());

        // Unmatch line
        $reconciliationService->unmatchLine($lineMatched);
        $lineMatched->refresh();
        $this->assertSame(BankStatementLine::STATUS_UNMATCHED, $lineMatched->status);
        $this->assertNull($lineMatched->matched_transaction_id);
    }

    public function test_accounting_web_routes_render_bento_views_cleanly(): void
    {
        (new AutoJournalService())->ensureStandardAccounts($this->business);

        $cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Kas Toko Pusat',
            'account_number' => 'CASH-001',
            'bank_name' => 'Tunai',
            'current_balance' => 500000,
            'is_active' => true,
        ]);

        // 1. COA View
        $this->actingAs($this->user)
            ->get(route('finance.coa.index'))
            ->assertOk()
            ->assertSee('Bagan Akun (Chart of Accounts)')
            ->assertSee('1-1001');

        // 2. Create sub-account via web POST
        $parent = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1-1001')->firstOrFail();
        $this->actingAs($this->user)
            ->post(route('finance.coa.store'), [
                'code' => '1-1001-01',
                'name' => 'Kasir Shift 1 Pagi',
                'type' => ChartOfAccount::TYPE_ASSET,
                'parent_id' => $parent->id,
                'normal_balance' => 'debit',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '1-1001-01',
            'parent_id' => $parent->id,
        ]);

        // 3. Balance Sheet View
        $this->actingAs($this->user)
            ->get(route('finance.balance-sheet'))
            ->assertOk()
            ->assertSee('Neraca Keuangan')
            ->assertSee('SAK EMKM');

        // 4. Trial Balance View
        $this->actingAs($this->user)
            ->get(route('finance.trial-balance'))
            ->assertOk()
            ->assertSee('Neraca Saldo (Trial Balance)')
            ->assertSee('Total Saldo Akhir Debit');

        // 5. General Ledger View
        $this->actingAs($this->user)
            ->get(route('finance.general-ledger', ['account' => '1-1001']))
            ->assertOk()
            ->assertSee('Buku Besar Umum')
            ->assertSee('1-1001');

        // 6. Reconciliation View
        $this->actingAs($this->user)
            ->get(route('finance.reconciliations.index'))
            ->assertOk()
            ->assertSee('Rekonsiliasi Bank');

        // 7. Manual Upload Statement Entries
        $this->actingAs($this->user)
            ->post(route('finance.reconciliations.upload'), [
                'cash_account_id' => $cashAccount->id,
                'statement_date' => now()->toDateString(),
                'manual_entries' => now()->toDateString() . ',Setoran Tunai Kasir Toko,350000,credit,REF-WEB-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bank_statements', [
            'business_id' => $this->business->id,
            'cash_account_id' => $cashAccount->id,
        ]);
        $this->assertDatabaseHas('bank_statement_lines', [
            'business_id' => $this->business->id,
            'amount' => 350000,
            'reference_number' => 'REF-WEB-01',
        ]);
    }

    /**
     * Test Audit SEC-01: Update COA memblokir parent_id yang mereferensikan dirinya sendiri atau turunannya.
     */
    public function test_coa_update_blocks_self_referencing_parent_and_descendants(): void
    {
        $accountA = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '1-1090',
            'name' => 'Akun Induk Uji',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
            'is_active' => true,
        ]);

        $accountB = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'parent_id' => $accountA->id,
            'code' => '1-1090.01',
            'name' => 'Sub Akun Uji B',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
            'is_active' => true,
        ]);

        // 1. Percobaan menjadikan diri sendiri sebagai parent
        $response1 = $this->actingAs($this->user)
            ->put(route('finance.coa.update', $accountA), [
                'name' => 'Akun Induk Uji Dimodifikasi',
                'code' => '1-1090',
                'parent_id' => $accountA->id,
                'is_active' => 1,
            ]);

        $response1->assertSessionHasErrors('parent_id');

        // 2. Percobaan menjadikan sub-akun (turunan) sebagai parent
        $response2 = $this->actingAs($this->user)
            ->put(route('finance.coa.update', $accountA), [
                'name' => 'Akun Induk Uji Dimodifikasi',
                'code' => '1-1090',
                'parent_id' => $accountB->id,
                'is_active' => 1,
            ]);

        $response2->assertSessionHasErrors('parent_id');
    }

    /**
     * Test Audit SEC-02: Buku Besar menelusuri rute dokumen sumber secara aman (fail-safe).
     */
    public function test_general_ledger_resolves_all_source_document_routes_safely(): void
    {
        $autoJournal = new AutoJournalService();
        $autoJournal->ensureStandardAccounts($this->business);
        $cashAcc = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1-1001')->firstOrFail();

        // Buat jurnal dengan referensi return dan settlement
        $entry1 = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_number' => 'JRN-TEST-RET-01',
            'entry_date' => now()->toDateString(),
            'description' => 'Uji Retur Pembelian',
            'reference_type' => JournalEntry::REF_PURCHASE_RETURN,
            'reference_id' => (string) Str::uuid(),
            'total_debit' => 50000,
            'total_credit' => 50000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $entry1->id,
            'account_id' => $cashAcc->id,
            'type' => 'debit',
            'amount' => 50000,
        ]);

        $entry2 = JournalEntry::create([
            'business_id' => $this->business->id,
            'entry_number' => 'JRN-TEST-RET-02',
            'entry_date' => now()->toDateString(),
            'description' => 'Uji Retur Penjualan',
            'reference_type' => JournalEntry::REF_SALES_RETURN,
            'reference_id' => (string) Str::uuid(),
            'total_debit' => 50000,
            'total_credit' => 50000,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $entry2->id,
            'account_id' => $cashAcc->id,
            'type' => 'credit',
            'amount' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('finance.general-ledger', ['account' => '1-1001']));

        $response->assertOk()
            ->assertSee('Buku Besar Umum')
            ->assertSee('JRN-TEST-RET-01')
            ->assertSee('JRN-TEST-RET-02');
    }

    /**
     * Test Audit SEC-03 & SEC-04: Validasi unggah rekening koran dan parsing nominal IDR.
     */
    public function test_reconciliation_upload_validation_and_idr_currency_parsing(): void
    {
        $cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Bank Mandiri Giro',
            'account_number' => '1234567890',
            'bank_name' => 'MANDIRI',
            'balance' => 10000000,
            'is_active' => true,
        ]);

        // 1. Gagal jika file dan manual entries keduanya kosong
        $responseEmpty = $this->actingAs($this->user)
            ->post(route('finance.reconciliations.upload'), [
                'cash_account_id' => $cashAccount->id,
                'statement_date' => now()->toDateString(),
                'statement_file' => null,
                'manual_entries' => null,
            ]);

        $responseEmpty->assertSessionHasErrors(['statement_file', 'manual_entries']);

        // 2. Berhasil dengan format nominal Indonesia (titik ribuan: 1.750.000)
        $responseIdr = $this->actingAs($this->user)
            ->post(route('finance.reconciliations.upload'), [
                'cash_account_id' => $cashAccount->id,
                'statement_date' => now()->toDateString(),
                'manual_entries' => now()->toDateString() . ',Transfer Client BCA,1.750.000,credit,MANDIRI-REF-99',
            ]);

        $responseIdr->assertRedirect();

        $this->assertDatabaseHas('bank_statement_lines', [
            'business_id' => $this->business->id,
            'amount' => 1750000,
            'reference_number' => 'MANDIRI-REF-99',
        ]);
    }
}
