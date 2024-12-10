<?php

namespace App\Service\Analysis\Interfaces;

use Illuminate\Support\Collection;

interface CarrierAnalyserInterface
{
    public function analyse(Collection $exceptions) : string;
}
