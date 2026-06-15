<?php

namespace Tests\Feature;

use App\Jobs\SyncBlockchainJob;
use App\Models\Block;
use App\Models\MempoolEntry;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncBlockchainJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_blockchain_job_inserts_blocks_transactions_and_refreshes_mempool(): void
    {
        $blockHash = str_repeat('a', 64);
        $confirmedTxid = str_repeat('b', 64);
        $unconfirmedTxid = str_repeat('c', 64);

        $this->mockRpc(function ($mock) use ($blockHash, $confirmedTxid, $unconfirmedTxid): void {
            $mock->shouldReceive('getBlockCount')->andReturn(0);
            $mock->shouldReceive('getBlockHash')->with(0)->andReturn($blockHash);
            $mock->shouldReceive('getBlock')->with($blockHash, 2)->andReturn([
                'hash' => $blockHash,
                'height' => 0,
                'time' => 1_700_000_000,
                'tx' => [
                    [
                        'txid' => $confirmedTxid,
                        'vin' => [[]],
                        'vout' => [
                            ['value' => 50.0],
                        ],
                    ],
                ],
                'size' => 250,
                'weight' => 1000,
                'difficulty' => 1.0,
            ]);
            $mock->shouldReceive('getRawMempool')->with(true)->andReturn([
                $confirmedTxid => [
                    'fees' => ['base' => 0.00001],
                    'vsize' => 100,
                    'depends' => [],
                    'time' => 1_700_000_100,
                ],
                $unconfirmedTxid => [
                    'fees' => ['base' => 0.00002],
                    'vsize' => 200,
                    'depends' => [$confirmedTxid],
                    'time' => 1_700_000_200,
                ],
            ]);
        });

        app(SyncBlockchainJob::class)->handle(app(\App\Services\BitcoinRpcService::class));

        $this->assertDatabaseHas('blocks', [
            'hash' => $blockHash,
            'height' => 0,
            'tx_count' => 1,
        ]);

        $this->assertDatabaseHas('transactions', [
            'txid' => $confirmedTxid,
            'block_hash' => $blockHash,
            'block_height' => 0,
            'total_output_sat' => 5_000_000_000,
        ]);

        $this->assertDatabaseMissing('mempool_entries', [
            'txid' => $confirmedTxid,
        ]);

        $this->assertDatabaseHas('mempool_entries', [
            'txid' => $unconfirmedTxid,
            'fee' => 2000,
            'vsize' => 200,
        ]);

        $this->assertSame(1, Block::query()->count());
        $this->assertSame(1, Transaction::query()->count());
        $this->assertSame(1, MempoolEntry::query()->count());
    }
}
