<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Block;
use App\Models\Transaction;
use App\Services\BitcoinRpcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly BitcoinRpcService $rpc)
    {
    }

    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        return TransactionResource::collection(
            Transaction::query()
                ->whereNotNull('confirmed_at')
                ->orderByDesc('confirmed_at')
                ->paginate($perPage)
        );
    }

    public function show(string $txid): JsonResponse
    {
        $transaction = Transaction::query()->find($txid);

        if ($transaction) {
            return response()->json(['data' => new TransactionResource($transaction)]);
        }

        try {
            $raw = $this->rpc->getRawTransaction($txid, true);

            if (! is_array($raw)) {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            $transientTx = new Transaction([
                'txid' => $raw['txid'] ?? $txid,
                'block_hash' => $raw['blockhash'] ?? null,
                'block_height' => $raw['height'] ?? null,
                'confirmed_at' => null,
                'fee' => isset($raw['fee']) ? $this->btcToSat($raw['fee']) : null,
                'size' => (int) ($raw['size'] ?? 0),
                'vsize' => (int) ($raw['vsize'] ?? 0),
                'input_count' => count($raw['vin'] ?? []),
                'output_count' => count($raw['vout'] ?? []),
                'total_output_sat' => $this->sumOutputs($raw['vout'] ?? []),
                'raw' => $raw,
            ]);

            return response()->json(['data' => new TransactionResource($transientTx)]);
        } catch (Throwable) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }
    }

    public function byBlock(string $hashOrHeight)
    {
        $isHash = preg_match('/^[a-f0-9]{64}$/i', $hashOrHeight) === 1;

        $block = $isHash
            ? Block::query()->where('hash', strtolower($hashOrHeight))->first()
            : Block::query()->where('height', (int) $hashOrHeight)->first();

        if (! $block) {
            return response()->json(['message' => 'Block not found.'], 404);
        }

        return TransactionResource::collection(
            Transaction::query()
                ->where('block_hash', $block->hash)
                ->orderBy('created_at')
                ->paginate(100)
        );
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
