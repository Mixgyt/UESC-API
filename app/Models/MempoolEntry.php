<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MempoolEntry extends Model
{
    protected $primaryKey = 'txid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'txid',
        'fee',
        'vsize',
        'fee_rate',
        'depends',
        'time',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'integer',
            'fee_rate' => 'float',
            'depends' => 'array',
            'time' => 'datetime',
            'raw' => 'array',
        ];
    }
}
