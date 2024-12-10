<?php

namespace App\Jobs;

use App\Models\ExceptionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class PersistException implements ShouldQueue
{
    use Queueable;
    public Collection $exceptionLogs;


    public function __construct(Collection $exceptionLogs)
    {
        $this->exceptionLogs = $exceptionLogs;
    }

    public function handle(): void
    {
        ExceptionLog::insertLogs($this->exceptionLogs->toArray());
    }
}
