<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NodeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_node_info_is_public(): void
    {
        $this->mockRpc(function ($mock): void {
            $mock->shouldReceive('getBlockchainInfo')->andReturn([
                'chain' => 'regtest',
                'blocks' => 10,
                'headers' => 10,
                'difficulty' => 1.0,
            ]);
            $mock->shouldReceive('getNetworkInfo')->andReturn([
                'networkactive' => true,
                'version' => 250000,
            ]);
        });

        $this->getJson('/api/node/info')->assertOk();
    }

    public function test_get_node_info_returns_expected_structure(): void
    {
        $this->mockRpc(function ($mock): void {
            $mock->shouldReceive('getBlockchainInfo')->andReturn([
                'chain' => 'regtest',
                'blocks' => 10,
                'headers' => 10,
                'difficulty' => 1.0,
            ]);
            $mock->shouldReceive('getNetworkInfo')->andReturn([
                'networkactive' => true,
                'version' => 250000,
            ]);
        });

        $this->getJson('/api/node/info')
            ->assertOk()
            ->assertJsonPath('data.chain', 'regtest')
            ->assertJsonPath('data.blocks', 10)
            ->assertJsonPath('data.network_active', true)
            ->assertJsonPath('data.version', 250000);
    }
}
