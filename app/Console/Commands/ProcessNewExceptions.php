<?php

namespace App\Console\Commands;

use App\Jobs\PersistException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use App\Jobs\AnalyseException;


class ProcessNewExceptions extends Command
{
    protected $signature = 'mq:process';
    protected $description = 'Empties a queue and spins up jobs in batches';
    private CONST LIMIT = 500;

    public function handle(): void
    {
        $time = now()->format('Y-m-d H:i:s');
        $persistLogs = []; //to be inserted into the database

        $analyseLogs = []; //to be analysed
        try {
            while ($message = Queue::pop('new-exception')) {
                $log = $message->payload();
                $persistLogs[] = $this->transform($log, $time);

                unset($log['previous']); //not needed for analysis

                $analyseLogs[] = $log;

                $message->delete(); //the message is not deleted automatically

                if (count($persistLogs) >= self::LIMIT) {//prevent the command from potentially running out of memory
                    $this->info('Inserting ' . count($persistLogs) . ' exceptions into the database');
                    $this->dispatchJobs($persistLogs,$analyseLogs);
                    $persistLogs = [];
                    $analyseLogs = [];
                }
            }
        }
        finally { //In case of errors or if the queue has been emptied
            if (!empty($persistLogs)) {
                $this->info('Inserting ' . count($persistLogs) . ' exceptions into the database');
                $this->dispatchJobs($persistLogs,$analyseLogs);
            }
        }
    }

    private function transform($log, $time): array
    {

        $logList = [];

        while ($log) {

            $log['created_at'] = $time;
            $log['updated_at'] = $time;
            $logList[] = array_diff_key($log, ['previous' => '']); //avoid data clutter
            $log = $log['previous'] ?? null;
        }
        return $logList;
    }

    private function dispatchJobs($persistLogs, $analyseLogs): void
    {
        $this->info('Dispatching job to insert ' . count($persistLogs) . ' exceptions and subsidiaries into the database');

        PersistException::dispatchSync($persistLogs);

        $analyseLogs = collect($analyseLogs)->groupBy('application');
        foreach($analyseLogs as $application => $logs){
            $this->info('Dispatching job to analyse ' . count($analyseLogs) . ' from ' . $application);
            AnalyseException::dispatchSync($logs,$application);
        }

    }
}

