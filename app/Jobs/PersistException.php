<?php

namespace App\Jobs;

use App\Models\ExceptionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PersistException implements ShouldQueue
{
    use Queueable;
    public array $exceptionLogs;


    public function __construct($exceptionLogs)
    {
        $this->exceptionLogs = $exceptionLogs;
    }


    public function handle(): void
    {
        ExceptionLog::insertLogs($this->exceptionLogs);

    }
}
