<?php

namespace App\Console\Commands;

use App\Models\ExceptionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use App\Jobs\AnalyseException;

class ProcessNewExceptions extends Command
{
    protected $signature = 'mq:process';
    protected $description = 'Listens for new exceptions in the queue and spins up jobs';

    public function handle(): void
    {
        $time = now()->format('Y-m-d H:i:s');
        $logs = [];
        $limit = 500;
        try {

            while ($message = Queue::pop('new-exception')) {
                $logs[] = $this->transform($message, $time);
                //For some reason the message is not deleted automatically
                $message->delete();

                //To prevent the command from potentially running out of memory
                if (count($logs) >= $limit) {
                    $this->info('Inserting ' . count($logs) . ' exceptions into the database');
                    ExceptionLog::insertLogs($logs);
                    $logs = $this->trimLogs($logs);
                    $this->dispatchJobs($logs);
                    $logs = [];
                }
            }
        } finally {
            if (!empty($logs)) { //If the queue is empty when the command runs, the logs array will be empty
                $this->info('Inserting ' . count($logs) . ' exceptions into the database');
                ExceptionLog::insertLogs($logs);
                $logs = $this->trimLogs($logs);
                $this->dispatchJobs($logs);


            }
        }
    }

    private function transform($message, $time): array
    {
        //There's no reason to store the payload as ExceptionLog object, since we'll have to transform it
        //back to an array to store it in bulk in the database
        //Well just transform it to an array here and now
        $log = $message->payload();
        $logList = [];
        //The log can be nested with previous logs,
        while ($log) {
            $log['created_at'] = $time;
            $log['updated_at'] = $time;
            $logList[] = array_diff_key($log, ['previous' => '']);
            $log = $log['previous'] ?? null;
        }
        return $logList;
    }

    private function dispatchJobs($logs): void
    {

        //Dispatch a job per application to keep them isolated from each other
        $bundle = collect($logs)->groupBy('application')->toArray();

        foreach ($bundle as $application => $bundledLogs) {
            $this->info('Dispatching job to analyse ' . count($bundledLogs) . ' exceptions from ' . $application);
            AnalyseException::dispatchSync($logs);
//            AnalyseException::dispatch($logs)->onQueue('analyse-exception');
        }
    }

    private function trimLogs(array $logs): array
    {
        //We only want the first exception in the chain sent to analysis, so the rest are removed
        return  array_map(static function($log) {
            return $log[0];
        }, $logs);
    }

}

