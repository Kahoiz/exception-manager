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

    public function __construct($exceptionLogs)
    {
        $this->exceptionLogs = $exceptionLogs;



    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $analyser = new ExceptionAnalyser($this->exceptionLogs);
        $result = $analyser->analyse();
        //do stuff with the result
        dump($result);

    }

//    private function notifySpikeWithBlocks(): void
//    {
//        $count = count($this->unique);
//        SlackAlert::blocks([
//            [
//                "type" => "header",
//                "text" => [
//                    "type" => "plain_text",
//                    "text" => ":rotating_light: Spike of exceptions detected :rotating_light: ",
//                    "emoji" => true
//                ]
//            ],
//            [
//                "type" => "section",
//                "fields" => [
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Application:*\n{$this->application}"
//                    ],
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Timeslot:*\n{$this->timeslot}"
//                    ]
//
//                ]
//            ],
//            [
//                "type" => "section",
//                "fields" => [
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Total exceptions:*\n{$this->amount}"
//                    ],
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Unique exceptions:*\n{$count}"
//                    ]
//                ]
//            ],
//            [
//                "type" => "divider"
//            ],
//            [
//                "type" => "section",
//                "text" => [
//                    "type" => "mrkdwn",
//                    "text" => "*All different exceptions:*"
//                ]
//            ],
//            ...$this->getAllExceptions()
//        ]);
//    }
//
//    /** For every exception type in the logs, create a block with the type and the amount of exceptions to that type
//     * @return array
//     */
//    private function getAllExceptions()
//    {
//        $exceptions = [];
//        foreach ($this->unique as $type => $logs) {
//            $exceptions[] = [
//                "type" => "section",
//                "fields" => [
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Type:*\n{$type}"
//                    ],
//                    [
//                        "type" => "mrkdwn",
//                        "text" => "*Amount:*\n" . count($logs)
//                    ]
//                ]
//            ];
//        }
//        return $exceptions; //return all exceptions
////        return array_slice($exceptions, 0, 5); //return only the first 5 exceptions
//    }
}
