<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockResource;
use App\Models\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        return BlockResource::collection(
            Block::query()->orderByDesc('height')->paginate($perPage)
        );
    }

    public function latest(): JsonResponse
    {
        $block = Block::query()->orderByDesc('height')->first();

        if (! $block) {
            return response()->json(['message' => 'No blocks found.'], 404);
        }

        return response()->json(['data' => new BlockResource($block)]);
    }

    public function show(string $hashOrHeight): JsonResponse
    {
        $query = Block::query();
        $isHash = preg_match('/^[a-f0-9]{64}$/i', $hashOrHeight) === 1;

        $block = $isHash
            ? $query->where('hash', strtolower($hashOrHeight))->first()
            : $query->where('height', (int) $hashOrHeight)->first();

        if (! $block) {
            return response()->json(['message' => 'Block not found.'], 404);
        }

        return response()->json(['data' => new BlockResource($block)]);
    }
}
