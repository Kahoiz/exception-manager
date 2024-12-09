<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\CarrierAnalyserInterface;

class CarrierAnalyser implements CarrierAnalyserInterface
{
    public function analyse($exceptions) : string
    {

        $carrierCounts = [];

        foreach ($exceptions as $exception) {
            $thrownPath = $exception['file'];
            //Carrier is, sadly, only in the path of the exception
            if (preg_match('/Carriers\/([^\/]+)\/Modules/', $thrownPath, $matches)) {

                $carrier = $matches[1];

                if (!isset($carrierCounts[$carrier])) {
                    $carrierCounts[$carrier] = 0;
                }
                $carrierCounts[$carrier]++;
            }
        }

        arsort($carrierCounts); //Sort by value, descending

        return array_key_first($carrierCounts);

    }

}

