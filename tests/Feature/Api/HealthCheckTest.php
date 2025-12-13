<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HealthCheckTest extends TestCase
{
    /** @test */
    public function health_endpoint_returns_healthy_status()
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'timestamp',
                'app',
                'version',
                'environment',
                'database',
                'cache',
            ])
            ->assertJson([
                'status' => 'healthy',
            ]);
    }

    /** @test */
    public function ping_endpoint_returns_pong()
    {
        $response = $this->getJson('/api/ping');

        $response->assertOk()
            ->assertJson([
                'pong' => true,
            ]);
    }
}
