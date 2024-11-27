<?php

namespace App\Service\Analysis;

class VolumeAnalyser
{

    public static function analyse($exceptions): int
    {
        return count($exceptions);
    }
}
