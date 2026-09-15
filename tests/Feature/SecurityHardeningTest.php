<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_requests_have_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_login_endpoint_has_throttling(): void
    {
        // 5 consecutive failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'wrong@example.com',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be throttled (HTTP 429 Too Many Requests)
        $throttledResponse = $this->post(route('login'), [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);
        $throttledResponse->assertStatus(429);
    }

    public function test_whatsapp_webhook_rejects_unauthorized_token(): void
    {
        config(['services.wa_server.token' => 'secure-worker-token-test']);

        // Empty / no token
        $response1 = $this->postJson('/api/v1/wa/webhook', [
            'sessionId' => 'test-session',
            'status' => 'CONNECTED',
        ]);
        $response1->assertStatus(401);

        // Invalid token
        $response2 = $this->postJson('/api/v1/wa/webhook', [
            'sessionId' => 'test-session',
            'status' => 'CONNECTED',
        ], ['Authorization' => 'Bearer wrong-token']);
        $response2->assertStatus(401);

        // Valid token
        $response3 = $this->postJson('/api/v1/wa/webhook', [
            'sessionId' => 'test-session',
            'status' => 'CONNECTED',
        ], ['Authorization' => 'Bearer secure-worker-token-test']);
        $response3->assertStatus(200);
    }
}
