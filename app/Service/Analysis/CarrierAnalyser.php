<?php

namespace App\Service\Analysis;

class CarrierAnalyser implements CarrierAnalyserInterface
{
    public function analyse($exceptions) : string
    {


        $carrierCounts = [];

        foreach ($exceptions as $exception) {

            $thrownPath = $exception['file'];

            if (preg_match('/Carriers\/([^\/]+)\/Modules/', $thrownPath, $matches)) {

                $carrier = $matches[1];

                if (!isset($carrierCounts[$carrier])) {

                    $carrierCounts[$carrier] = 0;
                }
                $carrierCounts[$carrier]++;
            }
        }

        arsort($carrierCounts);

        return array_key_first($carrierCounts);

    }

}

