<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MiningWorkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MiningController extends Controller
{
    public function __construct(private readonly MiningWorkService $mining)
    {
    }

    public function work(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:128'],
        ]);

        return response()->json([
            'data' => $this->mining->currentWorkForAddress($validated['address']),
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_id' => ['required', 'uuid'],
            'address' => ['required', 'string', 'max:128'],
            'nonce' => ['required', 'string', 'max:64'],
        ]);

        $result = $this->mining->submitSolution(
            $validated['job_id'],
            $validated['address'],
            $validated['nonce']
        );

        $status = match ($result['status'] ?? null) {
            'accepted' => 200,
            'already_solved' => 409,
            'expired' => 410,
            'not_found' => 404,
            default => 422,
        };

        return response()->json(['data' => $result], $status);
    }
}
