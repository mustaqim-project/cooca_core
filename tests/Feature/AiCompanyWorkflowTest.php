<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiActionProposal;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiCompanyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner AI Enterprise',
            'email' => 'owner_ai@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'PT COOCA Inovasi Semesta',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'admin',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 1000,
            'ai_tokens_remaining' => 1000,
        ]);
    }

    public function test_ai_company_lobby_is_accessible(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));

        $response->assertStatus(200);
        $response->assertSee('Action Center');
    }

    public function test_ai_action_proposals_page_lists_items_and_filters(): void
    {
        AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'operations',
            'department' => 'inventory',
            'agent' => 'InventoryAgent',
            'tool' => 'inventory_adjust',
            'action_type' => 'adjust_stock_buffer',
            'title' => 'Naikkan Safety Stock Minyak Goreng',
            'description' => 'Mencegah stockout menjelang akhir pekan.',
            'reason' => 'Berdasarkan tren penjualan mingguan.',
            'idempotency_key' => 'idemp-test-1',
            'risk_level' => AiActionProposal::RISK_LOW,
            'status' => AiActionProposal::STATUS_PENDING,
            'payload' => ['quantity' => 20, 'unit_price' => 15000],
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.actions'));

        $response->assertStatus(200);
        $response->assertSee('Naikkan Safety Stock Minyak Goreng');
        $response->assertSee('Menunggu Persetujuan');
    }

    public function test_ai_action_proposal_approval_and_rejection_flow(): void
    {
        $proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'growth',
            'department' => 'marketing',
            'agent' => 'CrmAgent',
            'tool' => 'crm_discount',
            'action_type' => 'send_crm_discount',
            'title' => 'Voucher Diskon Pelanggan Churn',
            'description' => 'Kirim diskon 15% kepada 5 pelanggan at-risk.',
            'reason' => 'Segmentasi RFM menunjukkan 5 pelanggan at risk.',
            'idempotency_key' => 'idemp-test-2',
            'risk_level' => AiActionProposal::RISK_LOW,
            'status' => AiActionProposal::STATUS_PENDING,
            'payload' => ['discount_percent' => 15],
        ]);

        // 1. Revise action
        $reviseRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.revise', $proposal->id), [
                'payload' => ['discount_percent' => 20, 'notes' => 'Tingkatkan diskon jadi 20%']
            ]);

        $reviseRes->assertStatus(200);
        $reviseRes->assertJsonPath('success', true);
        $this->assertEquals(20, $proposal->fresh()->payload['discount_percent']);

        // 2. Reject action
        $rejectRes = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.reject', $proposal->id), [
                'reason' => 'Dibatalkan untuk penyesuaian kampanye bulanan.'
            ]);

        $rejectRes->assertStatus(200);
        $rejectRes->assertJsonPath('success', true);
        $this->assertEquals(AiActionProposal::STATUS_REJECTED, $proposal->fresh()->status);
    }

    public function test_legacy_ai_routes_redirect_permanently_to_canonical_cooca_ai(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/ai');

        $response->assertStatus(301);
        $response->assertRedirect(route('cooca-ai.index'));

        $responseActions = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/ai/actions');

        $responseActions->assertStatus(301);
        $responseActions->assertRedirect(route('cooca-ai.actions'));
    }

    public function test_ai_ask_requires_configured_provider_when_enforced(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.ask'), [
                'query' => 'Bagaimana estimasi profit & cashflow bulan ini?',
                'agent' => 'cfo',
                'enforce_provider_check' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('needs_provider', true);
        $response->assertJsonPath('redirect_url', route('cooca-ai.providers'));
    }

    public function test_ai_ask_works_with_configured_provider_and_routes_agent(): void
    {
        \App\Models\AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'openai',
            'api_key' => 'sk-test-mock-key-for-unit-test',
            'model' => 'gpt-4o-mini',
            'is_active' => true,
            'is_default' => true,
            'status' => 'connected',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.ask'), [
                'query' => 'Bagaimana estimasi profit & cashflow bulan ini?',
                'agent' => 'cfo',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
