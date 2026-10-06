<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiProviderConfig;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Roastery Test',
            'email' => 'owner.test@cooca.id',
            'password' => 'password123',
            'phone' => '081299990001',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Artisan Test Roastery',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 50000,
            'ai_tokens_remaining' => 50000,
        ]);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->owner->id)
            ->first();

        Context::setBusiness($this->business, $membership);
    }

    public function test_providers_page_renders_cleanly_with_zero_plaintext_key_exposure(): void
    {
        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'anthropic',
            'api_key' => 'sk-ant-secret-test-key-12345',
            'model' => 'claude-haiku-4-5-20251001',
            'is_active' => true,
            'is_default' => true,
            'status' => 'connected',
        ]);

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));

        $response->assertOk();
        $response->assertSee('AI Providers (BYOAI)');
        $response->assertSee('Anthropic Claude');
        $response->assertSee('DEFAULT');
        $response->assertSee(__('ai.providers.key_placeholder_saved'));
        $response->assertDontSee('sk-ant-secret-test-key-12345');
    }

    public function test_test_provider_endpoint_measures_latency_and_saves_connected_status(): void
    {
        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id' => 'msg_mock_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'OK']],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
            ], 200),
            'https://api.anthropic.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'claude-haiku-4-5-20251001', 'display_name' => 'Claude Haiku 4.5', 'line' => 'haiku'],
                    ['id' => 'claude-sonnet-4-5-20250929', 'display_name' => 'Claude Sonnet 4.5', 'line' => 'sonnet'],
                ],
            ], 200),
        ]);

        $config = AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'anthropic',
            'api_key' => 'sk-ant-test-key',
            'model' => 'claude-haiku-4-5-20251001',
            'is_active' => true,
            'is_default' => true,
            'status' => 'untested',
        ]);

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.test'), [
                'provider' => 'anthropic',
                'model' => 'claude-haiku-4-5-20251001',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'model' => 'claude-haiku-4-5-20251001',
            'status' => 'connected',
        ]);

        $config->refresh();
        $this->assertSame('connected', $config->status);
        $this->assertNotNull($config->tested_at);
        $this->assertNull($config->last_error);
        $this->assertArrayHasKey('available_models', $config->settings ?? []);
    }

    public function test_detect_models_endpoint_discovers_models_dynamically(): void
    {
        Http::fake([
            'https://api.anthropic.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'claude-haiku-4-5-20251001', 'display_name' => 'Claude Haiku 4.5', 'line' => 'haiku'],
                    ['id' => 'claude-sonnet-4-5-20250929', 'display_name' => 'Claude Sonnet 4.5', 'line' => 'sonnet'],
                    ['id' => 'claude-opus-4-5-20251101', 'display_name' => 'Claude Opus 4.5', 'line' => 'opus'],
                ],
            ], 200),
        ]);

        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'anthropic',
            'api_key' => 'sk-ant-test-key',
            'model' => 'claude-haiku-4-5-20251001',
        ]);

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.detect-models'), [
                'provider' => 'anthropic',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'provider' => 'anthropic',
        ]);
        $this->assertCount(3, $response->json('models'));
    }

    public function test_store_provider_encrypts_key_and_manages_default_flag(): void
    {
        // Existing default
        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider' => 'gemini',
            'api_key' => 'gemini-key',
            'model' => 'gemini-2.5-flash',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('cooca-ai.providers.store'), [
                'provider' => 'anthropic',
                'api_key' => 'sk-ant-new-key-value',
                'model' => 'claude-haiku-4-5-20251001',
                'is_active' => '1',
                'is_default' => '1',
            ]);

        $response->assertSessionHas('success');

        $anthropicConfig = AiProviderConfig::where('business_id', $this->business->id)
            ->where('provider', 'anthropic')
            ->firstOrFail();

        $this->assertTrue($anthropicConfig->is_default);
        $this->assertSame('sk-ant-new-key-value', $anthropicConfig->api_key); // Auto-decrypted by cast

        // Verify other provider is no longer default
        $geminiConfig = AiProviderConfig::where('business_id', $this->business->id)
            ->where('provider', 'gemini')
            ->firstOrFail();
        $this->assertFalse($geminiConfig->is_default);
    }
}
