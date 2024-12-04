<?php

namespace App\Jobs;

use App\Service\Analysis\SpikeAnalyser;
use App\Service\Notification\NotificationBuilder;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SpikeDetection implements ShouldQueue
{
    use Queueable;
    use Batchable;

private Collection $exceptionLogs;
private string $application;
    public function __construct($exceptionLogs,$application)
    {
        $this->exceptionLogs = collect($exceptionLogs);
        $this->application = $application;
    }

    /**
     * Execute the job.
     */
    public function handle(): bool
    {

    }
}
