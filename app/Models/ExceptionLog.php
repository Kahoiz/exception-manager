<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model
{
    protected $table = 'exception_logs';
    protected $fillable = [
        'type',
        'code',
        'message',
        'file',
        'line',
        'trace',
        'uuid',
        'user_id',
        'application',
        'thrown_at',
        'created_at',
        'updated_at',
        'previous_log_id',
    ];

    public function previousLog()
    {
        return $this->belongsTo(__CLASS__, 'previous_log_id');
    }

    public static function insertLogs(array $bundle): void
    {
        foreach ($bundle as $logTrace) {
            $previousLogId = null;
            //reverse the list so we can start from the first log in the trace
            foreach (array_reverse($logTrace) as $log) {
                $log['previous_log_id'] = $previousLogId;

                $previousLogId = self::create($log)->id;
            }
        }
    }
    protected $casts = [
        'thrown_at' => 'datetime',
    ];
}
