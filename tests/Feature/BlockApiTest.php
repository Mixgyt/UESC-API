<?php

namespace Tests\Feature;

use App\Models\Block;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_are_public(): void
    {
        $this->getJson('/api/blocks')->assertOk();
    }

    public function test_api_responses_include_security_headers(): void
    {
        Block::query()->create([
            'hash' => str_repeat('e', 64),
            'height' => 1,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        $this->getJson('/api/blocks')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_get_blocks_returns_paginated_data(): void
    {
        Block::query()->create([
            'hash' => str_repeat('a', 64),
            'height' => 1,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => [['txid' => str_repeat('b', 64)]]],
        ]);

        $this->getJson('/api/blocks')
            ->assertOk()
            ->assertJsonPath('data.0.hash', str_repeat('a', 64))
            ->assertJsonMissingPath('data.0.raw');
    }

    public function test_get_blocks_latest_returns_highest_height(): void
    {
        Block::query()->create([
            'hash' => str_repeat('a', 64),
            'height' => 1,
            'time' => now()->subMinute(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        Block::query()->create([
            'hash' => str_repeat('c', 64),
            'height' => 2,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        $this->getJson('/api/blocks/latest')
            ->assertOk()
            ->assertJsonPath('data.height', 2);
    }

    public function test_get_block_by_height_and_hash(): void
    {
        $hash = str_repeat('d', 64);

        Block::query()->create([
            'hash' => $hash,
            'height' => 9,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        $this->getJson('/api/blocks/9')
            ->assertOk()
            ->assertJsonPath('data.hash', $hash);

        $this->getJson('/api/blocks/'.$hash)
            ->assertOk()
            ->assertJsonPath('data.height', 9);
    }
}
