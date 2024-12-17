<?php

namespace App\Jobs;

use App\Service\Notification\NotificationData;
use App\Service\Notification\Slack\LaravelLogBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class NotifyStaff implements ShouldQueue
{
    use Queueable;

    private NotificationData $notificationData;
    private Collection $notificationBuilders; // of AbstractNotificationBuilder


    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $fields['Total Exception Count'] = $data['Total Exception Count'];
        $fields['Top Errors'] = $data['Top Errors'];

        $this->notificationData = new NotificationData(
            $data['Application'],
            $data['Carrier'] ?? null,
            fields: $fields,
        );
        $this->notificationBuilders = collect();

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: add more notification types

        if ($this->notificationBuilders->isEmpty()) {
            $this->notificationBuilders->add(new LaravelLogBuilder());
//            $this->notificationBuilders->add(new SlackNotificationBuilder());

            $this->notificationBuilders->each(
                fn($builder) => $builder->addNotification($this->notificationData)
            );

            $this->notificationBuilders->each(
                fn($builder) => $builder->notify()
            );


        }
    }
}
