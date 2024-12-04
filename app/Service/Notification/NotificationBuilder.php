<?php

namespace App\Service\Notification;

use Spatie\SlackAlerts\Facades\SlackAlert;

class NotificationBuilder
{
    public static function notifySpikeWithBlocks($data,$application): void
    {
        $firstThrown = $data[0]['thrown_at'];
        $count = count($data);

        SlackAlert::blocks([
            [
                "type" => "header",
                "text" => [
                    "type" => "plain_text",
                    "text" => ":rotating_light: Spike of exceptions detected :rotating_light: ",
                    "emoji" => true
                ]
            ],
            [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Application:*\n{$application}"
                    ],
                    [
                        "type" => "mrkdwn",
                        "text" => "*First thrown:*\n{$firstThrown}"
                    ]

                ]
            ],
            [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Total exceptions:*\n{$count}"
                    ],
                ]
            ],

        ]);
    }



}
