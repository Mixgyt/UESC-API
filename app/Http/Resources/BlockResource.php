<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rawTransactions = $this->raw['tx'] ?? [];

        $transactions = array_values(array_map(
            static fn (mixed $tx): mixed => is_array($tx) ? ($tx['txid'] ?? null) : $tx,
            is_array($rawTransactions) ? $rawTransactions : []
        ));

        return [
            'hash' => $this->hash,
            'height' => $this->height,
            'time' => $this->time?->toIso8601String(),
            'tx_count' => $this->tx_count,
            'size' => $this->size,
            'weight' => $this->weight,
            'difficulty' => $this->difficulty,
            'miner_reward_sat' => $this->miner_reward,
            'transactions' => array_values(array_filter($transactions, static fn (mixed $txid): bool => is_string($txid))),
        ];
    }
}
