<?php

namespace App\Console\Commands;

use App\Models\ExceptionLog;
use App\Models\LogDTO;
use Carbon\Carbon;
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

        while ($message = Queue::pop('new-exception')) {
            $log = $this->transformMessage($message, $time);
            $logs[] = $log;
            $logDTO = new LogDTO($message->payload()); //Excess data from the payload is removed in the LogDTO constructor
            Queue::pushRaw($logDTO, 'analyse-exception');
            $message->delete();

            if (count($logs) >= $limit) {
                ExceptionLog::insert($logs);
                $logs = [];
            }
        }
        if (!empty($logs)) { //If the queue is empty when the command runs, the logs array will be empty
            ExceptionLog::insert($logs);
        }
    }

    private function transformMessage($message, $time)
    {
        //There's no reason to store the payload as ExceptionLog object, since we'll have to transform it back to an array to store it in the database
        //Well just transform it to an array here and now
        $log = $message->payload();
        $log['created_at'] = $time;
        $log['updated_at'] = $time;
        return $log;
    }
}
