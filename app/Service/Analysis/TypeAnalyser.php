<?php

namespace App\Service\Analysis;

class TypeAnalyser
{
    public static function analyse($exceptions)
    {
        return $exceptions
            ->groupBy('type')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }

    private static function makeTestList()
    {
        $data = [
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/IcaPaket/Modules/ShipmentModule.php',
            'app/Models/Carriers/DFM/Modules/ShipmentModule.php',
            'app/Models/Carriers/DAO/Modules/ShipmentModule.php',


        ];
        $testList = [];
        foreach ($data as $path) {
            if (preg_match('/Carriers\/([^\/]+)\/Modules/', $path, $matches)) {
                $carrier = $matches[1];
                if (!isset($testList[$carrier])) {
                    $testList[$carrier] = 0;
                }
                $testList[$carrier]++;
            }
        }

        arsort($testList);

        $collection =  collect($testList);

        if ($collection->first() > $collection->sum() * 0.8) {
            return $collection->keys()->first();
        }
        return $testList->first()['type'];
    }
}
