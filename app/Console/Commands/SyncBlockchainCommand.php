<?php

namespace App\Console\Commands;

use App\Jobs\SyncBlockchainJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SyncBlockchainCommand extends Command
{
    protected $signature = 'blockchain:sync';

    protected $description = 'Synchronize blocks, transactions and mempool from Bitcoin regtest node';

    public function handle(): int
    {
        Bus::dispatchSync(new SyncBlockchainJob());

        $this->info('Blockchain synchronization completed.');

        return self::SUCCESS;
    }
}
