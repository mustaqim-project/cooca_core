<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Finance\CashLedgerService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class CashLedgerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->user = User::create(['name' => 'Cash Owner', 'email' => 'cash@example.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Cash Business']);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_ledger_updates_balance_and_is_idempotent(): void
    {
        $service = new CashLedgerService;
        $first = $service->recordInflow($this->business, 100000, 'test', 'ref-1', 'Modal awal', 'cash', $this->user->id);
        $second = $service->recordInflow($this->business, 100000, 'test', 'ref-1', 'Retry modal', 'cash', $this->user->id);
        $service->recordOutflow($this->business, 25000, 'test', 'ref-2', 'Belanja', 'cash', $this->user->id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(75000.0, CashAccount::firstOrFail()->current_balance);
        $this->assertSame(2, CashTransaction::count());
        $this->expectException(InvalidArgumentException::class);
        $service->recordOutflow($this->business, 75001, 'test', 'ref-3', 'Terlalu besar', 'cash', $this->user->id);
    }

    public function test_transfer_moves_amount_between_two_accounts_atomically(): void
    {
        $service = new CashLedgerService;
        $cash = $service->accountFor($this->business, 'cash');
        $bank = $service->accountFor($this->business, 'bank_transfer');
        $service->recordInflow($this->business, 200000, 'test', 'ref-transfer-seed', 'Modal', 'cash', $this->user->id);
        $service->transfer($cash, $bank, 80000, 'Setor ke bank', $this->user->id);

        $this->assertSame(120000.0, $cash->fresh()->current_balance);
        $this->assertSame(80000.0, $bank->fresh()->current_balance);
        $this->assertSame(2, CashTransaction::where('type', CashTransaction::TYPE_TRANSFER)->count());
    }

    public function test_transfer_creates_balanced_journal_entry(): void
    {
        $service = new CashLedgerService;
        $cash = $service->accountFor($this->business, 'cash');
        $bank = $service->accountFor($this->business, 'bank_transfer');
        $service->recordInflow($this->business, 150000, 'test', 'seed-journal', 'Modal', 'cash', $this->user->id);
        $service->transfer($cash, $bank, 50000, 'Setoran tunai ke bank', $this->user->id);

        $journal = \App\Models\JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', \App\Models\JournalEntry::REF_CASH_TRANSFER)
            ->first();

        $this->assertNotNull($journal);
        $this->assertSame(50000.0, (float) $journal->total_debit);
        $this->assertSame(50000.0, (float) $journal->total_credit);
        $this->assertCount(2, $journal->lines);
    }

    public function test_expense_store_creates_record_journal_and_outflow_atomically(): void
    {
        $ledger = new CashLedgerService;
        $cash = $ledger->accountFor($this->business, 'cash');
        $ledger->recordInflow($this->business, 500000, 'test', 'seed-exp', 'Saldo kas', 'cash', $this->user->id);

        $response = $this->actingAs($this->user)->post(route('finance.expenses.store'), [
            'expense_date' => now()->toDateString(),
            'category' => 'utilities',
            'amount' => 125000,
            'payment_method' => 'cash',
            'cash_account_id' => $cash->id,
            'description' => 'Tagihan listrik kantor',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'business_id' => $this->business->id,
            'category' => 'utilities',
            'amount' => 125000,
        ]);

        $this->assertSame(375000.0, $cash->fresh()->current_balance);
    }

    public function test_finance_aging_views_render_successfully_with_filters(): void
    {
        $responseAP = $this->actingAs($this->user)->get(route('finance.payables', ['bucket' => 'not_due', 'search' => 'INV']));
        $responseAP->assertOk();

        $responseAR = $this->actingAs($this->user)->get(route('finance.receivables', ['bucket' => 'all']));
        $responseAR->assertOk();

        $responseLedger = $this->actingAs($this->user)->get(route('finance.cash-bank.ledger', ['type' => 'in']));
        $responseLedger->assertOk();

        $responseJournals = $this->actingAs($this->user)->get(route('finance.journals.index'));
        $responseJournals->assertOk();
    }
}

