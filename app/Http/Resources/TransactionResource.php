<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fee = $this->fee;
        $vsize = (int) ($this->vsize ?? 0);
        $feeRate = ($fee !== null && $vsize > 0) ? round(((int) $fee) / $vsize, 8) : null;

        return [
            'txid' => $this->txid,
            'status' => $this->confirmed_at || $this->block_hash ? 'confirmed' : 'unconfirmed',
            'fee_sat' => $fee,
            'fee_rate_sat_vbyte' => $feeRate,
            'size' => $this->size,
            'vsize' => $this->vsize,
            'input_count' => $this->input_count,
            'output_count' => $this->output_count,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'block_height' => $this->block_height,
            'block_hash' => $this->block_hash,
            'inputs' => $this->formatInputs(),
            'outputs' => $this->formatOutputs(),
        ];
    }

    private function formatInputs(): array
    {
        $vin = $this->raw['vin'] ?? [];

        if (!is_array($vin)) {
            return [];
        }

        return array_values(array_map(static function (array $input): array {
            return [
                'txid' => $input['txid'] ?? null,
                'vout' => $input['vout'] ?? null,
                'value_sat' => isset($input['value']) ? self::btcToSat($input['value']) : null,
            ];
        }, $vin));
    }

    private function formatOutputs(): array
    {
        $vout = $this->raw['vout'] ?? [];

        if (!is_array($vout)) {
            return [];
        }

        return array_values(array_map(static function (array $output): array {
            $address = $output['scriptPubKey']['address']
                ?? $output['scriptPubKey']['addresses'][0]
                ?? null;

            return [
                'address' => $address,
                'value_sat' => isset($output['value']) ? self::btcToSat($output['value']) : 0,
            ];
        }, $vout));
    }

    private static function btcToSat(mixed $btc): int
    {
        return (int) round(((float) $btc) * 100000000);
    }
}
