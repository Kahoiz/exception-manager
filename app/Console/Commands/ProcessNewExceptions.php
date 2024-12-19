<?php

namespace App\Console\Commands;

use App\Jobs\PersistException;
use App\Service\ExceptionAnalyser;
use App\TestTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use App\Jobs\AnalyseException;


class ProcessNewExceptions extends Command
{

    protected $signature = 'mq:process {--limit=500}';
    protected $description = 'Empties a queue and spins up jobs in batches. Limitmax is 500';
    private CONST LIMIT = 500;

    public function handle(): void
    {
        $time = now()->format('Y-m-d H:i:s');

        $limit = $this->validateLimitOption($this->option('limit'));

        $persistLogs = collect(); //to be inserted into the database

        $analyseLogs = collect(); //to be analysed
        try {
            while ($message = Queue::pop('new-exception')) {
                $log = $message->payload();
                $persistLogs->push($this->transform($log, $time));

                unset($log['previous']); //not needed for analysis

                $analyseLogs->push($log);

                $message->delete(); //the message is not deleted automatically

                if (count($persistLogs) >= $limit) {//prevent the command from potentially running out of memory
                    $this->dispatchJobs($persistLogs,$analyseLogs);
                    $persistLogs = collect();
                    $analyseLogs = collect();
                }
            }
        }
        finally { //In case of errors or if the queue has been emptied
            if ($persistLogs->isNotEmpty()) {
                $this->info("Queue is empty. Processing the remaining " . count($persistLogs) . " exceptions ");
                $this->dispatchJobs($persistLogs,$analyseLogs);
            }
        }
    }

    private function transform(array $log, string $time): array
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

    private function dispatchJobs(Collection $persistLogs,Collection $analyseLogs): void
    {
        $this->info('Dispatching job to insert ' . count($persistLogs) . ' exceptions and subsidiaries into the database');
        PersistException::dispatchSync($persistLogs);

        $analyseLogs = collect($analyseLogs)->groupBy('application');
        foreach($analyseLogs as $application => $logs){
            $this->info('Dispatching job to analyse ' . count($analyseLogs) . ' from ' . $application);
            AnalyseException::dispatchSync($logs,$application);
        }

    }

    /**
     * Validates the 'limit' option provided to the command.
     * Ensures that the limit is within the acceptable range.
     * If the limit is below 0, it sets it to the default limit.
     * If the limit is above the maximum allowed limit, it sets it to the default limit.
     *
     * @return int
     */
    private function validateLimitOption(string $limitStr): int
    {

        if(is_numeric($limitStr)){
            $limit = (int)$limitStr;
        } else {
            $this->info('The limit is not a number. The limit has been set to ' . self::LIMIT);
            return self::LIMIT;
        }

        if($limit < 0){
            $this->info('The limit is too low. The minimum is 0');
            $this->info('The limit has been set to' . self::LIMIT);
            return self::LIMIT;
        }
        if ($limit > self::LIMIT) {
            $this->info('The limit is too high. The maximum is ' . self::LIMIT);
            $this->info('The limit has been set to ' . self::LIMIT);
            return self::LIMIT;
        }
        return $limit;
    }
}
