<?php

namespace App\Jobs;

use App\Service\Analysis\SpikeAnalyser;
use App\Service\ExceptionAnalyser;
use App\Service\Notification\NotificationBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;


class AnalyseException implements ShouldQueue
{
    use Queueable;

    public Collection $exceptionLogs;

    public string $application;

    public function __construct($exceptionLogs, $application)
    {
        $this->exceptionLogs = collect($exceptionLogs);
        $this->application = $application;
    }



    public function handle(): void
    {   $analyser = new ExceptionAnalyser($this->exceptionLogs, $this->application);
//        $spike = $analyser->detectSpike();
        $analyser->findCause();
        if (!$spike) {
            return;
        }


        $analyser->findCause();
    }
}
