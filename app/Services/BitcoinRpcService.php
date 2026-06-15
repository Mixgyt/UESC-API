<?php

namespace App\Services;

use Denpa\Bitcoin\Client;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class BitcoinRpcService
{
    private Client $rpc;

    public function __construct()
    {
        $this->rpc = new Client([
            'scheme' => 'http',
            'host' => (string) config('bitcoin.host'),
            'port' => (int) config('bitcoin.port'),
            'user' => (string) config('bitcoin.user'),
            'pass' => (string) config('bitcoin.password'),
            'timeout' => (float) config('bitcoin.timeout', 10.0),
        ]);

        $wallet = (string) config('bitcoin.wallet', '');

        if ($wallet !== '') {
            $this->rpc->wallet($wallet);
        }
    }

    public function getBlockchainInfo(): array
    {
        return $this->call('getblockchaininfo');
    }

    public function getBlockCount(): int
    {
        return (int) $this->call('getblockcount');
    }

    public function getBlockHash(int $height): string
    {
        return (string) $this->call('getblockhash', [$height]);
    }

    public function getBlock(string $hash, int $verbosity = 2): array
    {
        return $this->call('getblock', [$hash, $verbosity]);
    }

    public function getRawTransaction(string $txid, bool $verbose = true): array|string
    {
        return $this->call('getrawtransaction', [$txid, $verbose]);
    }

    public function getMempoolInfo(): array
    {
        return $this->call('getmempoolinfo');
    }

    public function getRawMempool(bool $verbose = true): array
    {
        return $this->call('getrawmempool', [$verbose]);
    }

    public function getMempoolEntry(string $txid): array
    {
        return $this->call('getmempoolentry', [$txid]);
    }

    public function getNetworkInfo(): array
    {
        return Cache::remember('node.network_info', 60, fn (): array => $this->call('getnetworkinfo'));
    }

    /**
     * @param array<int, mixed> $params
     */
    private function call(string $method, array $params = []): mixed
    {
        try {
            return $this->rpc->{$method}(...$params)->result();
        } catch (Throwable $exception) {
            throw new HttpException(503, 'Bitcoin node RPC unavailable.', $exception);
        }
    }
}
