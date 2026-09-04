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
}
