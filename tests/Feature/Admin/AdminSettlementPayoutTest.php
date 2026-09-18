<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Finance\PaymentSettlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CashAccount;
use App\Models\JournalEntry;
use App\Models\PaymentSettlement;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdminSettlementPayoutTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Business $business;
    private User $merchantUser;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->admin = Admin::factory()->create([
            'name'      => 'Finance Superadmin',
            'email'     => 'superadmin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $this->merchantUser = User::create([
            'name'              => 'Merchant Owner',
            'email'             => 'merchant@example.com',
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'      => 'Kedai Kopi Maju',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code'   => BusinessSubscription::PLAN_CORE,
            'status'      => BusinessSubscription::STATUS_ACTIVE,
            'starts_at'   => now(),
        ]);

        $this->merchantUser->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    private function createPendingSettlement(float $gross = 100000.0, float $fee = 2500.0): PaymentSettlement
    {
        $order = PosOrder::create([
            'business_id'  => $this->business->id,
            'user_id'      => $this->merchantUser->id,
            'order_number' => 'POS-TEST-' . Str::random(4),
            'order_date'   => '2026-09-18',
            'status'       => PosOrder::STATUS_COMPLETED,
            'total_amount' => $gross,
        ]);

        $payment = PosOrderPayment::create([
            'pos_order_id'     => $order->id,
            'payment_method'   => PosOrderPayment::METHOD_QRIS,
            'amount'           => $gross,
            'reference_number' => 'REF-' . Str::random(6),
            'net_amount'       => $gross,
            'status'           => 'paid',
        ]);

        $service = new PaymentSettlementService();
        return $service->reconcile(
            business: $this->business,
            data: [
                'settlement_number' => 'REQ-SETTLE-001',
                'settlement_date'   => '2026-09-18',
                'payment_channel'   => 'tripay',
                'gross_amount'      => $gross,
                'fee_amount'        => $fee,
                'net_amount'        => $gross - $fee,
                'destination_bank'  => 'BCA - 1234567890 a/n Kedai Kopi',
                'notes'             => 'Mohon dicairkan segera',
            ],
            allocations: [
                [
                    'payment_type' => 'pos_order_payment',
                    'payment_id'   => $payment->id,
                    'amount'       => $gross,
                ],
            ],
            userId: $this->merchantUser->id,
            immediateComplete: false
        );
    }

    public function test_admin_can_view_settlements_index_and_kpi(): void
    {
        $settlement = $this->createPendingSettlement();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.settlements.index'));

        $response->assertOk();
        $response->assertSee('Pencairan Saldo &amp; Bukti Transfer', false);
        $response->assertSee('REQ-SETTLE-001');
        $response->assertSee('Kedai Kopi Maju');
        $response->assertSee('Menunggu Transfer');
    }

    public function test_admin_can_view_settlement_detail_page(): void
    {
        $settlement = $this->createPendingSettlement();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.settlements.show', $settlement->id));

        $response->assertOk();
        $response->assertSee('Detail Pencairan Settlement #' . $settlement->settlement_number);
        $response->assertSee('BCA - 1234567890 a/n Kedai Kopi');
        $response->assertSee('Kedai Kopi Maju');
    }

    public function test_admin_can_approve_settlement_with_proof_image_upload(): void
    {
        Storage::fake('public');

        $settlement = $this->createPendingSettlement(100000.0, 2500.0);
        $this->assertSame(PaymentSettlement::STATUS_PENDING, $settlement->status);

        $fakeImage = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 800, 1000);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.settlements.approve', $settlement->id), [
            'proof_image'    => $fakeImage,
            'transferred_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'admin_notes'    => 'Transfer via KlikBCA Bisnis Reff #987654321',
        ]);

        $response->assertRedirect(route('admin.settlements.show', $settlement->id));
        $response->assertSessionHas('success');

        $settlement->refresh();
        $this->assertSame(PaymentSettlement::STATUS_COMPLETED, $settlement->status);
        $this->assertNotNull($settlement->proof_image_path);
        $this->assertNotNull($settlement->proof_image_url);
        $this->assertNotNull($settlement->transferred_at);
        $this->assertSame($this->admin->id, $settlement->admin_id);
        $this->assertSame('Transfer via KlikBCA Bisnis Reff #987654321', $settlement->admin_notes);

        // Verify file is stored in public disk
        Storage::disk('public')->assertExists($settlement->proof_image_path);

        // Verify journal entry is created and balanced
        $journal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_SETTLEMENT)
            ->first();

        $this->assertNotNull($journal);
        $this->assertSame($journal->total_debit, $journal->total_credit);

        // Verify Cash Account updated
        $bankAccount = CashAccount::where('business_id', $this->business->id)
            ->where('type', CashAccount::TYPE_BANK)
            ->first();

        $this->assertNotNull($bankAccount);
        $this->assertSame(97500.0, (float) $bankAccount->current_balance);
    }

    public function test_admin_approval_validates_image_mimes(): void
    {
        Storage::fake('public');

        $settlement = $this->createPendingSettlement();

        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.settlements.approve', $settlement->id), [
            'proof_image' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['proof_image']);
        $settlement->refresh();
        $this->assertSame(PaymentSettlement::STATUS_PENDING, $settlement->status);
    }

    public function test_admin_can_reject_settlement_and_release_allocations(): void
    {
        $settlement = $this->createPendingSettlement();
        $allocationCount = $settlement->allocations()->count();
        $this->assertSame(1, $allocationCount);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.settlements.reject', $settlement->id), [
            'rejection_reason' => 'Nomor rekening atas nama berbeda dengan nama usaha.',
        ]);

        $response->assertRedirect(route('admin.settlements.show', $settlement->id));
        $response->assertSessionHas('success');

        $settlement->refresh();
        $this->assertSame(PaymentSettlement::STATUS_REJECTED, $settlement->status);
        $this->assertSame('Nomor rekening atas nama berbeda dengan nama usaha.', $settlement->rejection_reason);
        $this->assertSame(0, $settlement->allocations()->count());

        // The transaction is released back into unsettled pool
        $service = new PaymentSettlementService();
        $unsettled = $service->getUnsettledPayments($this->business);
        $this->assertGreaterThan(0, $unsettled['summary']['count']);
    }

    public function test_merchant_can_view_proof_image_in_web_and_json(): void
    {
        Storage::fake('public');

        $settlement = $this->createPendingSettlement(50000.0, 1000.0);
        $fakeImage = UploadedFile::fake()->image('struk.jpg', 600, 600);

        // Superadmin approves with proof
        $this->actingAs($this->admin, 'admin')->post(route('admin.settlements.approve', $settlement->id), [
            'proof_image' => $fakeImage,
            'admin_notes' => 'Lunas via Transfer Bank',
        ]);

        $settlement->refresh();

        // Flush admin context and authenticate as merchant owner
        auth('admin')->logout();
        Context::flush();
        $membership = \App\Models\BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->merchantUser->id)
            ->first();
        Context::setBusiness($this->business, $membership);

        // Merchant visits settlement detail via HTML
        $webResponse = $this->actingAs($this->merchantUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.settlements.show', $settlement->id));
        $webResponse->assertOk();
        $webResponse->assertSee('Bukti Transfer Resmi dari Admin COOCA');
        $webResponse->assertSee('Lunas via Transfer Bank');
        $webResponse->assertSee($settlement->proof_image_url);

        // Merchant visits settlement detail via JSON API
        $jsonResponse = $this->actingAs($this->merchantUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('finance.settlements.show', $settlement->id));
        $jsonResponse->assertOk();
        $jsonResponse->assertJsonPath('settlement.status', PaymentSettlement::STATUS_COMPLETED);
        $jsonResponse->assertJsonPath('settlement.proof_image_url', $settlement->proof_image_url);
    }
}
