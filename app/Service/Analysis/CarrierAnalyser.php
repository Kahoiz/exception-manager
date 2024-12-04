<?php

namespace App\Service\Analysis;

class CarrierAnalyser
{
    public static function analyse($exceptions, $type)
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
        //if the first key is more than 80% of the sum of the array, return the name of the carrier else return carrierexception
        $collection =  collect($carrierCounts);

        if ($collection->first() > $collection->sum() * 0.8) {

            return $collection->keys()->first();
        }
        return $type;

    }
}
