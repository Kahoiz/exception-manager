<?php

namespace App\Jobs;

use App\Models\Cause;
use App\Service\ExceptionAnalyser;
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
        $spike = $analyser->detectSpike($this->exceptionLogs, $this->application);

        $cause = $analyser->identifyCause($this->exceptionLogs);

        $cause->application = $this->application;

        $cause->save();

        if (!$spike) {
            return;
        }

    }
}
