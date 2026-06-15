<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitcoinRpcService;
use Illuminate\Http\JsonResponse;

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
}
