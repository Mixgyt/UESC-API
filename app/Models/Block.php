<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $primaryKey = 'hash';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'hash',
        'height',
        'time',
        'tx_count',
        'size',
        'weight',
        'difficulty',
        'miner_reward',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'time' => 'datetime',
            'difficulty' => 'float',
            'miner_reward' => 'integer',
            'raw' => 'array',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'block_hash', 'hash');
    }
}
