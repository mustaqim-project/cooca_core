<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\AiOffice;
use App\Models\AiActionProposal;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiOfficeEnvironmentsTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;
    private Business $businessA;
    private User $ownerB;
    private Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Business A
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'owner_a_offices@example.com',
            'password' => 'password123',
        ]);

        $this->businessA = Business::create(['name' => 'Mahakarya Roastery A']);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_PRESTIGE_ANNUAL,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'ai_tokens_monthly_allowance' => 100000,
            'ai_tokens_remaining' => 100000,
        ]);

        $catA = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kopi Artisan',
            'slug' => 'kopi-artisan-a',
        ]);

        $unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'BAG',
            'name' => 'Bag',
            'symbol' => 'bag',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $catA->id,
            'output_unit_id' => $unitA->id,
            'code' => 'PRD-A101',
            'name' => 'Gayo Natural Single Origin',
            'base_cost' => 45000,
            'selling_price' => 95000,
            'is_active' => true,
        ]);

        Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        AiWorkHistory::create([
            'business_id' => $this->businessA->id,
            'session_title' => 'Executive Audit Q4 Strategy',
            'executive_summary' => 'Analisis komprehensif profit margin dan efisiensi belanja modal.',
            'participating_agents' => ['business', 'finance', 'cfo'],
            'insights_count' => 3,
            'actions_count' => 1,
            'recorded_at' => now(),
        ]);

        AiWorkHistory::create([
            'business_id' => $this->businessA->id,
            'session_title' => 'Optimasi Rantai Pasokan & Gudang',
            'executive_summary' => 'Restocking biji kopi sangrai dan evaluasi supplier packaging.',
            'participating_agents' => ['inventory', 'purchasing'],
            'insights_count' => 2,
            'actions_count' => 1,
            'recorded_at' => now(),
        ]);

        AiWorkHistory::create([
            'business_id' => $this->businessA->id,
            'session_title' => 'Kampanye Social Media & Follow-up Pelanggan',
            'executive_summary' => 'Peluncuran promo Nitro Cold Brew dan re-engagement 200 pelanggan.',
            'participating_agents' => ['marketing', 'sales', 'social_media'],
            'insights_count' => 4,
            'actions_count' => 2,
            'recorded_at' => now(),
        ]);

        // 2. Setup Business B (for Tenant Isolation testing)
        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner_b_offices@example.com',
            'password' => 'password123',
        ]);

        $this->businessB = Business::create(['name' => 'Warung Kopi B']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);

        BusinessSubscription::create([
            'business_id' => $this->businessB->id,
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 15000,
            'ai_tokens_remaining' => 15000,
        ]);
    }

    public function test_user_can_access_main_ai_lobby(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get(route('cooca-ai.index'));

        $response->assertStatus(200);
        $response->assertViewIs('app.ai.lobby');
        $response->assertViewHasAll([
            'business',
            'user',
            'officesStats',
            'companyTotals',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider',
        ]);

        $response->assertSee('Executive Office');
        $response->assertSee('Operations Office');
        $response->assertSee('Growth Office');
        $response->assertSee('Lobi Utama');
        $response->assertSee('Virtual Office Floor');
        $response->assertSee('2D Blueprint');
        $response->assertSee('ATRIUM CENTRAL');
    }

    public function test_user_can_access_executive_office(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get(route('cooca-ai.office.executive'));
        file_put_contents('scratch/rendered_page.html', $response->getContent());

        $response->assertStatus(200);
        $response->assertViewIs('app.ai.offices.executive');
        $response->assertViewHasAll([
            'business',
            'user',
            'office',
            'officeStats',
            'financialHealth',
            'salesSummary',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider',
        ]);

        $response->assertSee('AI CEO');
        $response->assertSee('AI CFO');
        $response->assertSee('Business Agent');
        $response->assertSee('Finance Agent');
        $response->assertSee('Reporting Agent');
        $response->assertSee('Executive Audit Q4 Strategy');
        $response->assertSee('Virtual Office Floor');
        $response->assertSee('COOCA BOARDROOM');
    }

    public function test_user_can_access_operations_office(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get(route('cooca-ai.office.operations'));

        $response->assertStatus(200);
        $response->assertViewIs('app.ai.offices.operations');
        $response->assertViewHasAll([
            'business',
            'user',
            'office',
            'officeStats',
            'stockLevels',
            'operationalMetrics',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider',
        ]);

        $response->assertSee('AI COO');
        $response->assertSee('Inventory Agent');
        $response->assertSee('Purchasing Agent');
        $response->assertSee('Marketplace Agent');
        $response->assertSee('Optimasi Rantai Pasokan & Gudang');
        $response->assertSee('Virtual Office Floor');
        $response->assertSee('OPERATIONS RADAR');
    }

    public function test_user_can_access_growth_office(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get(route('cooca-ai.office.growth'));

        $response->assertStatus(200);
        $response->assertViewIs('app.ai.offices.growth');
        $response->assertViewHasAll([
            'business',
            'user',
            'office',
            'officeStats',
            'customerSummary',
            'topProducts',
            'growthMetrics',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider',
        ]);

        $response->assertSee('AI CMO');
        $response->assertSee('Sales Director');
        $response->assertSee('Marketing Agent');
        $response->assertSee('Content Agent');
        $response->assertSee('Social Media Agent');
        $response->assertSee('Sales Agent');
        $response->assertSee('Customer Agent');
        $response->assertSee('Kampanye Social Media & Follow-up Pelanggan');
        $response->assertSee('Virtual Office Floor');
        $response->assertSee('GROWTH LAB');
    }

    public function test_legacy_ai_office_url_redirects_to_cooca_ai_lobby(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get('/ai');

        $response->assertRedirect(route('cooca-ai.index'));
    }

    public function test_tenant_isolation_in_office_environments(): void
    {
        // Create pending proposal in Business A under Inventory Agent
        AiActionProposal::create([
            'business_id' => $this->businessA->id,
            'executive_role' => 'coo',
            'department' => 'operations',
            'agent' => AgentRole::INVENTORY->value,
            'tool' => 'draft_purchase_order_proposal',
            'title' => 'PO Rahasia Bisnis A',
            'description' => 'Pembelian biji kopi rahasia 50kg',
            'reason' => 'Stok menipis di bawah titik pemesanan ulang',
            'action_type' => 'create_purchase_order',
            'payload' => ['supplier_id' => 'supp-1'],
            'risk_level' => 'HIGH',
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-' . Str::random(12),
        ]);

        // Login as Owner B
        $this->actingAs($this->ownerB);
        Context::setBusiness($this->businessB);

        $response = $this->get(route('cooca-ai.office.operations'));

        $response->assertStatus(200);
        $response->assertDontSee('PO Rahasia Bisnis A');

        $opsStatsB = AiOffice::OPERATIONS->getOfficeStats($this->businessB);
        $this->assertEquals(0, $opsStatsB['pending_approvals_count']);
    }

    public function test_office_stats_calculation_reflects_real_backend_state(): void
    {
        // 1. Initially operations office has 0 pending approvals
        $statsInitial = AiOffice::OPERATIONS->getOfficeStats($this->businessA);
        $this->assertEquals(0, $statsInitial['pending_approvals_count']);

        // 2. Add an action proposal under Purchasing Agent
        AiActionProposal::create([
            'business_id' => $this->businessA->id,
            'executive_role' => 'coo',
            'department' => 'operations',
            'agent' => AgentRole::PURCHASING->value,
            'tool' => 'draft_purchase_order_proposal',
            'title' => 'Pengadaan Gula Aren 100kg',
            'description' => 'Stok gula aren tersisa 2kg',
            'reason' => 'Pengadaan rutin bahan baku kritis',
            'action_type' => 'create_purchase_order',
            'payload' => ['supplier_id' => 'supp-gula'],
            'risk_level' => 'MEDIUM',
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-' . Str::random(12),
        ]);

        // 3. Stats must now reflect 1 pending approval and agent status WAITING_APPROVAL
        $statsUpdated = AiOffice::OPERATIONS->getOfficeStats($this->businessA);
        $this->assertEquals(1, $statsUpdated['pending_approvals_count']);
        $this->assertEquals('WAITING_APPROVAL', $statsUpdated['agent_statuses']['purchasing']['status']);

        // 4. Add a running task under Content Agent in Growth Office
        AiTask::create([
            'business_id' => $this->businessA->id,
            'agent' => AgentRole::CONTENT->value,
            'input' => 'Menyusun caption promosi kopi gayo',
            'status' => AiTask::STATUS_RUNNING,
        ]);

        $growthStats = AiOffice::GROWTH->getOfficeStats($this->businessA);
        $this->assertEquals(1, $growthStats['active_tasks_count']);
        $this->assertEquals('WORKING', $growthStats['agent_statuses']['content']['status']);
    }

    public function test_3d_virtual_office_engine_and_viewport_renders_with_facilities(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->get(route('cooca-ai.office.executive'));

        $response->assertStatus(200);
        $response->assertSee('3D Virtual Office Floor');
        $response->assertSee('cooca-3d-office-viewport-executive');
        $response->assertSee('virtual-office-3d.js');
        $response->assertSee('three.min.js');
        $response->assertSee('OrbitControls.js');
        $response->assertSee('Pantry');
        $response->assertSee('Direksi');
        $response->assertSee('WEBGL 3D 60FPS');
    }
}

