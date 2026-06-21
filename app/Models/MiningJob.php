<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiningJob extends Model
{
    protected $fillable = [
        'id',
        'height',
        'previous_block_hash',
        'challenge',
        'target_prefix',
        'status',
        'winner_address',
        'winning_nonce',
        'winning_hash',
        'block_hash',
        'reward_sats',
        'expires_at',
        'solved_at',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'solved_at' => 'datetime',
            'reward_sats' => 'integer',
        ];
    }
}
