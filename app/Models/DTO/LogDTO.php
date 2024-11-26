<?php

namespace App\Models\DTO;

use Illuminate\Database\Eloquent\Model;

class LogDTO extends Model
{
    protected $fillable = [
        'type',
        'code',
        'message',
        'file',
        'line',
        'user_id',
        'application',
        'thrown_at',
        'previous',
    ];

    static function fromLogs(array $logs): LogDTO
    {
        if (isset($logs['previous'])) {
            $logs['previous'] = self::fromLogs($logs['previous']);
        }
        return new LogDTO($logs);
    }
}
