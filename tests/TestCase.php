<?php

namespace Tests;

use App\Services\BitcoinRpcService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    protected function mockRpc(callable $callback): void
    {
        $this->mock(BitcoinRpcService::class, function (MockInterface $mock) use ($callback): void {
            $callback($mock);
        });
    }
}
