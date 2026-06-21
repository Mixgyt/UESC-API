<?php

return [
    'host' => env('BITCOIN_RPC_HOST', '127.0.0.1'),
    'port' => (int) env('BITCOIN_RPC_PORT', 18443),
    'user' => env('BITCOIN_RPC_USER', 'regtest'),
    'password' => env('BITCOIN_RPC_PASSWORD', 'regtest'),
    'wallet' => env('BITCOIN_RPC_WALLET', ''),
    'timeout' => (float) env('BITCOIN_RPC_TIMEOUT', 10.0),
    'mining_cooldown' => (int) env('MINING_COOLDOWN_SECONDS', 120),
    'mining_job_ttl' => (int) env('MINING_JOB_TTL_SECONDS', 180),
    'mining_target_prefix' => env('MINING_TARGET_PREFIX', '00000'),
    'mining_reward_sats' => (int) env('MINING_REWARD_SATS', 5000000000),
];
