<?php

namespace Tests\Feature;

use App\Models\Block;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiningWorkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_mining_work_returns_hash_challenge(): void
    {
        config(['bitcoin.mining_target_prefix' => '0']);

        $previousHash = str_repeat('a', 64);

        $this->mockRpc(function ($mock) use ($previousHash): void {
            $mock->shouldReceive('getBlockCount')->once()->andReturn(10);
            $mock->shouldReceive('getBlockHash')->once()->with(10)->andReturn($previousHash);
        });

        $this->getJson('/api/mining/work?address=bcrt1qmineraddress')
            ->assertOk()
            ->assertJsonPath('data.height', 11)
            ->assertJsonPath('data.previous_block_hash', $previousHash)
            ->assertJsonPath('data.target_prefix', '0')
            ->assertJsonStructure([
                'data' => [
                    'job_id',
                    'challenge',
                    'payload_prefix',
                    'algorithm',
                    'expires_at',
                ],
            ]);
    }

    public function test_first_valid_solution_wins_and_duplicate_is_rejected(): void
    {
        config([
            'bitcoin.mining_target_prefix' => '0',
            'bitcoin.mining_reward_sats' => 5000000000,
        ]);

        $address = 'bcrt1qmineraddress';
        $previousHash = str_repeat('b', 64);
        $newHash = str_repeat('c', 64);

        Block::query()->create([
            'hash' => $previousHash,
            'height' => 10,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => [],
        ]);

        $this->mockRpc(function ($mock) use ($address, $previousHash, $newHash): void {
            $mock->shouldReceive('getBlockCount')->andReturn(10, 11);
            $mock->shouldReceive('getBlockHash')->with(10)->once()->andReturn($previousHash);
            $mock->shouldReceive('generateToAddress')->once()->with(1, $address)->andReturn([$newHash]);
            $mock->shouldReceive('getBlockHash')->with(11)->once()->andReturn($newHash);
            $mock->shouldReceive('getBlock')->with($newHash, 2)->once()->andReturn([
                'hash' => $newHash,
                'height' => 11,
                'time' => now()->timestamp,
                'size' => 120,
                'weight' => 480,
                'difficulty' => 1,
                'tx' => [
                    [
                        'txid' => str_repeat('d', 64),
                        'vin' => [],
                        'vout' => [['value' => 50.0]],
                    ],
                ],
            ]);
            $mock->shouldReceive('getRawMempool')->once()->with(true)->andReturn([]);
        });

        $work = $this->getJson('/api/mining/work?address='.$address)
            ->assertOk()
            ->json('data');

        [$nonce, $hash] = $this->findNonce($work['payload_prefix'], $work['target_prefix']);

        $this->postJson('/api/mining/submit', [
            'job_id' => $work['job_id'],
            'address' => $address,
            'nonce' => $nonce,
        ])
            ->assertOk()
            ->assertJsonPath('data.accepted', true)
            ->assertJsonPath('data.block_hash', $newHash)
            ->assertJsonPath('data.hash', $hash);

        $this->postJson('/api/mining/submit', [
            'job_id' => $work['job_id'],
            'address' => $address,
            'nonce' => $nonce,
        ])
            ->assertStatus(409)
            ->assertJsonPath('data.status', 'already_solved');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function findNonce(string $payloadPrefix, string $targetPrefix): array
    {
        for ($nonce = 0; $nonce < 100000; $nonce++) {
            $hash = hash('sha256', hash('sha256', $payloadPrefix.$nonce, true));
            if (str_starts_with($hash, $targetPrefix)) {
                return [(string) $nonce, $hash];
            }
        }

        $this->fail('No valid nonce found for test target.');
    }
}
