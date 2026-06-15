<?php

namespace Tests\Feature;

use App\Models\MempoolEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MempoolApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_mempool_returns_sorted_list_by_fee_rate(): void
    {
        MempoolEntry::query()->create([
            'txid' => str_repeat('a', 64),
            'fee' => 100,
            'vsize' => 100,
            'fee_rate' => 1.0,
            'depends' => [],
            'time' => now()->subMinute(),
            'raw' => [],
        ]);

        MempoolEntry::query()->create([
            'txid' => str_repeat('b', 64),
            'fee' => 1000,
            'vsize' => 100,
            'fee_rate' => 10.0,
            'depends' => [],
            'time' => now(),
            'raw' => [],
        ]);

        $this->getJson('/api/mempool?sort=fee_rate&order=desc')
            ->assertOk()
            ->assertJsonPath('data.0.txid', str_repeat('b', 64));
    }

    public function test_get_mempool_summary_returns_stat_fields(): void
    {
        MempoolEntry::query()->create([
            'txid' => str_repeat('c', 64),
            'fee' => 300,
            'vsize' => 100,
            'fee_rate' => 3.0,
            'depends' => [],
            'time' => now(),
            'raw' => [],
        ]);

        MempoolEntry::query()->create([
            'txid' => str_repeat('d', 64),
            'fee' => 700,
            'vsize' => 200,
            'fee_rate' => 7.0,
            'depends' => [],
            'time' => now(),
            'raw' => [],
        ]);

        $this->getJson('/api/mempool/summary')
            ->assertOk()
            ->assertJsonPath('data.tx_count', 2)
            ->assertJsonPath('data.total_vsize', 300)
            ->assertJsonPath('data.total_size_vbytes', 300)
            ->assertJsonPath('data.fee_min_sat_vbyte', 3)
            ->assertJsonPath('data.fee_max_sat_vbyte', 7)
            ->assertJsonPath('data.fee_avg_sat_vbyte', 5)
            ->assertJsonPath('data.total_fees_sat', 1000);
    }

    public function test_get_mempool_entry_not_found_returns_404(): void
    {
        $this->getJson('/api/mempool/'.str_repeat('d', 64))
            ->assertStatus(404);
    }

    public function test_get_mempool_is_public(): void
    {
        $this->getJson('/api/mempool')->assertOk();
    }
}
