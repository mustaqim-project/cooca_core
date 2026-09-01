<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SecurityAndRateLimitTest extends TestCase
{
    public function test_api_responses_contain_security_headers(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-XSS-Protection', '1; mode=block')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_public_auth_endpoints_have_rate_limiting_headers(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);

        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || $response->headers->has('X-Ratelimit-Limit'),
            'Response is missing RateLimit headers'
        );
    }
}
