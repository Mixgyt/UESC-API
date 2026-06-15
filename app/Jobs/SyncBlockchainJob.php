<?php

namespace App\Jobs;

use App\Models\Block;
use App\Models\MempoolEntry;
use App\Models\Transaction;
use App\Services\BitcoinRpcService;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SyncBlockchainJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $uniqueFor = 25;

    public function uniqueId(): string
    {
        return 'sync-blockchain-job';
    }

    public function uniqueVia(): Repository
    {
        return Cache::store('database');
    }

    public function handle(BitcoinRpcService $rpc): void
    {
        $nodeCount = $rpc->getBlockCount();
        $maxLocalHeight = Block::query()->max('height');
        $maxLocalHeight = $maxLocalHeight !== null ? (int) $maxLocalHeight : -1;

        for ($height = $maxLocalHeight + 1; $height <= $nodeCount; $height++) {
            $hash = $rpc->getBlockHash($height);
            $block = $rpc->getBlock($hash, 2);

            $blockModel = Block::query()->firstOrCreate(
                ['hash' => $hash],
                [
                    'height' => (int) ($block['height'] ?? $height),
                    'time' => Carbon::createFromTimestamp((int) ($block['time'] ?? now()->timestamp)),
                    'tx_count' => count($block['tx'] ?? []),
                    'size' => (int) ($block['size'] ?? 0),
                    'weight' => (int) ($block['weight'] ?? 0),
                    'difficulty' => (float) ($block['difficulty'] ?? 0),
                    'miner_reward' => $this->extractMinerReward($block),
                    'raw' => $block,
                ]
            );

            $transactionRows = [];

            foreach (($block['tx'] ?? []) as $tx) {
                if (! is_array($tx) || ! isset($tx['txid'])) {
                    continue;
                }

                $transactionRows[] = [
                    'txid' => (string) $tx['txid'],
                    'block_hash' => $blockModel->hash,
                    'block_height' => $blockModel->height,
                    'confirmed_at' => Carbon::createFromTimestamp((int) ($block['time'] ?? now()->timestamp)),
                    'fee' => isset($tx['fee']) ? $this->btcToSat($tx['fee']) : null,
                    'size' => (int) ($tx['size'] ?? 0),
                    'vsize' => (int) ($tx['vsize'] ?? 0),
                    'input_count' => count($tx['vin'] ?? []),
                    'output_count' => count($tx['vout'] ?? []),
                    'total_output_sat' => $this->sumOutputs($tx['vout'] ?? []),
                    'raw' => json_encode($tx, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($transactionRows !== []) {
                Transaction::query()->upsert(
                    $transactionRows,
                    ['txid'],
                    ['block_hash', 'block_height', 'confirmed_at', 'fee', 'size', 'vsize', 'input_count', 'output_count', 'total_output_sat', 'raw', 'updated_at']
                );
            }
        }

        $rawMempool = $rpc->getRawMempool(true);
        $rows = [];

        foreach ($rawMempool as $txid => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $feeSat = isset($entry['fees']['base']) ? $this->btcToSat($entry['fees']['base']) : 0;
            $vsize = (int) ($entry['vsize'] ?? 0);

            $rows[] = [
                'txid' => (string) $txid,
                'fee' => $feeSat,
                'vsize' => $vsize,
                'fee_rate' => $vsize > 0 ? round($feeSat / $vsize, 8) : 0,
                'depends' => json_encode($entry['depends'] ?? [], JSON_THROW_ON_ERROR),
                'time' => Carbon::createFromTimestamp((int) ($entry['time'] ?? now()->timestamp)),
                'raw' => json_encode($entry, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            MempoolEntry::query()->upsert(
                $rows,
                ['txid'],
                ['fee', 'vsize', 'fee_rate', 'depends', 'time', 'raw', 'updated_at']
            );
        }

        MempoolEntry::query()->whereIn('txid', Transaction::query()->select('txid'))->delete();
    }

    private function extractMinerReward(array $block): ?int
    {
        $coinbaseTx = $block['tx'][0] ?? null;

        if (! is_array($coinbaseTx)) {
            return null;
        }

        $firstOutput = $coinbaseTx['vout'][0]['value'] ?? null;

        if ($firstOutput === null) {
            return null;
        }

        return $this->btcToSat($firstOutput);
    }

    private function sumOutputs(array $outputs): int
    {
        $total = 0;

        foreach ($outputs as $output) {
            $total += isset($output['value']) ? $this->btcToSat($output['value']) : 0;
        }

        return $total;
    }

    private function btcToSat(mixed $btc): int
    {
        return (int) round(((float) $btc) * 100000000);
    }
}
