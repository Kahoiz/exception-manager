<?php

namespace App\Jobs;

use App\Service\ExceptionAnalyser;
use App\Service\Notification\AbstractNotificationBuilder;
use App\Service\Notification\NotificationData;
use App\Service\Notification\Slack\SlackNotificationBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;


class AnalyseException implements ShouldQueue
{
    use Queueable;

    public Collection $exceptionLogs;

    public string $application;

    // @var Collection<AbstractNotificationBuilder>
    private Collection $notificationBuilders; // of AbstractNotificationBuilder

    /**
     * Constructor for AnalyseException.
     *
     * @param Collection $exceptionLogs Collection of exception logs.
     * @param string $application Name of the application.
     * @param Collection|null $notificationBuilders Collection of notification builders (optional for now)
     * TODO make obligatory $NotificationBuilders .
     */
    public function __construct(Collection $exceptionLogs, string $application)
    {
        $this->exceptionLogs = $exceptionLogs;
        $this->application = $application;
        $this->notificationBuilders = collect();
    }


    public function handle(ExceptionAnalyser $analyser): void
    {
        if (!$analyser->detectSpike($this->exceptionLogs, $this->application)) {
            return;
        }

        $cause = $analyser->identifyCause($this->exceptionLogs);
        $cause->application = $this->application;

        //get the data we need from cause
        $data = [
            "Total Exception Count" => $this->exceptionLogs->count(),
            'Top Errors' => $cause['data']['types']
        ];

        //we encode the data to store it in the database
        $cause->data = json_encode($cause->data);
        $cause->save();

        $notificationData = new NotificationData(
            "Spike detected",
            $this->application,
            "A sudden increase in exceptions was detected.",
            carrier: $cause->data['carrier'] ?? null,
            fields:$data
        );

        // for now we only use slack
        // TODO: add more notification types
        if ($this->notificationBuilders->isEmpty()) {
            $this->notificationBuilders->add(new SlackNotificationBuilder());
        }

        foreach ($this->notificationBuilders as $notificationBuilder) {
            $notificationBuilder->addNotification($notificationData);
            $notificationBuilder->notify();
        }
    }
}
