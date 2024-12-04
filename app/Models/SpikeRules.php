<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpikeRules extends Model
{
    protected $table = 'spike_rules';

    protected $fillable = [
        'application',
        'alpha',
        'threshold',
        'last_ema',
    ];
}
