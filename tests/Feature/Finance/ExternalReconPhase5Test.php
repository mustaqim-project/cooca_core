<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Domain\Finance\ExternalReconciliationService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CashAccount;
use App\Models\ExternalAccountReconciliation;
use App\Models\PaymentSettlement;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Fase 5: External Reconciliation & Liquidity Dashboard tests.
 */
final class ExternalReconPhase5Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Business $business;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Recon Owner', 'email' => 'recon@test.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Toko Rekonsiliasi', 'is_active' => true]);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        BusinessSubscription::create(['business_id' => $this->business->id, 'plan_code' => BusinessSubscription::PLAN_CORE, 'status' => BusinessSubscription::STATUS_ACTIVE, 'starts_at' => now()]);
        $this->user->update(['active_business_id' => $this->business->id]);
        $this->token = $this->user->createToken('recon')->plainTextToken;
        Context::setBusiness($this->business);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'X-Business-Id' => $this->business->id, 'Accept' => 'application/json'];
    }

    // ── Model Tests ─────────────────────────────────────────

    public function test_model_has_channel_constants(): void
    {
        $this->assertEquals('edc', ExternalAccountReconciliation::CHANNEL_EDC);
        $this->assertEquals('ewallet', ExternalAccountReconciliation::CHANNEL_EWALLET);
        $this->assertEquals('marketplace', ExternalAccountReconciliation::CHANNEL_MARKETPLACE);
    }

    public function test_model_has_status_constants(): void
    {
        $this->assertEquals('draft', ExternalAccountReconciliation::STATUS_DRAFT);
        $this->assertEquals('submitted', ExternalAccountReconciliation::STATUS_SUBMITTED);
        $this->assertEquals('reviewed', ExternalAccountReconciliation::STATUS_REVIEWED);
        $this->assertEquals('discrepancy', ExternalAccountReconciliation::STATUS_DISCREPANCY);
    }

    public function test_model_recalculate_computes_expected_and_variance(): void
    {
        $recon = new ExternalAccountReconciliation([
            'opening_balance'    => 500000,
            'total_inflow'       => 300000,
            'total_disbursement' => 200000,
            'closing_balance'    => 600000,
        ]);

        $recon->recalculate();

        // expected = 500000 + 300000 - 200000 = 600000
        $this->assertEquals(600000.0, $recon->expected_closing);
        // variance = 600000 - 600000 = 0
        $this->assertEquals(0.0, $recon->variance);
        $this->assertFalse($recon->hasDiscrepancy());
    }

    public function test_model_detects_discrepancy(): void
    {
        $recon = new ExternalAccountReconciliation([
            'opening_balance'    => 500000,
            'total_inflow'       => 300000,
            'total_disbursement' => 200000,
            'closing_balance'    => 580000, // 20000 short
        ]);

        $recon->recalculate();

        // expected = 600000, closing = 580000, variance = -20000
        $this->assertEquals(600000.0, $recon->expected_closing);
        $this->assertEquals(-20000.0, $recon->variance);
        $this->assertTrue($recon->hasDiscrepancy());
    }

    public function test_model_tolerance_below_100_not_discrepancy(): void
    {
        $recon = new ExternalAccountReconciliation([
            'opening_balance'    => 500000,
            'total_inflow'       => 300000,
            'total_disbursement' => 200000,
            'closing_balance'    => 599950, // 50 off
        ]);

        $recon->recalculate();

        $this->assertEquals(-50.0, $recon->variance);
        $this->assertFalse($recon->hasDiscrepancy());
    }

    public function test_channel_options_returns_array(): void
    {
        $options = ExternalAccountReconciliation::channelOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('edc_bca', $options);
        $this->assertArrayHasKey('gopay', $options);
        $this->assertArrayHasKey('tokopedia', $options);
        $this->assertCount(10, $options);
    }

    // ── Service Tests ───────────────────────────────────────

    public function test_service_upsert_creates_new_entry(): void
    {
        $service = new ExternalReconciliationService;
        $recon = $service->upsertReconciliation(
            business: $this->business,
            channelType: 'edc_bca',
            channelLabel: 'EDC BCA - TID 12345',
            period: '2026-09',
            openingBalance: 1000000,
            totalInflow: 500000,
            totalDisbursement: 300000,
            closingBalance: 1200000,
            userId: $this->user->id,
        );

        $this->assertNotNull($recon->id);
        $this->assertEquals('submitted', $recon->status);
        $this->assertEquals(1200000.0, $recon->expected_closing);
        $this->assertEquals(0.0, $recon->variance);
        $this->assertFalse($recon->hasDiscrepancy());
    }

    public function test_service_upsert_updates_existing_entry(): void
    {
        $service = new ExternalReconciliationService;

        // Create
        $recon1 = $service->upsertReconciliation(
            business: $this->business,
            channelType: 'gopay',
            channelLabel: 'GoPay',
            period: '2026-09',
            openingBalance: 100000,
            totalInflow: 50000,
            totalDisbursement: 0,
            closingBalance: 150000,
        );

        // Update
        $recon2 = $service->upsertReconciliation(
            business: $this->business,
            channelType: 'gopay',
            channelLabel: 'GoPay Merchant',
            period: '2026-09',
            openingBalance: 100000,
            totalInflow: 80000,
            totalDisbursement: 30000,
            closingBalance: 150000,
        );

        $this->assertEquals($recon1->id, $recon2->id);
        $this->assertEquals('GoPay Merchant', $recon2->channel_label);
        $this->assertEquals(1, ExternalAccountReconciliation::count());
    }

    public function test_service_auto_flags_discrepancy(): void
    {
        $service = new ExternalReconciliationService;
        $recon = $service->upsertReconciliation(
            business: $this->business,
            channelType: 'ovo',
            channelLabel: 'OVO',
            period: '2026-09',
            openingBalance: 200000,
            totalInflow: 100000,
            totalDisbursement: 50000,
            closingBalance: 200000, // Should be 250000, deficit of 50000
        );

        $this->assertEquals('discrepancy', $recon->status);
        $this->assertEquals(-50000.0, $recon->variance);
        $this->assertTrue($recon->hasDiscrepancy());
    }

    // ── Liquidity Dashboard Tests ───────────────────────────

    public function test_liquidity_dashboard_aggregates_all_sources(): void
    {
        // Create internal cash account
        CashAccount::create([
            'business_id'    => $this->business->id,
            'name'           => 'Kas Toko',
            'type'           => CashAccount::TYPE_CASH,
            'current_balance' => 500000,
            'is_active'      => true,
        ]);

        CashAccount::create([
            'business_id'    => $this->business->id,
            'name'           => 'BCA Giro',
            'type'           => CashAccount::TYPE_BANK,
            'current_balance' => 3000000,
            'is_active'      => true,
        ]);

        // Create pending settlement (escrow)
        PaymentSettlement::create([
            'business_id'       => $this->business->id,
            'settlement_number' => 'SETTLE-LIQ-001',
            'settlement_date'   => Carbon::today(),
            'payment_channel'   => 'cooca_pay',
            'gross_amount'      => 200000,
            'fee_amount'        => 2000,
            'net_amount'        => 198000,
            'status'            => PaymentSettlement::STATUS_PENDING,
        ]);

        // Create external recon
        ExternalAccountReconciliation::create([
            'business_id'        => $this->business->id,
            'channel_type'       => 'edc_bca',
            'channel_label'      => 'EDC BCA',
            'period'             => Carbon::now()->format('Y-m'),
            'opening_balance'    => 100000,
            'total_inflow'       => 80000,
            'total_disbursement' => 50000,
            'closing_balance'    => 130000,
            'expected_closing'   => 130000,
            'variance'           => 0,
            'status'             => 'submitted',
        ]);

        $service = new ExternalReconciliationService;
        $dashboard = $service->buildLiquidityDashboard($this->business);

        // Internal: 500000 + 3000000 = 3500000
        $this->assertEquals(3500000.0, $dashboard['internal']['total']);
        $this->assertCount(2, $dashboard['internal']['accounts']);

        // Escrow: net = 200000 - 2000 = 198000
        $this->assertEquals(198000.0, $dashboard['escrow']['net']);
        $this->assertEquals(1, $dashboard['escrow']['count']);

        // External: 130000
        $this->assertEquals(130000.0, $dashboard['external']['total']);
        $this->assertCount(1, $dashboard['external']['accounts']);

        // Total: 3500000 + 198000 + 130000 = 3828000
        $this->assertEquals(3828000.0, $dashboard['liquidity']['total']);
        $this->assertEquals(0, $dashboard['liquidity']['discrepancy_count']);
    }

    public function test_liquidity_dashboard_counts_discrepancies(): void
    {
        ExternalAccountReconciliation::create([
            'business_id'        => $this->business->id,
            'channel_type'       => 'dana',
            'channel_label'      => 'DANA',
            'period'             => Carbon::now()->format('Y-m'),
            'opening_balance'    => 100000,
            'total_inflow'       => 50000,
            'total_disbursement' => 0,
            'closing_balance'    => 120000,
            'expected_closing'   => 150000,
            'variance'           => -30000,
            'status'             => 'discrepancy',
        ]);

        $service = new ExternalReconciliationService;
        $dashboard = $service->buildLiquidityDashboard($this->business);

        $this->assertEquals(1, $dashboard['liquidity']['discrepancy_count']);
        $this->assertEquals(-30000.0, $dashboard['liquidity']['total_variance']);
    }

    // ── Scope Tests ─────────────────────────────────────────

    public function test_scope_for_period_filters_correctly(): void
    {
        ExternalAccountReconciliation::create(['business_id' => $this->business->id, 'channel_type' => 'gopay', 'channel_label' => 'GoPay', 'period' => '2026-09', 'opening_balance' => 0, 'total_inflow' => 0, 'total_disbursement' => 0, 'closing_balance' => 0, 'expected_closing' => 0, 'variance' => 0, 'status' => 'draft']);
        ExternalAccountReconciliation::create(['business_id' => $this->business->id, 'channel_type' => 'gopay', 'channel_label' => 'GoPay', 'period' => '2026-10', 'opening_balance' => 0, 'total_inflow' => 0, 'total_disbursement' => 0, 'closing_balance' => 0, 'expected_closing' => 0, 'variance' => 0, 'status' => 'draft']);

        $this->assertCount(1, ExternalAccountReconciliation::forPeriod('2026-09')->get());
        $this->assertCount(1, ExternalAccountReconciliation::forPeriod('2026-10')->get());
    }

    public function test_scope_with_discrepancy_filters_correctly(): void
    {
        ExternalAccountReconciliation::create(['business_id' => $this->business->id, 'channel_type' => 'ovo', 'channel_label' => 'OVO', 'period' => '2026-09', 'opening_balance' => 0, 'total_inflow' => 0, 'total_disbursement' => 0, 'closing_balance' => 0, 'expected_closing' => 0, 'variance' => 0, 'status' => 'submitted']);
        ExternalAccountReconciliation::create(['business_id' => $this->business->id, 'channel_type' => 'dana', 'channel_label' => 'DANA', 'period' => '2026-09', 'opening_balance' => 0, 'total_inflow' => 0, 'total_disbursement' => 0, 'closing_balance' => 0, 'expected_closing' => 0, 'variance' => -50000, 'status' => 'discrepancy']);

        $this->assertCount(1, ExternalAccountReconciliation::withDiscrepancy()->get());
    }
}
