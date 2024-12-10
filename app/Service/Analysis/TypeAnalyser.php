<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use Illuminate\Support\Collection;

class TypeAnalyser implements TypeAnalyserInterface
{
    public function analyse(Collection $exceptions) : Collection
    {
        return $exceptions->groupBy('type')->sortDesc()->take(5);
    }

}
