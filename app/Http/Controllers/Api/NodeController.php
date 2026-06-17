<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitcoinRpcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SyncBlockchainJob;

class NodeController extends Controller
{
    public function __construct(private readonly BitcoinRpcService $rpc)
    {
    }

    public function info(): JsonResponse
    {
        $blockchainInfo = $this->rpc->getBlockchainInfo();
        $networkInfo = $this->rpc->getNetworkInfo();

        return response()->json([
            'data' => [
                'chain' => $blockchainInfo['chain'] ?? null,
                'blocks' => $blockchainInfo['blocks'] ?? null,
                'headers' => $blockchainInfo['headers'] ?? null,
                'difficulty' => $blockchainInfo['difficulty'] ?? null,
                'network_active' => $networkInfo['networkactive'] ?? null,
                'version' => $networkInfo['version'] ?? null,
            ],
        ]);
    }

    public function mine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => 'required|string',
        ]);

        $address = $validated['address'];

        $cooldown = config('bitcoin.mining_cooldown', 120);
        $lockExpiresAt = Cache::get('node:mining:lock');

        if ($lockExpiresAt) {
            $remaining = $lockExpiresAt - now()->timestamp;
            if ($remaining > 0) {
                return response()->json([
                    'message' => 'Mining is on cooldown.',
                    'remaining_seconds' => $remaining,
                ], 429);
            }
        }

        // Call RPC to mine exactly 1 block
        $blockHashes = $this->rpc->generateToAddress(1, $address);

        // Sync immediately so blocks/transactions are stored in DB
        (new SyncBlockchainJob())->handle($this->rpc);

        // Store cooldown lock
        Cache::put('node:mining:lock', now()->timestamp + $cooldown, $cooldown);

        return response()->json([
            'message' => 'Blocks mined successfully.',
            'block_hashes' => $blockHashes,
        ]);
    }
}


