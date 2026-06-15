<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MempoolEntryResource;
use App\Models\MempoolEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MempoolController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'fee_rate');
        $order = strtolower((string) $request->query('order', 'desc'));

        if (! in_array($sort, ['fee_rate', 'time'], true)) {
            $sort = 'fee_rate';
        }

        if (! in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        return MempoolEntryResource::collection(
            MempoolEntry::query()->orderBy($sort, $order)->paginate($perPage)
        );
    }

    public function show(string $txid): JsonResponse
    {
        $entry = MempoolEntry::query()->find($txid);

        if (! $entry) {
            return response()->json(['message' => 'Mempool transaction not found.'], 404);
        }

        return response()->json(['data' => new MempoolEntryResource($entry)]);
    }

    public function summary(): JsonResponse
    {
        $stats = MempoolEntry::query()
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(vsize), 0) as total_vsize')
            ->selectRaw('COALESCE(MIN(fee_rate), 0) as fee_min_sat_vbyte')
            ->selectRaw('COALESCE(MAX(fee_rate), 0) as fee_max_sat_vbyte')
            ->selectRaw('COALESCE(AVG(fee_rate), 0) as fee_avg_sat_vbyte')
            ->selectRaw('COALESCE(SUM(fee), 0) as total_fees_sat')
            ->first();

        $data = $stats?->toArray() ?? [];
        $data['tx_count'] = (int) ($data['tx_count'] ?? 0);
        $data['total_vsize'] = (int) ($data['total_vsize'] ?? 0);
        $data['total_size_vbytes'] = $data['total_vsize'];
        $data['fee_min_sat_vbyte'] = (float) ($data['fee_min_sat_vbyte'] ?? 0);
        $data['fee_max_sat_vbyte'] = (float) ($data['fee_max_sat_vbyte'] ?? 0);
        $data['fee_avg_sat_vbyte'] = (float) ($data['fee_avg_sat_vbyte'] ?? 0);
        $data['total_fees_sat'] = (int) ($data['total_fees_sat'] ?? 0);

        return response()->json(['data' => $data]);
    }
}
