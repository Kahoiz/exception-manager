<?php

namespace App\Console\Commands;

use App\Models\DTO\LogDTO;
use App\Models\ExceptionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class ProcessNewExceptions extends Command
{
    protected $signature = 'mq:process';
    protected $description = 'Listens for new exceptions in the queue and spins up jobs';

    public function handle(): void
    {
        $time = now()->format('Y-m-d H:i:s');
        $logs = [];
        $limit = 50;
        try {

            while ($message = Queue::pop('new-exception')) {

                $logs[] = $this->transform($message, $time);
                $logDTO = LogDTO::fromLogs($message->payload()); //Excess data from the payload is removed in the LogDTO constructor
                Queue::pushRaw($logDTO, 'analyse-exception');

                $message->delete();

                if (count($logs) >= $limit) {
                    $this->info('Inserting ' . count($logs) . ' exceptions into the database');
                    ExceptionLog::insertLogs($logs);
                    $logs = [];
                }
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());

        } finally {
            if (!empty($logs)) { //If the queue is empty when the command runs, the logs array will be empty
                $this->info('Inserting ' . count($logs) . ' exceptions into the database');
                ExceptionLog::insertLogs($logs);

            }
        }
    }

    private function transform($message, $time): array
    {
        //There's no reason to store the payload as ExceptionLog object, since we'll have to transform it back to an array to store it in the database
        //Well just transform it to an array here and now
        $log = $message->payload();
        $logList = [];
        //The log can be nested with previous logs,
        while ($log) {
            $logs['created_at'] = $time;
            $logs['updated_at'] = $time;
            $logList[] = $log;
            $log = $log['previous'] ?? null;
        }

        return $logList;
    }

}

