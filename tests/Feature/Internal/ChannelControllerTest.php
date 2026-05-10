<?php

namespace Tests\Feature\Internal;

use Tests\TestCase;

class ChannelControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.stream_server.token', 'test-token');
    }

    public function test_channels_endpoint_requires_bearer_token(): void
    {
        $this->getJson('/api/internal/channels')
            ->assertStatus(401);
    }

    public function test_channels_endpoint_rejects_wrong_bearer_token(): void
    {
        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->getJson('/api/internal/channels')
            ->assertStatus(401);
    }

    public function test_logo_endpoint_requires_bearer_token(): void
    {
        $this->getJson('/api/internal/channels/some-slug/logo')
            ->assertStatus(401);
    }

    public function test_endpoints_500_when_token_unconfigured(): void
    {
        config()->set('services.stream_server.token', null);

        $this->withHeader('Authorization', 'Bearer anything')
            ->getJson('/api/internal/channels')
            ->assertStatus(500);
    }
}
