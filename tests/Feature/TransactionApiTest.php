<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_are_public(): void
    {
        $this->getJson('/api/transactions')->assertOk();
    }

    public function test_get_transaction_by_txid_returns_expected_fields(): void
    {
        Transaction::query()->create([
            'txid' => str_repeat('a', 64),
            'block_hash' => null,
            'block_height' => null,
            'confirmed_at' => null,
            'fee' => 1000,
            'size' => 200,
            'vsize' => 180,
            'input_count' => 1,
            'output_count' => 1,
            'total_output_sat' => 5000,
            'raw' => [
                'vin' => [['txid' => str_repeat('f', 64), 'vout' => 0, 'value' => 0.0001]],
                'vout' => [['value' => 0.00009, 'scriptPubKey' => ['address' => 'bcrt1qtest']]],
            ],
        ]);

        $this->getJson('/api/transactions/'.str_repeat('a', 64))
            ->assertOk()
            ->assertJsonPath('data.txid', str_repeat('a', 64))
            ->assertJsonPath('data.fee_sat', 1000)
            ->assertJsonPath('data.outputs.0.value_sat', 9000);
    }

    public function test_get_transaction_not_found_returns_404(): void
    {
        $this->mockRpc(function ($mock): void {
            $mock->shouldReceive('getRawTransaction')->andThrow(new \RuntimeException('Not found'));
        });

        $this->getJson('/api/transactions/'.str_repeat('0', 64))
            ->assertStatus(404);
    }

    public function test_get_transaction_uses_rpc_when_not_in_database(): void
    {
        $txid = str_repeat('f', 64);

        $this->mockRpc(function ($mock) use ($txid): void {
            $mock->shouldReceive('getRawTransaction')
                ->with($txid, true)
                ->andReturn([
                    'txid' => $txid,
                    'vin' => [
                        ['txid' => str_repeat('e', 64), 'vout' => 1, 'value' => 0.0002],
                    ],
                    'vout' => [
                        ['value' => 0.00019, 'scriptPubKey' => ['address' => 'bcrt1qtest0000000000000000000000000000000']],
                    ],
                    'size' => 220,
                    'vsize' => 180,
                    'fee' => 0.00001,
                ]);
        });

        $this->getJson('/api/transactions/'.$txid)
            ->assertOk()
            ->assertJsonPath('data.txid', $txid)
            ->assertJsonPath('data.status', 'unconfirmed')
            ->assertJsonPath('data.fee_sat', 1000)
            ->assertJsonPath('data.inputs.0.value_sat', 20000)
            ->assertJsonPath('data.outputs.0.value_sat', 19000);
    }

    public function test_get_transactions_by_block_returns_only_block_transactions(): void
    {
        $block = Block::query()->create([
            'hash' => str_repeat('b', 64),
            'height' => 3,
            'time' => now(),
            'tx_count' => 2,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        $otherBlock = Block::query()->create([
            'hash' => str_repeat('c', 64),
            'height' => 8,
            'time' => now(),
            'tx_count' => 1,
            'size' => 100,
            'weight' => 400,
            'difficulty' => 1,
            'miner_reward' => 5000000000,
            'raw' => ['tx' => []],
        ]);

        Transaction::query()->create([
            'txid' => str_repeat('1', 64),
            'block_hash' => $block->hash,
            'block_height' => $block->height,
            'confirmed_at' => now(),
            'fee' => 100,
            'size' => 200,
            'vsize' => 100,
            'input_count' => 1,
            'output_count' => 1,
            'total_output_sat' => 100,
            'raw' => ['vin' => [], 'vout' => []],
        ]);

        Transaction::query()->create([
            'txid' => str_repeat('2', 64),
            'block_hash' => $otherBlock->hash,
            'block_height' => $otherBlock->height,
            'confirmed_at' => now(),
            'fee' => 100,
            'size' => 200,
            'vsize' => 100,
            'input_count' => 1,
            'output_count' => 1,
            'total_output_sat' => 100,
            'raw' => ['vin' => [], 'vout' => []],
        ]);

        $this->getJson('/api/blocks/3/transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.txid', str_repeat('1', 64));
    }
}
