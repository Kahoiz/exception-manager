<?php

namespace App\Service\Analysis;

class SpikeAnalyser
{
    public static function analyse($exceptions): bool
    {
        return count($exceptions) > 10;
    }
}
