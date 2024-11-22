<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDTO extends Model
{
    protected $fillable = [
        'type',
        'message',
        'file',
        'line',
        'environment',
        'thrown_at',
    ];
}
