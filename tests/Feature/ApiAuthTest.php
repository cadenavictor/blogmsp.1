<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_503_when_key_not_configured(): void
    {
        config(['services.codex.api_key' => '']);

        $this->getJson('/api/ping')->assertStatus(503);
    }

    public function test_returns_401_without_key(): void
    {
        config(['services.codex.api_key' => 'secret-test-key']);

        $this->getJson('/api/ping')->assertStatus(401);
    }

    public function test_returns_401_with_wrong_key(): void
    {
        config(['services.codex.api_key' => 'secret-test-key']);

        $this->getJson('/api/ping', ['X-Api-Key' => 'nope'])->assertStatus(401);
    }

    public function test_accepts_valid_key_via_header(): void
    {
        config(['services.codex.api_key' => 'secret-test-key']);

        $this->getJson('/api/ping', ['X-Api-Key' => 'secret-test-key'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_accepts_valid_key_via_bearer(): void
    {
        config(['services.codex.api_key' => 'secret-test-key']);

        $this->getJson('/api/ping', ['Authorization' => 'Bearer secret-test-key'])
            ->assertOk();
    }
}
