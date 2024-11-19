<?php

namespace App\Console\Commands;

use App\Jobs\AnalyseNewException;
use App\Jobs\CacheNewException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class ProcessNewExceptions extends Command
{
    protected $signature = 'mq:process';
    protected $description = 'Listens for new exceptions in the queue and spins up jobs';


    public function handle()
    {
        while ($message = Queue::pop('new-exception')) {
            CacheNewException::dispatch($message->payload())->onQueue('cache-exception');
            AnalyseNewException::dispatch($message->payload())->onQueue('analyse-exception');
            $message->delete();


        }

    }
}
