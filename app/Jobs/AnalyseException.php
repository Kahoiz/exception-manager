<?php

namespace App\Jobs;

use App\Notifications\SpikeDetected;
use App\Service\ExceptionAnalyser;
use App\Service\Notification\AbstractNotificationBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;


class AnalyseException implements ShouldQueue
{
    use Queueable;

    public Collection $exceptionLogs;

    public string $application;

    // @var Collection<AbstractNotificationBuilder>

    /**
     * Constructor for AnalyseException.
     *
     * @param Collection $exceptionLogs Collection of exception logs.
     * @param string $application Name of the application.
     * TODO make obligatory $NotificationBuilders .
     */
    public function __construct(Collection $exceptionLogs, string $application)
    {
        $this->exceptionLogs = $exceptionLogs;
        $this->application = $application;
    }


    public function handle(ExceptionAnalyser $analyser): void
    {
        //No spike, no reason to notify
//        if (!$analyser->detectSpike($this->exceptionLogs, $this->application)) {
//            return;
//        }

        $cause = $analyser->identifyCause($this->exceptionLogs);
        $cause->application = $this->application;

        //get the data we need from cause
        $data = [
            "Application" => $this->application,
            "Total Exception Count" => $this->exceptionLogs->count(),
            'Top Errors' => $cause['data']['types']
        ];

        if(isset($cause['data']['carrier'])) {
            $data['Carrier'] = $cause['data']['carrier'];
        }



        $cause->data = json_encode($cause->data);


        $cause->save();

        Notification::sendNow($cause, new SpikeDetected($data));

    }
}
