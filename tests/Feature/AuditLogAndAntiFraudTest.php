<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Security\AntiFraudService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\PosOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AuditLogAndAntiFraudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createBusinessWithUser(string $scale = 'corporate', string $role = 'owner'): array
    {
        $uid = uniqid();
        $user = User::factory()->create([
            'name' => 'Budi Owner ' . $uid,
            'email' => 'budi-' . $uid . '@cooca-test.id',
            'phone' => '0812' . rand(10000000, 99999999),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        $business = Business::create([
            'name' => 'PT Maju Terus Korporasi ' . $uid,
            'slug' => 'pt-maju-terus-korporasi-' . $uid,
            'phone' => $user->phone,
            'email' => 'contact-' . $uid . '@ptmajuterus.id',
            'address' => 'Jl. Jenderal Sudirman No. 45, Jakarta',
            'currency' => 'IDR',
            'business_scale' => $scale,
            'is_active' => true,
        ]);

        $user->update(['active_business_id' => $business->id]);

        BusinessMembership::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);

        return [$business, $user];
    }

    public function test_audit_log_records_normal_event_with_low_risk(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Pelanggan Biasa',
            'phone' => '081122334455',
            'email' => 'pelanggan@test.com',
        ]);

        $log = AuditLog::where('business_id', $business->id)
            ->where('auditable_type', Customer::class)
            ->where('auditable_id', $customer->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('created', $log->action);
        $this->assertSame(AuditLog::RISK_LOW, $log->risk_level);
        $this->assertTrue($log->isLowRisk());
    }

    public function test_pos_order_void_triggers_high_risk_and_whatsapp_alert(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $order = PosOrder::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'POS-2026-0042',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 450000,
            'total_amount' => 450000,
            'paid_amount' => 450000,
        ]);

        // Trigger void order
        $order->update([
            'status' => PosOrder::STATUS_VOIDED,
            'void_reason' => 'Pelanggan salah pesan makanan',
            'voided_by' => $user->id,
            'voided_at' => now(),
        ]);

        $log = AuditLog::where('business_id', $business->id)
            ->where('auditable_type', PosOrder::class)
            ->where('auditable_id', $order->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(AuditLog::RISK_HIGH, $log->risk_level);
        $this->assertSame('Pembatalan Pesanan Kasir (Void Order)', $log->risk_reason);
        $this->assertStringContainsString('Pelanggan salah pesan makanan', (string) $log->notes);
        $this->assertNotNull($log->alert_sent_at);
        $this->assertSame($user->phone, $log->alert_recipient);
    }

    public function test_pos_order_excessive_discount_triggers_high_risk(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $order = PosOrder::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'POS-2026-0088',
            'order_date' => now(),
            'status' => PosOrder::STATUS_PENDING,
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);

        // Cashier gives 30% discount (> 20%)
        $order->update([
            'discount_percentage' => 30.0,
            'discount_amount' => 60000,
            'total_amount' => 140000,
        ]);

        $log = AuditLog::where('business_id', $business->id)
            ->where('auditable_type', PosOrder::class)
            ->where('auditable_id', $order->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(AuditLog::RISK_HIGH, $log->risk_level);
        $this->assertSame('Pemberian Diskon Kasir di atas 20%', $log->risk_reason);
    }

    public function test_supplier_bank_account_change_triggers_high_risk_and_alert(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'CV Sumber Rejeki',
            'slug' => 'cv-sumber-rejeki-' . uniqid(),
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'CV Sumber Rejeki',
        ]);

        // Malicious or critical change in bank account
        $supplier->update([
            'bank_account_number' => '9988776655',
            'bank_name' => 'Mandiri',
        ]);

        $log = AuditLog::where('business_id', $business->id)
            ->where('auditable_type', Supplier::class)
            ->where('auditable_id', $supplier->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(AuditLog::RISK_HIGH, $log->risk_level);
        $this->assertSame('Perubahan Nomor Rekening Bank Supplier', $log->risk_reason);
        $this->assertStringContainsString('1234567890', (string) $log->notes);
        $this->assertStringContainsString('9988776655', (string) $log->notes);
        $this->assertNotNull($log->alert_sent_at);
    }

    public function test_manual_journal_deletion_triggers_high_risk(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $journal = JournalEntry::create([
            'business_id' => $business->id,
            'entry_number' => 'JRN-2026-001',
            'entry_date' => now()->toDateString(),
            'description' => 'Penyesuaian biaya operasional',
            'total_debit' => 1500000,
            'total_credit' => 1500000,
            'created_by' => $user->id,
        ]);

        // Delete manual journal entry
        $journal->delete();

        $log = AuditLog::where('business_id', $business->id)
            ->where('auditable_type', JournalEntry::class)
            ->where('auditable_id', $journal->id)
            ->where('action', 'deleted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(AuditLog::RISK_HIGH, $log->risk_level);
        $this->assertSame('Penghapusan Transaksi Jurnal Manual', $log->risk_reason);
    }

    public function test_audit_log_is_strictly_immutable(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Pelanggan Uji Mutabilitas',
        ]);

        $log = AuditLog::where('auditable_id', $customer->id)->first();
        $this->assertNotNull($log);

        // Attempt 1: Modifying immutable data should throw RuntimeException
        try {
            $log->update(['action' => 'tampered_action']);
            $this->fail('Expected RuntimeException on updating immutable audit log data');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Attempt 2: Deleting should throw RuntimeException
        try {
            $log->delete();
            $this->fail('Expected RuntimeException on deleting immutable audit log');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Verify log is still in database
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'created',
        ]);
    }

    public function test_audit_log_explorer_filtering(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        // Low risk log
        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'auditable_type' => Customer::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'created',
            'risk_level' => AuditLog::RISK_LOW,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // High risk log
        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'auditable_type' => PosOrder::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'updated',
            'risk_level' => AuditLog::RISK_HIGH,
            'risk_reason' => 'Pembatalan Pesanan Kasir (Void Order)',
            'notes' => 'Pelanggan komplain salah input',
            'ip_address' => '182.253.11.4',
            'created_at' => now(),
        ]);

        // 1. Visit index without filter
        $response = $this->get(route('settings.audit-logs.index'));
        $response->assertOk();
        $response->assertSee('Jejak Audit');
        $response->assertSee('Pembatalan Pesanan Kasir (Void Order)');

        // 2. Filter by risk_level=high
        $responseHigh = $this->get(route('settings.audit-logs.index', ['risk_level' => 'high']));
        $responseHigh->assertOk();
        $responseHigh->assertSee('Pembatalan Pesanan Kasir (Void Order)');
    }

    public function test_visual_diff_payload_and_modal_view(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $log = AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'auditable_type' => PosOrder::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'updated',
            'risk_level' => AuditLog::RISK_HIGH,
            'risk_reason' => 'Pembatalan Pesanan Kasir (Void Order)',
            'old_values' => ['status' => 'completed', 'total_amount' => 450000],
            'new_values' => ['status' => 'voided', 'total_amount' => 450000, 'void_reason' => 'Salah meja'],
            'ip_address' => '182.253.11.4',
            'user_agent' => 'Mozilla/5.0 Chrome/120.0',
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('settings.audit-logs.show', $log->id));
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'action',
            'risk_level',
            'risk_reason',
            'ip_address',
            'diffs' => [
                '*' => ['key', 'label', 'old', 'new', 'is_different'],
            ],
        ]);

        $diffs = collect($response->json('diffs'));
        $statusDiff = $diffs->firstWhere('key', 'status');
        $this->assertNotNull($statusDiff);
        $this->assertSame('completed', $statusDiff['old']);
        $this->assertSame('voided', $statusDiff['new']);
        $this->assertTrue($statusDiff['is_different']);
    }

    public function test_cross_tenant_isolation_on_audit_logs(): void
    {
        [$businessA, $userA] = $this->createBusinessWithUser();
        [$businessB, $userB] = $this->createBusinessWithUser();

        $logB = AuditLog::create([
            'business_id' => $businessB->id,
            'user_id' => $userB->id,
            'auditable_type' => PosOrder::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'updated',
            'risk_level' => AuditLog::RISK_HIGH,
            'risk_reason' => 'Void Kasir Tenant B',
            'created_at' => now(),
        ]);

        // User A tries to access Tenant B's audit log
        $this->actingAs($userA);
        Context::setBusiness($businessA);

        $response = $this->get(route('settings.audit-logs.show', $logB->id));
        $response->assertNotFound();
    }

    public function test_business_has_many_audit_logs_relation(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        $initialCount = $business->auditLogs()->count();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'auditable_type' => Customer::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'created',
            'risk_level' => AuditLog::RISK_LOW,
            'created_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $business->auditLogs());
        $this->assertSame($initialCount + 1, $business->auditLogs()->count());
        $this->assertTrue($business->auditLogs->every(fn (AuditLog $log) => $log->business_id === $business->id));
    }

    public function test_owner_can_export_business_audit_logs_as_csv(): void
    {
        [$business, $user] = $this->createBusinessWithUser();
        $this->actingAs($user);
        Context::setBusiness($business);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'auditable_type' => PosOrder::class,
            'auditable_id' => (string) \Illuminate\Support\Str::uuid(),
            'action' => 'updated',
            'risk_level' => AuditLog::RISK_HIGH,
            'risk_reason' => 'Pembatalan Struk Kasir',
            'notes' => 'Permintaan kasir 1',
            'ip_address' => '10.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->get(route('settings.audit-logs.export'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
    }
}
