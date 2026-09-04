<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CashAccount;
use App\Models\JournalEntry;
use App\Models\PaymentSettlement;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PaymentSettlementReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->user = User::create(['name' => 'Settlement Owner', 'email' => 'settlement@example.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Settlement Business', 'is_active' => true]);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        BusinessSubscription::create(['business_id' => $this->business->id, 'plan_code' => BusinessSubscription::PLAN_CORE, 'status' => BusinessSubscription::STATUS_ACTIVE, 'starts_at' => now()]);
        $this->user->update(['active_business_id' => $this->business->id]);
        $this->token = $this->user->createToken('settlement')->plainTextToken;
        Context::setBusiness($this->business);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'X-Business-Id' => $this->business->id, 'Accept' => 'application/json'];
    }

    public function test_gateway_settlement_matches_payment_posts_net_and_fee_once(): void
    {
        $order = PosOrder::create(['business_id' => $this->business->id, 'user_id' => $this->user->id, 'order_number' => 'POS-SET-01', 'order_date' => '2026-09-04', 'status' => PosOrder::STATUS_COMPLETED, 'total_amount' => 100000]);
        $payment = PosOrderPayment::create(['pos_order_id' => $order->id, 'payment_method' => PosOrderPayment::METHOD_QRIS, 'amount' => 100000, 'reference_number' => 'GW-001', 'net_amount' => 100000, 'status' => 'paid']);

        $payload = ['settlement_number' => 'SET-001', 'settlement_date' => '2026-09-04', 'payment_channel' => 'qris', 'gross_amount' => 100000, 'fee_amount' => 2500, 'net_amount' => 97500, 'allocations' => [['payment_type' => 'pos_order_payment', 'payment_id' => $payment->id, 'amount' => 100000]]];
        $response = $this->withHeaders($this->headers())->postJson('/api/v1/finance/settlements/reconcile', $payload);
        $response->assertCreated()->assertJsonPath('settlement.status', PaymentSettlement::STATUS_COMPLETED);
        $this->assertSame(97500.0, CashAccount::where('business_id', $this->business->id)->where('type', CashAccount::TYPE_EWALLET)->firstOrFail()->current_balance);
        $journal = JournalEntry::where('reference_type', JournalEntry::REF_SETTLEMENT)->firstOrFail();
        $this->assertSame($journal->total_debit, $journal->total_credit);
        $this->withHeaders($this->headers())->postJson('/api/v1/finance/settlements/reconcile', $payload)->assertCreated();
        $this->assertSame(1, PaymentSettlement::count());
        $this->assertSame(1, JournalEntry::where('reference_type', JournalEntry::REF_SETTLEMENT)->count());
    }

    public function test_settlement_rejects_gross_mismatch(): void
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/v1/finance/settlements/reconcile', ['settlement_number' => 'SET-002', 'settlement_date' => '2026-09-04', 'payment_channel' => 'qris', 'gross_amount' => 100, 'fee_amount' => 1, 'allocations' => [['payment_type' => 'pos_order_payment', 'payment_id' => 'missing', 'amount' => 99]]]);
        $response->assertStatus(422);
    }
}
