<?php

namespace App\Jobs;

use App\Service\ExceptionAnalyser;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Spatie\SlackAlerts\Facades\SlackAlert;

class AnalyseException implements ShouldQueue
{
    use Queueable;

    public array $exceptionLogs;
    public string $application;
    public string $firstThrown;

    public function __construct($exceptionLogs, $application)
    {
        $this->exceptionLogs = $exceptionLogs;
        $this->application = $application;
        $this->firstThrown = $exceptionLogs[0]['thrown_at']; //The logs are in chronological order



    }



    public function handle(): void
    {
        $analyser = new ExceptionAnalyser($this->exceptionLogs);
        $result = $analyser->analyse();
        //do stuff with the result
        dump($result);
        $this->notifySpikeWithBlocks($result);

    }

    private function notifySpikeWithBlocks($result): void
    {
        $count = count($result['types']);
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
                    [
                        "type" => "mrkdwn",
                        "text" => "*First thrown:*\n{$this->firstThrown}"
                    ]

                ]
            ],
            [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Total exceptions:*\n{$result['amount']}"
                    ],
                    [
                        "type" => "mrkdwn",
                        "text" => "*Unique exceptions:*\n{$count}"
                    ]
                ]
            ],
            [
                "type" => "divider"
            ],
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "*All different exceptions:*"
                ]
            ],
            ...$this->getAllExceptions($result)
        ]);
    }

    /** For every exception type in the logs, create a block with the type and the amount of exceptions to that type
     * @return array
     */
    private function getAllExceptions($result)
    {
        $exceptions = [];
        foreach ($result['types'] as $type => $count) {
            $exceptions[] = [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => "*Type:*\n{$type}"
                    ],
                    [
                        "type" => "mrkdwn",
                        "text" => "*Amount:*\n" . $count
                    ]
                ]
            ];
        }
        return $exceptions; //return all exceptions
//        return array_slice($exceptions, 0, 5); //return only the first 5 exceptions
    }
}
