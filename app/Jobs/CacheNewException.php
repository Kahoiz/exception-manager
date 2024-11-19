<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CacheNewException implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


}
