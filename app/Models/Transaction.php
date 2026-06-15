<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $primaryKey = 'txid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'txid',
        'block_hash',
        'block_height',
        'confirmed_at',
        'fee',
        'size',
        'vsize',
        'input_count',
        'output_count',
        'total_output_sat',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'fee' => 'integer',
            'total_output_sat' => 'integer',
            'raw' => 'array',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_hash', 'hash');
    }
}
