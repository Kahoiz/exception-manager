<?php

namespace App\Jobs;

use App\Models\Cause;
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

    public function handle(ExceptionAnalyser $analyser): void
    {
        if (!$analyser->detectSpike($this->exceptionLogs, $this->application)) {
            return;
        }

        $cause = $analyser->identifyCause($this->exceptionLogs);
        $cause->application = $this->application;

        $cause->save();
        $builder = new NotificationBuilder($cause);
        $builder->notifySpikeWithBlocks($cause);
    }
}
