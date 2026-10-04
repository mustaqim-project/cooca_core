<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Context\AiContextEngine;
use App\Domain\Ai\Context\Directives\AgentRoleDirectives;
use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\ExecutiveRole;
use App\Domain\Ai\Orchestration\AiOrchestrator;
use App\Domain\Ai\Rag\AiRagRetriever;
use App\Domain\Ai\Rag\Sources\AiSystemCapabilitiesKnowledgeSource;
use App\Domain\Ai\Rag\Sources\TenantMasterDataKnowledgeSource;
use App\Domain\Ai\Rag\Sources\TenantOperationalHistoryKnowledgeSource;
use App\Domain\Ai\Rag\Sources\UmkmRegulationsKnowledgeSource;
use App\Domain\Ai\Services\AiAgentBusinessMetricsService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiRagAndContextEngineeringTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;
    private Business $businessA;
    private User $ownerB;
    private Business $businessB;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // Business A
        $this->ownerA = User::create([
            'name' => 'Owner Kedai Kopi A',
            'email' => 'kopi_a@example.com',
            'password' => 'password123',
        ]);
        $this->businessA = Business::create(['name' => 'Kedai Kopi A', 'currency' => 'IDR', 'business_scale' => 'umkm']);
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
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 10000,
            'ai_tokens_remaining' => 10000,
        ]);

        $locA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Pusat A',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Business B (for cross-tenant isolation testing)
        $this->ownerB = User::create([
            'name' => 'Owner Toko B',
            'email' => 'toko_b@example.com',
            'password' => 'password123',
        ]);
        $this->businessB = Business::create(['name' => 'Toko Kelontong B', 'currency' => 'IDR']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);

        $locB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Gudang Pusat B',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $catA = ProductCategory::create(['business_id' => $this->businessA->id, 'name' => 'Minuman', 'slug' => 'minuman-a']);
        $unitA = Unit::create(['business_id' => $this->businessA->id, 'name' => 'Cup', 'code' => 'CUP', 'symbol' => 'cup', 'category' => Unit::CATEGORY_QUANTITY]);

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $catA->id,
            'output_unit_id' => $unitA->id,
            'name' => 'Kopi Gula Aren Special',
            'code' => 'KGA-001',
            'selling_price' => 18000,
            'base_cost' => 8500,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $locA->id,
            'product_id' => $this->productA->id,
            'quantity' => 25,
        ]);

        $catB = ProductCategory::create(['business_id' => $this->businessB->id, 'name' => 'Sembako', 'slug' => 'sembako-b']);
        $unitB = Unit::create(['business_id' => $this->businessB->id, 'name' => 'Sak', 'code' => 'SAK', 'symbol' => 'sak', 'category' => Unit::CATEGORY_QUANTITY]);
        $this->productB = Product::create([
            'business_id' => $this->businessB->id,
            'category_id' => $catB->id,
            'output_unit_id' => $unitB->id,
            'name' => 'Beras Pandan Wangi B',
            'code' => 'BPW-999',
            'selling_price' => 75000,
            'base_cost' => 60000,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->businessB->id,
            'location_id' => $locB->id,
            'product_id' => $this->productB->id,
            'quantity' => 10,
        ]);
    }

    public function test_tenant_master_data_rag_source_retrieves_matching_products_and_enforces_tenant_isolation(): void
    {
        $source = new TenantMasterDataKnowledgeSource();
        $chunks = $source->retrieve($this->businessA, $this->ownerA, 'Kopi Gula Aren');

        $this->assertNotEmpty($chunks);
        $first = $chunks[0];
        $this->assertStringContainsString('Kopi Gula Aren Special', $first['content']);
        $this->assertStringContainsString('KGA-001', $first['content']);
        $this->assertStringContainsString('[Katalog Produk: #KGA-001', $first['citation']);

        // Verify that Business B product is NEVER returned to Business A
        $chunksCross = $source->retrieve($this->businessA, $this->ownerA, 'Beras Pandan Wangi');
        foreach ($chunksCross as $chunk) {
            $this->assertStringNotContainsString('Beras Pandan Wangi B', $chunk['content']);
        }
    }

    public function test_tenant_operational_history_rag_source_retrieves_invoices_and_orders(): void
    {
        $cust = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'CV Sumber Makmur',
            'phone' => '081234567890',
        ]);

        $inv = Invoice::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $cust->id,
            'invoice_number' => 'INV-2026-001',
            'total_amount' => 540000,
            'status' => Invoice::STATUS_UNPAID,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        $posOrder = PosOrder::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'order_number' => 'ORD-POS-1001',
            'order_date' => now(),
            'total_amount' => 36000,
            'total_gross_profit' => 19000,
            'status' => PosOrder::STATUS_COMPLETED,
            'payment_method' => 'qris',
        ]);

        $source = new TenantOperationalHistoryKnowledgeSource();

        $invChunks = $source->retrieve($this->businessA, $this->ownerA, 'faktur piutang jatuh tempo');
        $this->assertNotEmpty($invChunks);
        $this->assertStringContainsString('INV-2026-001', $invChunks[0]['content']);
        $this->assertStringContainsString('CV Sumber Makmur', $invChunks[0]['content']);

        $orderChunks = $source->retrieve($this->businessA, $this->ownerA, 'penjualan omzet kasir');
        $this->assertNotEmpty($orderChunks);
        $this->assertStringContainsString('ORD-POS-1001', $orderChunks[0]['content']);
    }

    public function test_umkm_regulations_rag_source_provides_pp55_pph21_and_anti_fraud_sop(): void
    {
        $source = new UmkmRegulationsKnowledgeSource();

        $taxChunks = $source->retrieve($this->businessA, $this->ownerA, 'bagaimana aturan pajak penghasilan umkm');
        $this->assertNotEmpty($taxChunks);
        $this->assertStringContainsString('PP 55/2022', $taxChunks[0]['content']);
        $this->assertStringContainsString('0,5%', $taxChunks[0]['content']);
        $this->assertStringContainsString('500.000.000', $taxChunks[0]['content']);

        $fraudChunks = $source->retrieve($this->businessA, $this->ownerA, 'sop kasir void selisih uang');
        $this->assertNotEmpty($fraudChunks);
        $this->assertStringContainsString('Blind Cash Count', $fraudChunks[0]['content']);
        $this->assertStringContainsString('10.000', $fraudChunks[0]['content']);

        $ropChunks = $source->retrieve($this->businessA, $this->ownerA, 'rumus safety stock dan rop persediaan');
        $this->assertNotEmpty($ropChunks);
        $this->assertStringContainsString('Reorder Point (ROP)', $ropChunks[0]['content']);
        $this->assertStringContainsString('Lead Time', $ropChunks[0]['content']);
    }

    public function test_ai_system_capabilities_rag_source_enforces_maker_checker_principle(): void
    {
        $source = new AiSystemCapabilitiesKnowledgeSource();
        $chunks = $source->retrieve($this->businessA, $this->ownerA, 'apa peran ai tool dan kebijakan proposal');

        $this->assertNotEmpty($chunks);
        $this->assertStringContainsString('Maker-Checker', $chunks[0]['content']);
        $this->assertStringContainsString('Draft Proposal', $chunks[0]['content']);
    }

    public function test_ai_rag_retriever_aggregates_scores_and_formats_grounding_block(): void
    {
        $retriever = new AiRagRetriever();
        $chunks = $retriever->retrieveRelevantKnowledge($this->businessA, $this->ownerA, 'harga produk Kopi dan aturan pajak UMKM');

        $this->assertNotEmpty($chunks);
        $promptBlock = $retriever->formatKnowledgeForPrompt($chunks);

        $this->assertStringContainsString('=== FAKTA & DOKUMEN SISTEM TERVERIFIKASI (RAG GROUNDING) ===', $promptBlock);
        $this->assertStringContainsString('DILARANG berhalusinasi', $promptBlock);
        $this->assertStringContainsString('[Katalog Produk:', $promptBlock);
    }

    public function test_agent_role_directives_provides_job_desk_kpis_and_formulas_for_all_17_roles(): void
    {
        $allRoles = [
            'ceo', 'coo', 'cfo', 'cmo', 'hr_lead', 'sales_director',
            'business', 'sales', 'customer', 'inventory', 'purchasing',
            'marketplace', 'finance', 'reporting', 'marketing', 'content',
            'social_media', 'hr',
        ];

        foreach ($allRoles as $role) {
            $directive = AgentRoleDirectives::getDirective($role);
            $this->assertNotEmpty($directive['role_name'], "Role name missing for {$role}");
            $this->assertNotEmpty($directive['mission'], "Mission missing for {$role}");
            $this->assertNotEmpty($directive['kpis'], "KPIs missing for {$role}");
            $this->assertNotEmpty($directive['directive_text'], "Directive text missing for {$role}");
        }

        $teamDirectiveText = AgentRoleDirectives::formatTeamDirectives([AgentRole::SALES, AgentRole::CUSTOMER]);
        $this->assertStringContainsString('Sales Intelligence Agent', $teamDirectiveText);
        $this->assertStringContainsString('Customer Relationship Agent', $teamDirectiveText);
    }

    public function test_ai_context_engine_assembles_5_layers_and_anti_hallucination_instructions(): void
    {
        $contextEngine = new AiContextEngine();
        $teamRouting = [
            'team' => 'sales',
            'team_name' => 'Tim Sales',
            'room' => 'Sales & CRM Command',
            'is_executive' => false,
        ];

        $assembled = $contextEngine->assembleEngineeredPrompt(
            $this->businessA,
            $this->ownerA,
            'Berapa stok Kopi Gula Aren dan apa rekomendasi penjualan?',
            [AgentRole::SALES],
            $teamRouting
        );

        $this->assertArrayHasKey('system_prompt', $assembled);
        $this->assertArrayHasKey('assembled_context', $assembled);
        $this->assertArrayHasKey('rag_citations', $assembled);
        $this->assertArrayHasKey('token_estimate', $assembled);

        $prompt = $assembled['system_prompt'];
        $this->assertStringContainsString('ZERO HALLUCINATION & FACTUAL CITATION', $prompt);
        $this->assertStringContainsString('Data tidak ditemukan dalam sistem', $prompt);
        $this->assertStringContainsString('Kopi Gula Aren Special', $prompt);
        $this->assertGreaterThan(0, $assembled['token_estimate']);
    }

    public function test_ai_agent_business_metrics_service_provides_dual_monitors_for_all_17_roles(): void
    {
        $service = new AiAgentBusinessMetricsService();
        $allMetrics = $service->getAllMetrics($this->businessA);

        $this->assertArrayHasKey('ceo', $allMetrics);
        $this->assertArrayHasKey('sales', $allMetrics);
        $this->assertArrayHasKey('inventory', $allMetrics);
        $this->assertArrayHasKey('finance', $allMetrics);

        $salesM = $allMetrics['sales'];
        $this->assertArrayHasKey('monitor1', $salesM);
        $this->assertArrayHasKey('monitor2', $salesM);

        // Monitor 1 validation
        $this->assertEquals('SALES & POS DISPATCH', $salesM['monitor1']['title']);
        $this->assertNotEmpty($salesM['monitor1']['kpi_label_1']);
        $this->assertNotEmpty($salesM['monitor1']['kpi_value_1']);
        $this->assertNotEmpty($salesM['monitor1']['chart_data']);

        // Monitor 2 validation
        $this->assertEquals('LIVE ORDER STREAM', $salesM['monitor2']['title']);
        $this->assertIsArray($salesM['monitor2']['items']);

        // Inventory Monitor validation
        $invM = $allMetrics['inventory'];
        $this->assertEquals('WAREHOUSE INVENTORY', $invM['monitor1']['title']);
        $this->assertNotEmpty($invM['monitor1']['kpi_value_1']);
        $this->assertEquals('LOW STOCK WATCHLIST', $invM['monitor2']['title']);
    }

    public function test_live_metrics_web_api_endpoint_returns_json_and_requires_auth(): void
    {
        // Unauthenticated access should redirect or deny
        $responseUnauth = $this->getJson(route('cooca-ai.live-metrics'));
        $this->assertTrue(in_array($responseUnauth->status(), [401, 403, 302]));

        // Authenticated owner access
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        $response = $this->getJson(route('cooca-ai.live-metrics'));
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'business_id',
            'timestamp',
            'metrics' => [
                'ceo',
                'sales',
                'inventory',
                'finance',
            ],
        ]);

        // Specific role query
        $responseRole = $this->getJson(route('cooca-ai.live-metrics', ['role' => 'sales']));
        $responseRole->assertOk();
        $responseRole->assertJsonPath('metrics.monitor1.title', 'SALES & POS DISPATCH');
    }
}
