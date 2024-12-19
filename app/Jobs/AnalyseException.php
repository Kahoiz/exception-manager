<?php

namespace App\Jobs;

use App\Notifications\SpikeDetected;
use App\Service\ExceptionAnalyser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;



class AnalyseException implements ShouldQueue
{
    use Queueable;

    public Collection $exceptionLogs;

    public string $application;

    private ExceptionAnalyser $analyser;

    /**
     * Constructor for AnalyseException.
     *
     * @param Collection $exceptionLogs Collection of exception logs.
     * @param string $application Name of the application.
     */
    public function __construct(Collection $exceptionLogs,
                                string $application
                                )
    {
        $this->exceptionLogs = $exceptionLogs;
        $this->application = $application;
        $this->analyser = resolve(ExceptionAnalyser::class);
    }


    public function handle(): void
    {
        // Detect anomalies in the exception logs
        $causes = $this->detectAnomalies($this->exceptionLogs); //string array of causes

        if(empty($causes)) {
            return;
        }

        // Identify the cause of the exceptions
        $cause = $this->analyser->identifyCause($this->exceptionLogs);
        $cause->application = $this->application;

        // Retrieve the data property, modify it, and set it back
        $data = $cause->data;
        $data["causes"] = $causes;
        $cause->data = $data;

        // Notify the cause of the exceptions
        $cause->notifyNow(new SpikeDetected());

        $cause->data = json_encode($cause->data);

        $cause->save();
    }

    private function detectAnomalies(Collection $exceptionLogs) : array
    {
        $anomalies = [];
        $value = $this->analyser->detectSpike($exceptionLogs, $this->application);
        if($value) {
            $anomalies['spikeDetected'] = "true";
        }

        $value = $this->analyser->detectTypeAnomaly($exceptionLogs);
        foreach ($value as $type => $frequency) {
            if($frequency > 0) {
                $anomalies[$type ."Anomaly"] = $frequency;
            }
        }

        return $anomalies;
    }
}
