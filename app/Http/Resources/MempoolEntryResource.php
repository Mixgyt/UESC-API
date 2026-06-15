<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MempoolEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'txid' => $this->txid,
            'fee_sat' => $this->fee,
            'vsize' => $this->vsize,
            'fee_rate_sat_vbyte' => $this->fee_rate,
            'depends' => $this->depends ?? [],
            'time' => $this->time?->toIso8601String(),
        ];
    }
}
