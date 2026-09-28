<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Domain\Finance\PaymentSettlementService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\MerchantPayoutBankAccount;
use App\Models\PaymentSettlement;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Fase 4: Cooca Pay Payout Hub feature tests.
 */
final class PayoutHubPhase4Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Business $business;

    private MerchantPayoutBankAccount $verifiedAccount;

    private MerchantPayoutBankAccount $unverifiedAccount;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Ahmad Surya', 'email' => 'payout@test.com', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Bengkel Bagema', 'is_active' => true]);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        BusinessSubscription::create(['business_id' => $this->business->id, 'plan_code' => BusinessSubscription::PLAN_CORE, 'status' => BusinessSubscription::STATUS_ACTIVE, 'starts_at' => now()]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->verifiedAccount = MerchantPayoutBankAccount::create([
            'business_id'         => $this->business->id,
            'bank_code'           => 'BCA',
            'bank_name'           => 'Bank BCA',
            'account_number'      => '1234567890',
            'account_holder_name' => 'Ahmad Surya',
            'is_primary'          => true,
            'is_verified'         => true,
            'verified_at'         => now(),
            'created_by'          => $this->user->id,
        ]);

        $this->unverifiedAccount = MerchantPayoutBankAccount::create([
            'business_id'         => $this->business->id,
            'bank_code'           => 'MANDIRI',
            'bank_name'           => 'Bank Mandiri',
            'account_number'      => '9876543210',
            'account_holder_name' => 'Orang Lain',
            'is_primary'          => false,
            'is_verified'         => false,
            'created_by'          => $this->user->id,
        ]);
    }

    public function test_payout_model_has_payout_mode_constants(): void
    {
        $this->assertEquals('manual', PaymentSettlement::PAYOUT_MODE_MANUAL);
        $this->assertEquals('auto_h1', PaymentSettlement::PAYOUT_MODE_AUTO_H1);
    }

    public function test_payout_model_belongs_to_bank_account(): void
    {
        $settlement = PaymentSettlement::create([
            'business_id'            => $this->business->id,
            'settlement_number'      => 'SETTLE-TEST-001',
            'settlement_date'        => Carbon::today(),
            'payment_channel'        => 'cooca_pay',
            'gross_amount'           => 100000,
            'fee_amount'             => 1450,
            'net_amount'             => 98550,
            'destination_bank'       => 'BCA - 1234567890',
            'status'                 => PaymentSettlement::STATUS_PENDING,
            'payout_bank_account_id' => $this->verifiedAccount->id,
            'payout_mode'            => 'manual',
        ]);

        $this->assertNotNull($settlement->payoutBankAccount);
        $this->assertEquals('Bank BCA', $settlement->payoutBankAccount->bank_name);
    }

    public function test_request_payout_with_verified_bank_rejects_unverified(): void
    {
        $service = new PaymentSettlementService;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('belum terverifikasi');

        $service->requestPayoutWithVerifiedBank(
            business: $this->business,
            bankAccount: $this->unverifiedAccount,
            allocations: [['payment_type' => 'pos_order_payment', 'payment_id' => 'fake', 'amount' => 100]],
            userId: $this->user->id
        );
    }

    public function test_request_payout_with_verified_bank_rejects_wrong_business(): void
    {
        $otherBusiness = Business::create(['name' => 'Toko Lain', 'is_active' => true]);
        $service = new PaymentSettlementService;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak milik bisnis');

        $service->requestPayoutWithVerifiedBank(
            business: $otherBusiness,
            bankAccount: $this->verifiedAccount,
            allocations: [['payment_type' => 'pos_order_payment', 'payment_id' => 'fake', 'amount' => 100]],
            userId: $this->user->id
        );
    }

    public function test_schedule_auto_payout_skips_when_no_primary_verified_bank(): void
    {
        $this->verifiedAccount->update(['is_verified' => false]);

        $service = new PaymentSettlementService;
        $result = $service->scheduleAutoPayout($this->business);

        $this->assertNull($result);
    }

    public function test_settlement_auto_payout_queue_scope_works(): void
    {
        // Due auto-payout (should be in queue)
        PaymentSettlement::create([
            'business_id'          => $this->business->id,
            'settlement_number'    => 'SETTLE-AUTO-001',
            'settlement_date'      => Carbon::today(),
            'payment_channel'      => 'cooca_pay',
            'gross_amount'         => 50000,
            'fee_amount'           => 750,
            'net_amount'           => 49250,
            'status'               => PaymentSettlement::STATUS_PENDING,
            'payout_mode'          => PaymentSettlement::PAYOUT_MODE_AUTO_H1,
            'scheduled_payout_at'  => now()->subHour(),
        ]);

        // Manual (should NOT be in queue)
        PaymentSettlement::create([
            'business_id'          => $this->business->id,
            'settlement_number'    => 'SETTLE-MANUAL-002',
            'settlement_date'      => Carbon::today(),
            'payment_channel'      => 'cooca_pay',
            'gross_amount'         => 30000,
            'fee_amount'           => 500,
            'net_amount'           => 29500,
            'status'               => PaymentSettlement::STATUS_PENDING,
            'payout_mode'          => PaymentSettlement::PAYOUT_MODE_MANUAL,
        ]);

        // Completed auto (should NOT be in queue)
        PaymentSettlement::create([
            'business_id'          => $this->business->id,
            'settlement_number'    => 'SETTLE-AUTO-003',
            'settlement_date'      => Carbon::today(),
            'payment_channel'      => 'cooca_pay',
            'gross_amount'         => 70000,
            'fee_amount'           => 1000,
            'net_amount'           => 69000,
            'status'               => PaymentSettlement::STATUS_COMPLETED,
            'payout_mode'          => PaymentSettlement::PAYOUT_MODE_AUTO_H1,
            'scheduled_payout_at'  => now()->subHour(),
        ]);

        $queue = PaymentSettlement::autoPayoutQueue()->get();
        $this->assertCount(1, $queue);
        $this->assertEquals(PaymentSettlement::PAYOUT_MODE_AUTO_H1, $queue->first()->payout_mode);
        $this->assertEquals('SETTLE-AUTO-001', $queue->first()->settlement_number);
    }

    public function test_process_auto_payout_queue_returns_count(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            PaymentSettlement::create([
                'business_id'          => $this->business->id,
                'settlement_number'    => "SETTLE-BATCH-00{$i}",
                'settlement_date'      => Carbon::today(),
                'payment_channel'      => 'cooca_pay',
                'gross_amount'         => 100000,
                'fee_amount'           => 1500,
                'net_amount'           => 98500,
                'status'               => PaymentSettlement::STATUS_PENDING,
                'payout_mode'          => PaymentSettlement::PAYOUT_MODE_AUTO_H1,
                'scheduled_payout_at'  => now()->subMinute(),
            ]);
        }

        $processed = PaymentSettlementService::processAutoPayoutQueue();
        $this->assertEquals(3, $processed);
    }

    public function test_is_auto_payout_mode_helper(): void
    {
        $settlement = new PaymentSettlement(['payout_mode' => 'auto_h1']);
        $this->assertTrue($settlement->isAutoPayoutMode());

        $manual = new PaymentSettlement(['payout_mode' => 'manual']);
        $this->assertFalse($manual->isAutoPayoutMode());
    }

    public function test_admin_show_provides_identity_match_json(): void
    {
        $settlement = PaymentSettlement::create([
            'business_id'            => $this->business->id,
            'settlement_number'      => 'SETTLE-ADMIN-001',
            'settlement_date'        => Carbon::today(),
            'payment_channel'        => 'cooca_pay',
            'gross_amount'           => 200000,
            'fee_amount'             => 2000,
            'net_amount'             => 198000,
            'status'                 => PaymentSettlement::STATUS_PENDING,
            'payout_bank_account_id' => $this->verifiedAccount->id,
            'payout_mode'            => 'manual',
            'reconciled_by'          => $this->user->id,
        ]);

        $admin = \App\Models\Admin::create([
            'name'     => 'SuperAdmin',
            'email'    => 'admin-payout@cooca.id',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.settlements.show', $settlement));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'identity_verification' => ['owner_name', 'account_holder_name', 'is_match'],
            ]);

        $data = $response->json('identity_verification');
        $this->assertEquals('Ahmad Surya', $data['owner_name']);
        $this->assertEquals('Ahmad Surya', $data['account_holder_name']);
        $this->assertTrue($data['is_match']);
    }

    public function test_admin_show_detects_identity_mismatch(): void
    {
        $mismatchAccount = MerchantPayoutBankAccount::create([
            'business_id'         => $this->business->id,
            'bank_code'           => 'BRI',
            'bank_name'           => 'Bank BRI',
            'account_number'      => '5555555555',
            'account_holder_name' => 'Orang Berbeda',
            'is_primary'          => false,
            'is_verified'         => true,
            'verified_at'         => now(),
            'created_by'          => $this->user->id,
        ]);

        $settlement = PaymentSettlement::create([
            'business_id'            => $this->business->id,
            'settlement_number'      => 'SETTLE-MISMATCH-001',
            'settlement_date'        => Carbon::today(),
            'payment_channel'        => 'cooca_pay',
            'gross_amount'           => 150000,
            'fee_amount'             => 1800,
            'net_amount'             => 148200,
            'status'                 => PaymentSettlement::STATUS_PENDING,
            'payout_bank_account_id' => $mismatchAccount->id,
            'payout_mode'            => 'manual',
        ]);

        $admin = \App\Models\Admin::create([
            'name'     => 'Admin2',
            'email'    => 'admin2-payout@cooca.id',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.settlements.show', $settlement));

        $data = $response->json('identity_verification');
        $this->assertEquals('Ahmad Surya', $data['owner_name']);
        $this->assertEquals('Orang Berbeda', $data['account_holder_name']);
        $this->assertFalse($data['is_match']);
    }
}
