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
    {
        $analyser = $exceptionAnalyser = app(ExceptionAnalyser::class);

        $spike = $analyser->detectSpike($this->exceptionLogs, $this->application);
        $analyser->findCause($this->exceptionLogs, $this->application);
        if (!$spike) {
            return;
        }
    }
}
