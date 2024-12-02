<?php

namespace App\Console\Commands;

use App\Jobs\PersistException;
use App\Models\ExceptionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use App\Jobs\AnalyseException;
use Spatie\SlackAlerts\Facades\SlackAlert;

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
                $message->delete(); //the message is not deleted automatically

                //prevent the command from potentially running out of memory
                if (count($logs) >= $limit) {
                    $this->info('Inserting ' . count($logs) . ' exceptions into the database');
                    $this->dispatchJobs($logs);
                    $logs = [];
                }
            }
        } finally { //In case of errors or if the queue has been emptied
            if (!empty($logs)) { //If the queue is empty when the command runs, the logs array will be empty
                $this->dispatchJobs($logs);
            }
        }
    }

    private function transform($message, $time): array
    {
        $log = $message->payload();

        $logList = [];

        while ($log) {
            $log['created_at'] = $time;
            $log['updated_at'] = $time;
            $logList[] = array_diff_key($log, ['previous' => '']); //avoid circular reference
            $log = $log['previous'] ?? null;
        }
        return $logList;
    }

    private function dispatchJobs($logs): void
    {
        $this->info('Dispatching job to insert ' . count($logs) . ' exceptions and subsidiaries into the database');

        PersistException::dispatchSync($logs);
//        PersistException::dispatch($logs)->onQueue('persist-exception');

        //We only want the first exception in the chain sent to analysis, so the rest are removed
        $logs = $this->trimAndGroupLogs($logs);
        foreach ($logs as $application => $bundledLogs) {
            $this->info('Dispatching job to analyse ' . count($bundledLogs) . ' exceptions from ' . $application);
            AnalyseException::dispatchSync($bundledLogs, $application);
//            AnalyseException::dispatch($logs)->onQueue('analyse-exception');
        }
    }

    private function trimAndGroupLogs(array $logs): array
    {
        $trimmedLogs = array_map(static function ($log) {
            return $log[0];
        }, $logs);
        return collect($trimmedLogs)->groupBy('application')->toArray();
    }

}

