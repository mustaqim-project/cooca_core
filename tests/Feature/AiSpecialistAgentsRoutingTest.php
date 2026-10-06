<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\CompanyHierarchy;
use App\Domain\Ai\Organization\Department;
use App\Domain\Ai\Organization\ExecutiveRole;
use App\Domain\Ai\Tools\AiToolRegistry;
use App\Domain\Ai\Tools\GetAttendanceSummaryTool;
use App\Models\AiProviderConfig;
use App\Models\AiTask;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiSpecialistAgentsRoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Kedai Kopi',
            'email' => 'owner_kopi@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create(['name' => 'Kedai Kopi Nusantara']);
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PRESTIGE_ANNUAL,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'ai_tokens_monthly_allowance' => 100000,
            'ai_tokens_remaining' => 100000,
        ]);

        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'gemini',
            'model' => 'gemini-1.5-flash',
            'api_key' => 'test-mock-api-key',
            'is_active' => true,
        ]);
    }

    public function test_all_12_specialist_agents_and_6_executives_route_correctly(): void
    {
        $expectedMappings = [
            'business' => ['dept' => 'executive', 'lead' => 'ceo', 'role' => AgentRole::BUSINESS],
            'sales' => ['dept' => 'sales', 'lead' => 'sales_director', 'role' => AgentRole::SALES],
            'customer' => ['dept' => 'sales', 'lead' => 'sales_director', 'role' => AgentRole::CUSTOMER],
            'inventory' => ['dept' => 'operations', 'lead' => 'coo', 'role' => AgentRole::INVENTORY],
            'purchasing' => ['dept' => 'operations', 'lead' => 'coo', 'role' => AgentRole::PURCHASING],
            'marketplace' => ['dept' => 'operations', 'lead' => 'coo', 'role' => AgentRole::MARKETPLACE],
            'finance' => ['dept' => 'finance', 'lead' => 'cfo', 'role' => AgentRole::FINANCE],
            'reporting' => ['dept' => 'finance', 'lead' => 'cfo', 'role' => AgentRole::REPORTING],
            'marketing' => ['dept' => 'marketing', 'lead' => 'cmo', 'role' => AgentRole::MARKETING],
            'content' => ['dept' => 'marketing', 'lead' => 'cmo', 'role' => AgentRole::CONTENT],
            'social_media' => ['dept' => 'marketing', 'lead' => 'cmo', 'role' => AgentRole::SOCIAL_MEDIA],
            'hr' => ['dept' => 'people', 'lead' => 'hr_lead', 'role' => AgentRole::HR],
        ];

        foreach ($expectedMappings as $slug => $meta) {
            $routing = CompanyHierarchy::routeTopicToTeam('Audit performa harian', null, $slug);

            $this->assertEquals($meta['dept'], $routing['team'], "Department mismatch for {$slug}");
            $this->assertEquals($meta['lead'], $routing['executive_lead'], "Lead mismatch for {$slug}");
            $this->assertSame($meta['role'], $routing['primary_agent'], "Primary agent mismatch for {$slug}");
            $this->assertEquals($slug, $routing['agent_slugs'][0], "First agent slug should be targeted agent for {$slug}");
        }

        // Test executives
        $executives = ['ceo', 'coo', 'cfo', 'cmo', 'sales_director', 'hr_lead'];
        foreach ($executives as $execSlug) {
            $routing = CompanyHierarchy::routeTopicToTeam('Koordinasi Dewan Direksi', null, $execSlug);
            $this->assertTrue($routing['is_executive'], "Executive flag should be true for {$execSlug}");
            $this->assertEquals($execSlug, $routing['executive_lead'], "Executive lead mismatch for {$execSlug}");
        }
    }

    public function test_ai_tool_registry_maps_tools_for_all_specialists(): void
    {
        $registry = new AiToolRegistry();

        // Marketplace agent has sales, stock, and top product tools
        $marketplaceTools = $registry->getToolsForAgent(AgentRole::MARKETPLACE);
        $this->assertArrayHasKey('GetSalesSummary', $marketplaceTools);
        $this->assertArrayHasKey('GetStockLevels', $marketplaceTools);
        $this->assertArrayHasKey('GetTopProducts', $marketplaceTools);

        // Reporting agent has sales, financial, top product, and stock tools
        $reportingTools = $registry->getToolsForAgent(AgentRole::REPORTING);
        $this->assertArrayHasKey('GetSalesSummary', $reportingTools);
        $this->assertArrayHasKey('GetFinancialHealth', $reportingTools);
        $this->assertArrayHasKey('GetTopProducts', $reportingTools);
        $this->assertArrayHasKey('GetStockLevels', $reportingTools);

        // Customer agent has customer and sales summary tools
        $customerTools = $registry->getToolsForAgent(AgentRole::CUSTOMER);
        $this->assertArrayHasKey('GetCustomerSummary', $customerTools);
        $this->assertArrayHasKey('GetSalesSummary', $customerTools);

        // HR agent has attendance summary tool
        $hrTools = $registry->getToolsForAgent(AgentRole::HR);
        $this->assertArrayHasKey('GetAttendanceSummary', $hrTools);
    }

    public function test_get_attendance_summary_tool_queries_real_attendance_records(): void
    {
        $staff1 = User::create(['name' => 'Staf Barista 1', 'email' => 'staff1@example.com', 'password' => 'secret']);
        $staff2 = User::create(['name' => 'Staf Barista 2', 'email' => 'staff2@example.com', 'password' => 'secret']);

        $this->business->users()->attach([
            $staff1->id => ['id' => (string) Str::uuid(), 'role' => 'staff'],
            $staff2->id => ['id' => (string) Str::uuid(), 'role' => 'staff'],
        ]);

        Attendance::create([
            'business_id' => $this->business->id,
            'user_id' => $staff1->id,
            'date' => now()->toDateString(),
            'status' => Attendance::STATUS_PRESENT,
            'clock_in_at' => now()->setTime(8, 0),
        ]);

        Attendance::create([
            'business_id' => $this->business->id,
            'user_id' => $staff2->id,
            'date' => now()->toDateString(),
            'status' => Attendance::STATUS_LATE,
            'clock_in_at' => now()->setTime(8, 30),
            'late_minutes' => 30,
        ]);

        $tool = new GetAttendanceSummaryTool();
        $result = $tool->execute($this->business, $this->owner);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, $result['total_staff']);
        $this->assertEquals(2, $result['present_today']);
        $this->assertEquals(1, $result['late_today']);
        $this->assertNotEmpty($result['today_roster']);
    }

    public function test_ask_endpoint_records_targeted_specialist_agent_in_ai_task(): void
    {
        $this->actingAs($this->owner);

        $response = $this->postJson(route('cooca-ai.ask'), [
            'query' => 'Sinkronkan katalog harga dan stok untuk etalase marketplace.',
            'agent' => 'marketplace',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $latestTask = AiTask::where('business_id', $this->business->id)->latest()->first();
        $this->assertNotNull($latestTask);
        $this->assertEquals('marketplace', $latestTask->agent, 'AiTask must record the targeted specialist agent');
        $this->assertEquals('operations', $latestTask->department);
        $this->assertEquals('coo', $latestTask->executive_role);
    }
}
