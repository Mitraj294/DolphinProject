<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    /**
     * Test that the health endpoint returns 200 with ok status.
     */
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok'
        ]);
    }
}
