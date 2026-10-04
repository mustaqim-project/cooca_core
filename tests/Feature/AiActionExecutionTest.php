<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Execution\AiActionExecutor;
use App\Models\AiActionProposal;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Expense;
use App\Models\PosOrder;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiActionExecutionTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private AiActionExecutor $executor;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Hendrawan Roaster',
            'email' => 'hendrawan@mahakaryacoffee.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Mahakarya Artisan Roastery & Coffee',
            'currency' => 'IDR',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 50000,
            'ai_tokens_remaining' => 50000,
        ]);

        $this->executor = new AiActionExecutor();
    }

    public function test_can_execute_cost_structure_audit_proposal(): void
    {
        // Seed expense
        Expense::create([
            'business_id' => $this->business->id,
            'expense_number' => 'EXP-2026-10-001',
            'expense_date' => now()->toDateString(),
            'category' => 'rent',
            'amount' => 15000000,
            'description' => 'Sewa Roastery Senopati',
        ]);

        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'cfo',
            'department' => 'finance',
            'agent' => 'finance',
            'tool' => 'AuditCostStructureProposal',
            'action_type' => 'cost_structure_audit',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Audit Struktur Pengeluaran Operasional',
            'description' => 'Evaluasi menyeluruh beban sewa dan opex terhadap pendapatan kasir.',
            'reason' => 'Rasio beban operasional mencapai 8.86x di awal bulan.',
            'payload' => [
                'period' => 'October 2026',
                'current_revenue' => 1693600,
                'current_expenses' => 15000000,
                'efficiency_ratio' => 8.86,
                'required_revenue_for_breakeven' => 7500000,
            ],
            'estimated_cost' => 0,
            'status' => AiActionProposal::STATUS_APPROVED,
            'idempotency_key' => 'ACT-TEST-COST-001',
            'created_by' => $this->owner->id,
        ]);

        $result = $this->executor->executeApprovedAction($this->business, $this->owner, $proposal);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Audit Struktur Pengeluaran Operasional', $result['message']);

        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
        $this->assertNotNull($fresh->executed_at);
        $this->assertSame('audit_completed', $fresh->result['status']);
        $this->assertStringStartsWith('AUD-COST-', $fresh->result['audit_ref']);
        $this->assertNotEmpty($fresh->result['findings']);
        $this->assertNotEmpty($fresh->result['action_plan']);

        // Assert AuditLog was created
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'ai_action_executed',
            'auditable_id' => $proposal->id,
        ]);
    }

    public function test_can_execute_revenue_acceleration_proposal(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'cmo',
            'department' => 'growth',
            'agent' => 'marketing',
            'tool' => 'SalesAccelerationProposal',
            'action_type' => 'revenue_acceleration',
            'risk_level' => AiActionProposal::RISK_MEDIUM,
            'title' => 'Kampanye Akselerasi Penjualan Beans & Kopi',
            'description' => 'Meningkatkan volume transaksi harian roastery.',
            'reason' => 'Target omzet harian Rp 1.100.000.',
            'payload' => [
                'target_daily_revenue' => 1100000,
                'target_daily_transactions' => 21,
                'campaign_channels' => ['whatsapp_broadcast', 'in_store_promo'],
                'duration_days' => 30,
            ],
            'estimated_cost' => 0,
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-TEST-REV-001',
            'created_by' => $this->owner->id,
        ]);

        $result = $this->executor->executeApprovedAction($this->business, $this->owner, $proposal);

        $this->assertTrue($result['success']);
        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
        $this->assertSame('activated', $fresh->result['status']);
        $this->assertStringStartsWith('CMP-REV-', $fresh->result['campaign_code']);
    }

    public function test_can_execute_system_maintenance_proposal(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'coo',
            'department' => 'operations',
            'agent' => 'operations',
            'tool' => 'SystemMaintenanceTool',
            'action_type' => 'system_maintenance',
            'risk_level' => AiActionProposal::RISK_LOW,
            'title' => 'Verifikasi dan Pemeliharaan Integritas Sistem',
            'description' => 'Pemeriksaan performa database dan laporan penjualan produk.',
            'reason' => 'Audit kesehatan query sistem berkala.',
            'payload' => [
                'priority' => 'HIGH',
            ],
            'estimated_cost' => 0,
            'status' => AiActionProposal::STATUS_APPROVED,
            'idempotency_key' => 'ACT-TEST-MNT-001',
            'created_by' => $this->owner->id,
        ]);

        $result = $this->executor->executeApprovedAction($this->business, $this->owner, $proposal);

        $this->assertTrue($result['success']);
        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
        $this->assertSame('completed', $fresh->result['status']);
        $this->assertStringStartsWith('MNT-', $fresh->result['maintenance_ref']);
    }

    public function test_can_execute_custom_strategic_directive_fallback(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'ceo',
            'department' => 'executive',
            'agent' => 'ceo',
            'tool' => 'StrategicPolicyDirective',
            'action_type' => 'expand_b2b_hotel_supply',
            'risk_level' => AiActionProposal::RISK_MEDIUM,
            'title' => 'Inisiatif Ekspansi Pasokan Kopi ke Jaringan Hotel',
            'description' => 'Target kemitraan pasokan 200kg biji kopi per bulan.',
            'reason' => 'Diversifikasi pendapatan tetap untuk mengamankan biaya sewa.',
            'payload' => [
                'target_hotels' => 5,
                'minimum_monthly_volume_kg' => 200,
            ],
            'estimated_cost' => 0,
            'status' => AiActionProposal::STATUS_APPROVED,
            'idempotency_key' => 'ACT-TEST-CUSTOM-001',
            'created_by' => $this->owner->id,
        ]);

        $result = $this->executor->executeApprovedAction($this->business, $this->owner, $proposal);

        $this->assertTrue($result['success']);
        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
        $this->assertSame('implemented', $fresh->result['status']);
        $this->assertStringStartsWith('DIR-', $fresh->result['directive_ref']);
    }

    public function test_web_controller_approve_action_executes_successfully(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'cfo',
            'department' => 'finance',
            'agent' => 'finance',
            'tool' => 'AuditCostStructureProposal',
            'action_type' => 'cost_structure_audit',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Audit Beban Operasional Roastery',
            'description' => 'Audit pengeluaran bulanan.',
            'reason' => 'Pemeriksaan CFO.',
            'payload' => [
                'period' => 'October 2026',
            ],
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-WEB-TEST-001',
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson("/cooca-ai/actions/{$proposal->id}/approve");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
    }

    public function test_can_execute_purchase_order_with_whatsapp_dispatch_and_owner_notification(): void
    {
        $this->owner->update(['phone' => '081299887766']);

        $supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Koperasi Tani Kopi Gayo Highland',
            'phone' => '081388990011',
            'contact_person' => 'Tengku Zulkarnain',
            'address' => 'Aceh Tengah',
        ]);

        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'coo',
            'department' => 'operations',
            'agent' => 'purchasing',
            'tool' => 'ReorderLowStockTool',
            'action_type' => 'create_purchase_order',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Pengadaan Biji Kopi Mentah (Green Beans Gayo)',
            'description' => 'Restock bahan baku green beans gayo 50 kg.',
            'reason' => 'Stok di gudang di bawah batas minimum.',
            'payload' => [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_phone' => $supplier->phone,
                'quantity' => 50,
                'unit_price' => 110000,
                'product_name' => 'Green Beans Arabica Gayo Washed',
            ],
            'estimated_cost' => 5500000,
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-TEST-PO-001',
            'created_by' => $this->owner->id,
        ]);

        $result = $this->executor->executeApprovedAction($this->business, $this->owner, $proposal);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('purchase_orders', [
            'business_id' => $this->business->id,
            'supplier_id' => $supplier->id,
            'total_amount' => 5500000,
        ]);

        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_COMPLETED, $fresh->status);
        $this->assertNotNull($fresh->result['whatsapp_url']);
        $this->assertStringContainsString('wa.me/6281388990011', $fresh->result['whatsapp_url']);
        $this->assertStringContainsString('PURCHASE ORDER RESMI', $fresh->result['whatsapp_message']);
        $this->assertNotNull($fresh->result['notification_to_owner']);
        $this->assertSame('notified', $fresh->result['notification_to_owner']['status']);
    }

    public function test_can_revise_proposal_parameters_before_approval(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'coo',
            'department' => 'operations',
            'agent' => 'purchasing',
            'tool' => 'ReorderLowStockTool',
            'action_type' => 'create_purchase_order',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Restock Green Beans',
            'description' => 'Restock awal 50 kg.',
            'reason' => 'Stok kritis.',
            'payload' => [
                'quantity' => 50,
                'unit_price' => 100000,
            ],
            'estimated_cost' => 5000000,
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-TEST-REV-PO-001',
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson("/cooca-ai/actions/{$proposal->id}/revise", [
                'payload' => [
                    'quantity' => 75,
                    'unit_price' => 95000,
                    'notes' => 'Tingkatkan ke 75kg untuk promo akhir bulan.',
                ],
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        $fresh = $proposal->fresh();
        $this->assertSame(AiActionProposal::STATUS_PENDING, $fresh->status);
        $this->assertEquals(75 * 95000, $fresh->estimated_cost);
        $this->assertEquals(75, $fresh->payload['quantity']);
        $this->assertEquals(95000, $fresh->payload['unit_price']);
        $this->assertStringContainsString('Direvisi oleh pemilik', $fresh->reason);
    }
}
