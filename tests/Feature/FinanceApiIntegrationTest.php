<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CashAccount;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FinanceApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->user = User::create(['name' => 'Finance API User', 'email' => 'finance-api@example.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Finance API Business', 'is_active' => true]);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        BusinessSubscription::create(['business_id' => $this->business->id, 'plan_code' => BusinessSubscription::PLAN_CORE, 'status' => BusinessSubscription::STATUS_ACTIVE, 'starts_at' => now()]);
        $this->user->update(['active_business_id' => $this->business->id]);
        $this->token = $this->user->createToken('finance-api')->plainTextToken;
        Context::setBusiness($this->business);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'X-Business-Id' => $this->business->id, 'Accept' => 'application/json'];
    }

    public function test_cash_ledger_api_records_idempotent_inflow_and_transfer(): void
    {
        $inflow = $this->withHeaders($this->headers())->postJson('/api/v1/finance/cash-ledger/inflows', ['account_method' => 'cash', 'amount' => 200000, 'description' => 'Modal API', 'reference_id' => 'api-cash-1']);
        $inflow->assertCreated()->assertJsonPath('transaction.amount', 200000);
        $this->withHeaders($this->headers())->postJson('/api/v1/finance/cash-ledger/inflows', ['account_method' => 'cash', 'amount' => 200000, 'description' => 'Retry API', 'reference_id' => 'api-cash-1'])->assertCreated();
        $this->withHeaders($this->headers())->postJson('/api/v1/finance/cash-ledger/inflows', ['account_method' => 'bank_transfer', 'amount' => 100000, 'description' => 'Modal bank', 'reference_id' => 'api-bank-1'])->assertCreated();
        $accounts = $this->withHeaders($this->headers())->getJson('/api/v1/finance/cash-ledger/accounts')->assertOk();
        $cash = CashAccount::where('business_id', $this->business->id)->where('type', CashAccount::TYPE_CASH)->firstOrFail();
        $bank = CashAccount::where('business_id', $this->business->id)->where('type', CashAccount::TYPE_BANK)->firstOrFail();
        $this->withHeaders($this->headers())->postJson('/api/v1/finance/cash-ledger/transfers', ['from_account_id' => $cash->id, 'to_account_id' => $bank->id, 'amount' => 50000, 'description' => 'Setor API'])->assertCreated();
        $this->assertSame(150000.0, $cash->fresh()->current_balance);
        $this->assertSame(150000.0, $bank->fresh()->current_balance);
        $this->assertSame(4, \App\Models\CashTransaction::count());
        $accounts->assertJsonStructure(['accounts']);
    }

    public function test_supplier_invoice_api_lists_and_records_payment(): void
    {
        $supplier = Supplier::create(['business_id' => $this->business->id, 'name' => 'API Supplier']);
        $location = Location::create(['business_id' => $this->business->id, 'name' => 'Finance API Location', 'type' => 'warehouse', 'is_active' => true]);
        $receipt = GoodsReceipt::create(['business_id' => $this->business->id, 'location_id' => $location->id, 'supplier_id' => $supplier->id, 'receipt_number' => 'GR-FIN-01', 'receipt_date' => '2026-09-04', 'status' => 'completed']);
        $invoice = SupplierInvoice::create(['business_id' => $this->business->id, 'supplier_id' => $supplier->id, 'goods_receipt_id' => $receipt->id, 'invoice_number' => 'AP-FIN-01', 'invoice_date' => '2026-09-04', 'total_amount' => 100000, 'balance_due' => 100000, 'status' => SupplierInvoice::STATUS_UNPAID]);
        $this->withHeaders($this->headers())->postJson('/api/v1/finance/cash-ledger/inflows', ['account_method' => 'bank_transfer', 'amount' => 200000, 'description' => 'Saldo bank', 'reference_id' => 'seed-bank'])->assertCreated();
        $this->withHeaders($this->headers())->getJson('/api/v1/purchasing/supplier-invoices')->assertOk()->assertJsonStructure(['supplier_invoices', 'summary', 'pagination']);
        $response = $this->withHeaders($this->headers())->postJson('/api/v1/purchasing/supplier-invoices/' . $invoice->id . '/payments', ['amount' => 40000, 'payment_date' => '2026-09-04', 'payment_method' => 'bank_transfer', 'payment_number' => 'SP-API-01']);
        $response->assertCreated()->assertJsonPath('supplier_invoice.balance_due', 60000);
        $this->assertDatabaseHas('supplier_payments', ['payment_number' => 'SP-API-01', 'amount' => 40000]);
    }

    public function test_supplier_invoice_api_rejects_cross_tenant_access(): void
    {
        $other = Business::create(['name' => 'Other Finance Business']);
        $supplier = Supplier::create(['business_id' => $other->id, 'name' => 'Other Supplier']);
        $location = Location::create(['business_id' => $other->id, 'name' => 'Other Location', 'type' => 'warehouse', 'is_active' => true]);
        $receipt = GoodsReceipt::create(['business_id' => $other->id, 'location_id' => $location->id, 'supplier_id' => $supplier->id, 'receipt_number' => 'GR-OTHER-01', 'receipt_date' => '2026-09-04', 'status' => 'completed']);
        $invoice = SupplierInvoice::create(['business_id' => $other->id, 'supplier_id' => $supplier->id, 'goods_receipt_id' => $receipt->id, 'invoice_number' => 'AP-OTHER-01', 'invoice_date' => '2026-09-04', 'total_amount' => 10, 'balance_due' => 10, 'status' => SupplierInvoice::STATUS_UNPAID]);
        $this->withHeaders($this->headers())->getJson('/api/v1/purchasing/supplier-invoices/' . $invoice->id)->assertNotFound();
    }
}
