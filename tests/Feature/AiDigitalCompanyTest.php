<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Execution\AiActionExecutor;
use App\Domain\Ai\Orchestration\AiOrchestrator;
use App\Domain\Ai\Policy\AiActionPolicy;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Models\AiActionProposal;
use App\Models\AiProviderConfig;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiDigitalCompanyTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;
    private Business $businessA;
    private User $ownerB;
    private Business $businessB;
    private Product $productA;
    private Customer $customerA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Business A
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'owner_a@example.com',
            'password' => 'password123',
        ]);

        $this->businessA = Business::create(['name' => 'Kopi Nusantara A']);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 5000,
            'ai_tokens_remaining' => 5000,
        ]);

        $catA = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Minuman',
            'slug' => 'minuman-a',
        ]);

        $unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $catA->id,
            'output_unit_id' => $unitA->id,
            'code' => 'PRD-A1',
            'name' => 'Es Kopi Gula Aren',
            'base_cost' => 8000,
            'selling_price' => 20000,
            'is_active' => true,
        ]);

        $this->customerA = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pelanggan Setia A',
            'phone' => '08111111111',
        ]);

        // 2. Setup Business B (for Tenant Isolation testing)
        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner_b@example.com',
            'password' => 'password123',
        ]);

        $this->businessB = Business::create(['name' => 'Bengkel Sejahtera B']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    public function test_byoai_provider_stores_encrypted_key_and_never_leaks_in_json(): void
    {
        Context::setBusiness($this->businessA);

        $config = AiProviderConfig::create([
            'business_id' => $this->businessA->id,
            'provider' => 'openai',
            'api_key' => 'sk-test-super-secret-key-12345',
            'model' => 'gpt-4o-mini',
            'is_active' => true,
            'is_default' => true,
        ]);

        // Verify database raw value is encrypted (not plaintext)
        $rawInDb = DB::table('ai_provider_configs')->where('id', $config->id)->value('api_key');
        $this->assertNotEquals('sk-test-super-secret-key-12345', $rawInDb);
        $this->assertStringNotContainsString('sk-test-super-secret-key-12345', $rawInDb);

        // Verify model access auto-decrypts
        $this->assertEquals('sk-test-super-secret-key-12345', $config->api_key);

        // Verify JSON serialization hides the key completely
        $json = $config->toJson();
        $this->assertStringNotContainsString('sk-test-super-secret-key-12345', $json);
        $this->assertArrayNotHasKey('api_key', $config->toArray());

        // Verify masked representation
        $this->assertEquals('••••••••••••••••', $config->masked_api_key);
    }

    public function test_ai_orchestrator_runs_diagnosis_and_generates_action_proposal_and_history(): void
    {
        Context::setBusiness($this->businessA);

        $orchestrator = new AiOrchestrator();
        $result = $orchestrator->process($this->businessA, $this->ownerA, 'Kenapa omzet saya turun? Buatkan draf invoice untuk pelanggan.');

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['task_id']);
        $this->assertContains('business', $result['participating_agents']);
        $this->assertContains('sales', $result['participating_agents']);

        // Verify AiTask persisted
        $this->assertDatabaseHas('ai_tasks', [
            'id' => $result['task_id'],
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
        ]);

        // Verify Work History persisted
        $this->assertDatabaseHas('ai_work_histories', [
            'business_id' => $this->businessA->id,
            'ai_task_id' => $result['task_id'],
        ]);
    }

    public function test_human_approval_gate_prevents_premature_execution_until_owner_approves(): void
    {
        Context::setBusiness($this->businessA);

        // Create a pending action proposal
        $proposal = AiActionProposal::create([
            'business_id' => $this->businessA->id,
            'executive_role' => 'sales_director',
            'department' => 'sales',
            'agent' => 'sales',
            'tool' => 'DraftInvoiceProposal',
            'action_type' => 'create_invoice',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Terbitkan Faktur Kopi Aren',
            'description' => 'Menerbitkan faktur 10x Es Kopi Gula Aren.',
            'reason' => 'Permintaan transaksi.',
            'payload' => [
                'customer_id' => $this->customerA->id,
                'customer_name' => $this->customerA->name,
                'product_id' => $this->productA->id,
                'product_name' => $this->productA->name,
                'quantity' => 10,
                'unit_price' => 20000,
                'total_amount' => 200000,
            ],
            'estimated_cost' => 200000,
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-TEST-001',
            'created_by' => $this->ownerA->id,
        ]);

        // Verify invoice is NOT created yet in database
        $this->assertDatabaseCount('invoices', 0);

        // Owner approves proposal via web endpoint
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        $response = $this->postJson(route('ai.actions.approve', $proposal));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Proposal status is now COMPLETED
        $proposal->refresh();
        $this->assertEquals(AiActionProposal::STATUS_COMPLETED, $proposal->status);
        $this->assertNotNull($proposal->executed_at);

        // Invoice is NOW created in database
        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->businessA->id,
            'customer_id' => $this->customerA->id,
            'total_amount' => 200000,
        ]);

        // Immutable AuditLog created
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'ai_action_executed',
            'auditable_type' => AiActionProposal::class,
            'auditable_id' => $proposal->id,
        ]);
    }

    public function test_idempotency_guard_prevents_duplicate_execution_of_approved_action(): void
    {
        Context::setBusiness($this->businessA);

        $proposal = AiActionProposal::create([
            'business_id' => $this->businessA->id,
            'executive_role' => 'sales_director',
            'department' => 'sales',
            'agent' => 'sales',
            'tool' => 'DraftInvoiceProposal',
            'action_type' => 'create_invoice',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Terbitkan Faktur Kopi Aren Sekali',
            'description' => 'Menerbitkan faktur 5x Es Kopi Gula Aren.',
            'reason' => 'Permintaan transaksi.',
            'payload' => [
                'customer_id' => $this->customerA->id,
                'customer_name' => $this->customerA->name,
                'product_id' => $this->productA->id,
                'product_name' => $this->productA->name,
                'quantity' => 5,
                'unit_price' => 20000,
                'total_amount' => 100000,
            ],
            'estimated_cost' => 100000,
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-TEST-IDEMPOTENT',
            'created_by' => $this->ownerA->id,
        ]);

        $executor = new AiActionExecutor();

        // 1st Execution
        $firstResult = $executor->executeApprovedAction($this->businessA, $this->ownerA, $proposal);
        $this->assertTrue($firstResult['success']);
        $this->assertDatabaseCount('invoices', 1);

        // 2nd Execution attempt with the same proposal
        $secondResult = $executor->executeApprovedAction($this->businessA, $this->ownerA, $proposal);
        $this->assertTrue($secondResult['success']);
        $this->assertStringContainsString('Idempotent', $secondResult['message']);

        // Invoices count remains exactly 1 (no duplicate!)
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_strict_multi_tenant_isolation_prevents_tenant_b_from_accessing_tenant_a_proposals(): void
    {
        Context::setBusiness($this->businessA);

        $proposalA = AiActionProposal::create([
            'business_id' => $this->businessA->id,
            'executive_role' => 'sales_director',
            'department' => 'sales',
            'agent' => 'sales',
            'tool' => 'DraftInvoiceProposal',
            'action_type' => 'create_invoice',
            'risk_level' => AiActionProposal::RISK_HIGH,
            'title' => 'Proposal Bisnis A Rahasia',
            'description' => 'Data sensitif.',
            'reason' => 'Privat.',
            'payload' => [],
            'status' => AiActionProposal::STATUS_PENDING,
            'idempotency_key' => 'ACT-BIZ-A-001',
            'created_by' => $this->ownerA->id,
        ]);

        // Now Owner B tries to approve Business A's proposal
        $this->actingAs($this->ownerB);
        session(['active_business_id' => $this->businessB->id]);
        Context::setBusiness($this->businessB);

        $response = $this->postJson(route('ai.actions.approve', $proposalA));

        // Must reject with 404 (due to BusinessScope) or 404/403 security guard
        $this->assertContains($response->status(), [404, 403]);
        $this->assertEquals(AiActionProposal::STATUS_PENDING, $proposalA->fresh()->status);
    }

    public function test_ai_office_and_actions_views_render_successfully_with_apple_hig_bento_layout(): void
    {
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        // 1. AI Office Command Center (3 Distinct Offices Lobby)
        $officeResponse = $this->get(route('cooca-ai.index'));
        $officeResponse->assertStatus(200);
        $officeResponse->assertSee('Executive Office');
        $officeResponse->assertSee('Operations Office');
        $officeResponse->assertSee('Growth Office');
        $officeResponse->assertSee('AI CEO');
        $officeResponse->assertSee('AI COO');

        // 2. Action Center
        $actionsResponse = $this->get(route('ai.actions'));
        $actionsResponse->assertStatus(200);
        $actionsResponse->assertSee('Action Center');
        $actionsResponse->assertSee('Menunggu Persetujuan');

        // 3. AI Work History
        $historyResponse = $this->get(route('ai.history'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('AI Work History');

        // 4. AI Providers (BYOAI)
        $providersResponse = $this->get(route('ai.providers'));
        $providersResponse->assertStatus(200);
        $providersResponse->assertSee('AI Providers (BYOAI)');
        $providersResponse->assertSee('OpenAI');
        $providersResponse->assertSee('Google Gemini');
        $providersResponse->assertSee('Anthropic Claude');
        $providersResponse->assertSee('OpenRouter');
    }
}
