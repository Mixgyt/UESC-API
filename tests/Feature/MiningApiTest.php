<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MiningApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::query()->create([
            'name' => 'Tester',
            'email' => 'tester@regtest.local',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_post_mine_requires_authentication(): void
    {
        $this->postJson('/api/node/mine', [
            'address' => 'bcrt1qexampleaddress',
        ])->assertStatus(401);
    }

    public function test_post_mine_requires_address(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/node/mine', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['address']);
    }

    public function test_post_mine_executes_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->mockRpc(function ($mock): void {
            $mock->shouldReceive('generateToAddress')
                ->once()
                ->with(1, 'bcrt1qexampleaddress')
                ->andReturn(['00000000aaaa1111']);
            
            // SyncBlockchainJob mocks
            $mock->shouldReceive('getBlockCount')->andReturn(1);
            $mock->shouldReceive('getBlockHash')->with(0)->andReturn('00000000aaaa1111');
            $mock->shouldReceive('getBlockHash')->with(1)->andReturn('00000000aaaa1111');
            $mock->shouldReceive('getBlock')->andReturn([
                'hash' => '00000000aaaa1111',
                'height' => 1,
                'time' => time(),
                'size' => 100,
                'weight' => 400,
                'difficulty' => 1.0,
                'tx' => [],
            ]);
            $mock->shouldReceive('getRawMempool')->andReturn([]);
        });

        $this->withToken($token)
            ->postJson('/api/node/mine', [
                'address' => 'bcrt1qexampleaddress',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Blocks mined successfully.',
                'block_hashes' => ['00000000aaaa1111'],
            ]);
    }

    public function test_post_mine_respects_cooldown(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->mockRpc(function ($mock): void {
            $mock->shouldReceive('generateToAddress')
                ->once()
                ->with(1, 'bcrt1qexampleaddress')
                ->andReturn(['00000000aaaa1111']);

            // SyncBlockchainJob mocks
            $mock->shouldReceive('getBlockCount')->andReturn(1);
            $mock->shouldReceive('getBlockHash')->andReturn('00000000aaaa1111');
            $mock->shouldReceive('getBlock')->andReturn([
                'hash' => '00000000aaaa1111',
                'height' => 1,
                'time' => time(),
                'size' => 100,
                'weight' => 400,
                'difficulty' => 1.0,
                'tx' => [],
            ]);
            $mock->shouldReceive('getRawMempool')->andReturn([]);
        });

        config(['bitcoin.mining_cooldown' => 120]);

        // First call - passes
        $this->withToken($token)
            ->postJson('/api/node/mine', [
                'address' => 'bcrt1qexampleaddress',
            ])
            ->assertOk();

        // Second call - throttled by cooldown
        $this->withToken($token)
            ->postJson('/api/node/mine', [
                'address' => 'bcrt1qexampleaddress',
            ])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Mining is on cooldown.');
    }
}

