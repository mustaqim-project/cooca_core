<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthCheckTest extends TestCase
{
    public function test_health_check_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'Universal HPP Calculator Engine',
                'version' => '1.0.0',
            ])
            ->assertJsonStructure([
                'status',
                'service',
                'timestamp',
                'version',
            ]);
    }
}
