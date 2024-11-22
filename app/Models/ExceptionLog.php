<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model
{
    protected $table = 'exception_logs';
    protected $fillable = [
        'type',
        'message',
        'file',
        'line',
        'trace',
        'uuid',
        'environment',
        'thrown_at',
        'created_at',
        'updated_at'
    ];
}
