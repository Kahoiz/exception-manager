<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\TypeAnalyserInterface;

class TypeAnalyser implements TypeAnalyserInterface
{
    public function analyse($exceptions)
    {
        return $exceptions
            ->groupBy('type')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }

    public static function makeTestList()
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

        return array_key_first($testList);

    }
}
