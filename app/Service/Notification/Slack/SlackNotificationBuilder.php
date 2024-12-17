<?php

namespace App\Service\Notification\Slack;

use App\Service\Notification\NotificationBuilderInterface;
use App\Service\Notification\NotificationData;
use Illuminate\Support\Collection;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SlackNotificationBuilder implements NotificationBuilderInterface
{
    private Collection $notification;

    public function __construct()
    {
        $this->notification = collect();  // Create an empty collection for notification
    }

    public function notify(): void
    {
        if ($this->notification->isEmpty()) {
            // No notification to send
            return;
        }

        // Send the notification blocks to Slack
        SlackAlert::blocks($this->notification->toArray());
    }

    private function addHeader(string $header): void
    {
        // Add a header to the notification
        $this->notification->prepend([  // Prepend so the header comes first
            "type" => "header",
            "text" => [
                "type" => "plain_text",
                "text" => ":rotating_light: $header :rotating_light:",
                "emoji" => true,
            ],
        ]);
    }

    public function addNotification(NotificationData $notificationData): void
    {
        // First, add the header
        $this->addHeader($notificationData->title);

        // Add the application and message sections
        $this->notification->add([   // Use push to add each section
            "type" => "section",
            "fields" => [
                [
                    "type" => "mrkdwn",
                    "text" => "*Application:*\n{$notificationData->application}",
                ],
                [
                    "type" => "mrkdwn",
                    "text" => "*Message:*\n{$notificationData->message}",
                ],
            ],
        ]);

        // Add the context section if provided
        if ($notificationData->context) {
            $this->notification->add([
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Context:*\n{$notificationData->context}",
                    ],
                ],
            ]);
        }

        // Add the fields section (key-value pairs)
        $this->notification->add([
            "type" => "section",
            "fields" => collect($notificationData->fields)->map(function ($value, $key) use ($notificationData) {
                // If the value is an array, join the array values with newlines
                return [
                    "type" => "mrkdwn",
                    "text" => "*$key:*\n$value",  // Slack Markdown format
                ];
            })->toArray(),
        ]);

    }
}
