<?php

namespace App\Service\Notification;

use Spatie\SlackAlerts\Facades\SlackAlert;

class NotificationBuilder
{
    private $application;
    private $cause;
    public function __construct($cause)
    {
        $this->application = $cause->application;
        $this->cause = $cause;
    }


    public function notifySpikeWithBlocks($cause): void
    {
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
                        "text" => "*Application:*\n{$this->application}"
                    ],

                ]
            ],
            [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Total exceptions:*\n{}"
                    ],
                ]
            ],
                //...self::addExceptionTypes($cause->data['types']),
        ]);
    }

    private static function addExceptionTypes(mixed $types): array
    {
        return array_map(function ($type) {
            return [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Type:*\n{$type}"
                    ]
                ]
            ];
        }, $types);
    }
}
