<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\TypeAnalyserInterface;

class TypeAnalyser implements TypeAnalyserInterface
{
    public function analyse($exceptions)
    {
        return $exceptions->groupBy('type')->sortDesc()->take(5);

    }

}
