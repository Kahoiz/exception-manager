<?php

namespace App\Service\Analysis\Interfaces;

use Illuminate\Support\Collection;

interface TypeAnalyserInterface
{
    public function analyse(Collection $exceptions) : Collection;
}
