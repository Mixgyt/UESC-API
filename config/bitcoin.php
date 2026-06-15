<?php

return [
    'host' => env('BITCOIN_RPC_HOST', '127.0.0.1'),
    'port' => (int) env('BITCOIN_RPC_PORT', 18443),
    'user' => env('BITCOIN_RPC_USER', 'regtest'),
    'password' => env('BITCOIN_RPC_PASSWORD', 'regtest'),
    'wallet' => env('BITCOIN_RPC_WALLET', ''),
    'timeout' => (float) env('BITCOIN_RPC_TIMEOUT', 10.0),
];
