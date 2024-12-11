<?php

namespace App\Service\Notification;

interface NotificationBuilderInterface
{
    /**
     * Sends the notification.
     *
     * @return void
     */
    public function notify(): void;

    /**
     * Adds a notification to the builder.
     *
     * @param NotificationData $notification The notification to add.
     * @return void
     */
    public function addNotification(NotificationData $notification): void;

}
