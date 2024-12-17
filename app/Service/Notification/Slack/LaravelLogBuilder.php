<?php

namespace App\Service\Notification\Slack;

use App\Service\Notification\NotificationBuilderInterface;
use App\Service\Notification\NotificationData;
use Illuminate\Support\Facades\Log;

class LaravelLogBuilder implements NotificationBuilderInterface
{
    private array $data;

    public function notify(): void
    {
        Log::critical(':rotating_light: Spike detected :rotating_light:', [$this->data]);
    }

    public function addNotification(NotificationData $notification): void
    {
        $this->data = [
            'Application' => $notification->application,
            'Message' => $notification->message,
            'Total Exception Count' => $notification->fields['Total Exception Count'],
            'Top Errors' => $notification->fields['Top Errors'],

        ];

        if (isset($notification->fields['Carrier'])) {
            $this->data['Carrier'] = $notification->fields['Carrier'];
        }
    }
}
