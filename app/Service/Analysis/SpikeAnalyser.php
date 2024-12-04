<?php

namespace App\Service\Analysis;
use App\Models\SpikeRules;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SpikeAnalyser implements Analyser
{


    public static function analyse($exceptions): bool
    {
        //get application from the exceptions array
        $application = $exceptions->first()['application'];
        if($exceptions->isEmpty()) {
            return false;
        }

        $exceptionsCount = $exceptions->count();

        //get ruleset for application or create new with default values
       $spikeRules = SpikeRules::where('application', $application)->first();
        if(!$spikeRules) {
            $data = [
                'application' => $application,
                'alpha' => 0.3,
                'threshold' => 50,
                'last_ema' => $exceptionsCount //Estimated Moving Average always starts with the first exception count
            ];

            $spikeRules = SpikeRules::create($data);
        }

        $alpha = $spikeRules->alpha;
        $threshold = $spikeRules->threshold;
        $previousEMA = $spikeRules->last_ema;

        $ema = $alpha * $exceptionsCount + (1 - $alpha) * $previousEMA;

        $spikeRules->last_ema = $ema;

        $spikeRules->save();

        //save ema history
        DB::table('ema_history')->insert(['EMA' => $ema, 'count' => $exceptionsCount, 'application' => $application]);


        return $exceptionsCount > $ema + $threshold;
    }

}

