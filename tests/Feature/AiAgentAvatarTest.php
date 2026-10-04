<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiAgentAvatar;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiAgentAvatarTest extends TestCase
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
            'email' => 'owner_a_avatar@example.com',
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

        // 2. Setup Business B
        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner_b_avatar@example.com',
            'password' => 'password123',
        ]);

        $this->businessB = Business::create(['name' => 'Kedai Kopi B']);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);

        BusinessSubscription::create([
            'business_id' => $this->businessB->id,
            'plan_code' => BusinessSubscription::PLAN_PRESTIGE_ANNUAL,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'ai_tokens_monthly_allowance' => 100000,
            'ai_tokens_remaining' => 100000,
        ]);
    }

    public function test_user_can_fetch_agent_avatars_and_presets(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $response = $this->getJson(route('cooca-ai.agents.avatars'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'presets',
            'avatars',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('executive_male', $data['presets']);
        $this->assertArrayHasKey('cyber_innovator', $data['presets']);
        $this->assertArrayHasKey('ceo', $data['avatars']);
        $this->assertArrayHasKey('cfo', $data['avatars']);
        $this->assertArrayHasKey('inventory', $data['avatars']);
    }

    public function test_user_can_customize_ai_agent_avatar(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $payload = [
            'preset' => 'cyber_innovator',
            'custom_name' => 'Aria Prime AI',
            'suit_color' => '#06b6d4',
            'skin_tone' => '#94a3b8',
            'hair_color' => '#06b6d4',
            'accessory' => 'cyber_visor',
        ];

        $response = $this->postJson(route('cooca-ai.agents.avatar.update', ['role' => 'ceo']), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'role' => 'ceo',
            'avatar' => [
                'preset' => 'cyber_innovator',
                'custom_name' => 'Aria Prime AI',
                'suit_color' => '#06b6d4',
                'skin_tone' => '#94a3b8',
                'hair_color' => '#06b6d4',
                'accessory' => 'cyber_visor',
            ],
        ]);

        $this->assertDatabaseHas('ai_agent_avatars', [
            'business_id' => $this->businessA->id,
            'agent_role' => 'ceo',
            'custom_name' => 'Aria Prime AI',
            'preset' => 'cyber_innovator',
            'suit_color' => '#06b6d4',
            'accessory' => 'cyber_visor',
        ]);
    }

    public function test_avatar_customizations_are_strictly_isolated_per_tenant(): void
    {
        // Business A customizes CFO
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        $this->postJson(route('cooca-ai.agents.avatar.update', ['role' => 'cfo']), [
            'preset' => 'financial_strategist',
            'custom_name' => 'Finley Gold',
            'suit_color' => '#1e293b',
            'skin_tone' => '#f8d9b6',
            'hair_color' => '#d4af37',
            'accessory' => 'glasses',
        ])->assertOk();

        // Business B checks CFO avatar - should NOT have Business A's custom name
        $this->actingAs($this->ownerB);
        Context::setBusiness($this->businessB);

        $responseB = $this->getJson(route('cooca-ai.agents.avatars'));
        $responseB->assertOk();
        $avatarsB = $responseB->json('avatars');

        $this->assertNotEquals('Finley Gold', $avatarsB['cfo']['custom_name'] ?? null);
    }
}
