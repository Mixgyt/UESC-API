<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\MempoolController;
use App\Http\Controllers\Api\MiningController;
use App\Http\Controllers\Api\NodeController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthController::class, 'token']);

Route::delete('/auth/token', [AuthController::class, 'revoke'])->middleware('auth:sanctum');
Route::post('/node/mine', [NodeController::class, 'mine'])->middleware('auth:sanctum');


Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/node/info', [NodeController::class, 'info']);
    Route::get('/mining/work', [MiningController::class, 'work']);
    Route::post('/mining/submit', [MiningController::class, 'submit']);

    Route::get('/mempool', [MempoolController::class, 'index']);
    Route::get('/mempool/summary', [MempoolController::class, 'summary']);
    Route::get('/mempool/{txid}', [MempoolController::class, 'show']);
});

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/blocks', [BlockController::class, 'index']);
    Route::get('/blocks/latest', [BlockController::class, 'latest']);
    Route::get('/blocks/{hashOrHeight}/transactions', [TransactionController::class, 'byBlock']);
    Route::get('/blocks/{hashOrHeight}', [BlockController::class, 'show']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{txid}', [TransactionController::class, 'show']);
});
