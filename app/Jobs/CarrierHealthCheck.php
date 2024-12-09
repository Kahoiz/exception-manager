<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CarrierHealthCheck implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //Only dispatches when a spike is detected and among the most frequent exceptions is RequestException
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Check the health status for all Carrier API's
    }
}
