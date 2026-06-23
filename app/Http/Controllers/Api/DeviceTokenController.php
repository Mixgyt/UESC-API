<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'address' => ['required', 'string'],
        ]);

        $deviceToken = DeviceToken::query()->firstOrCreate([
            'token' => $validated['token'],
            'address' => $validated['address'],
        ]);

        return response()->json([
            'message' => 'Device token registered successfully.',
            'data' => $deviceToken,
        ], 201);
    }

    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'address' => ['required', 'string'],
        ]);

        $deleted = DeviceToken::query()
            ->where('token', $validated['token'])
            ->where('address', $validated['address'])
            ->delete();

        return response()->json([
            'message' => $deleted > 0 ? 'Device token unregistered successfully.' : 'Device token not found.',
        ], 200);
    }
}
