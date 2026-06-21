<?php

namespace App\Services;

use App\Jobs\SyncBlockchainJob;
use App\Models\MiningJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MiningWorkService
{
    public function __construct(private readonly BitcoinRpcService $rpc)
    {
    }

    public function currentWorkForAddress(string $address): array
    {
        $job = $this->currentOpenJob();

        return $this->workPayload($job, $address);
    }

    public function submitSolution(string $jobId, string $address, string $nonce): array
    {
        $result = DB::transaction(function () use ($jobId, $address, $nonce): array {
            /** @var MiningJob|null $job */
            $job = MiningJob::query()->whereKey($jobId)->lockForUpdate()->first();

            if (! $job) {
                return [
                    'accepted' => false,
                    'status' => 'not_found',
                    'message' => 'Mining job not found.',
                ];
            }

            if ($job->status === 'solved') {
                return [
                    'accepted' => false,
                    'status' => 'already_solved',
                    'message' => 'This block was already mined by another user.',
                    'winner_address' => $job->winner_address,
                    'block_hash' => $job->block_hash,
                ];
            }

            if ($job->expires_at->isPast()) {
                $job->forceFill(['status' => 'expired'])->save();

                return [
                    'accepted' => false,
                    'status' => 'expired',
                    'message' => 'Mining job expired. Request a new block.',
                ];
            }

            $hash = $this->hashForNonce($job, $address, $nonce);
            if (! str_starts_with($hash, strtolower($job->target_prefix))) {
                return [
                    'accepted' => false,
                    'status' => 'invalid_solution',
                    'message' => 'Submitted nonce does not satisfy the target.',
                    'hash' => $hash,
                    'target_prefix' => $job->target_prefix,
                ];
            }

            $job->forceFill([
                'status' => 'solved',
                'winner_address' => $address,
                'winning_nonce' => $nonce,
                'winning_hash' => $hash,
                'reward_sats' => (int) config('bitcoin.mining_reward_sats'),
                'solved_at' => now(),
            ])->save();

            return [
                'accepted' => true,
                'status' => 'accepted',
                'message' => 'Valid proof of work accepted.',
                'job_id' => $job->id,
                'address' => $address,
                'nonce' => $nonce,
                'hash' => $hash,
                'target_prefix' => $job->target_prefix,
                'reward_sats' => $job->reward_sats,
            ];
        });

        if (! ($result['accepted'] ?? false)) {
            return $result;
        }

        return $this->settleAcceptedSolution($result);
    }

    private function currentOpenJob(): MiningJob
    {
        $targetPrefix = strtolower((string) config('bitcoin.mining_target_prefix', '00000'));
        $height = $this->rpc->getBlockCount() + 1;
        $previousBlockHash = $this->rpc->getBlockHash($height - 1);

        /** @var MiningJob|null $job */
        $job = MiningJob::query()
            ->where('status', 'open')
            ->where('height', $height)
            ->where('previous_block_hash', $previousBlockHash)
            ->where('target_prefix', $targetPrefix)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($job) {
            return $job;
        }

        MiningJob::query()
            ->where('status', 'open')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        return MiningJob::query()->create([
            'id' => (string) Str::uuid(),
            'height' => $height,
            'previous_block_hash' => $previousBlockHash,
            'challenge' => bin2hex(random_bytes(16)),
            'target_prefix' => $targetPrefix,
            'status' => 'open',
            'expires_at' => now()->addSeconds((int) config('bitcoin.mining_job_ttl', 180)),
        ]);
    }

    private function settleAcceptedSolution(array $result): array
    {
        $blockHashes = $this->rpc->generateToAddress(1, $result['address']);

        (new SyncBlockchainJob())->handle($this->rpc);

        $blockHash = $blockHashes[0] ?? null;
        MiningJob::query()
            ->whereKey($result['job_id'])
            ->update(['block_hash' => $blockHash]);

        $result['block_hash'] = $blockHash;
        $result['block_hashes'] = $blockHashes;
        $result['message'] = 'Block mined and reward sent to winning address.';

        return $result;
    }

    private function workPayload(MiningJob $job, string $address): array
    {
        return [
            'job_id' => $job->id,
            'height' => $job->height,
            'previous_block_hash' => $job->previous_block_hash,
            'challenge' => $job->challenge,
            'target_prefix' => $job->target_prefix,
            'payload_prefix' => $this->payloadPrefix($job, $address),
            'algorithm' => 'double_sha256_hex(payload_prefix + nonce)',
            'reward_sats' => (int) config('bitcoin.mining_reward_sats'),
            'expires_at' => $job->expires_at?->toIso8601String(),
        ];
    }

    private function hashForNonce(MiningJob $job, string $address, string $nonce): string
    {
        return hash('sha256', hash('sha256', $this->payloadPrefix($job, $address).$nonce, true));
    }

    private function payloadPrefix(MiningJob $job, string $address): string
    {
        return implode('|', [
            'viper-pow-v1',
            $job->id,
            $job->height,
            $job->previous_block_hash,
            $job->challenge,
            $address,
            '',
        ]);
    }
}
