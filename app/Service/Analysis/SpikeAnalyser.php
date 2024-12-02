<?php

namespace App\Service\Analysis;
use Illuminate\Support\Facades\DB;

class SpikeAnalyser
{


    public static function analyse($exceptions): bool
    {
        $alpha = 0.3;
        $threshold = 50;


        if($exceptions->isEmpty()) {
            return false;
        }

        $exceptionsCount = $exceptions->count();

        $application = $exceptions->first()['application'];


        //get last EMA
       $spikeRules = DB::table('spike_rules')->where('application', $application)->get();

        if($spikeRules->isEmpty()) {
            DB::table('spike_rules')->insert(['application' => $application, 'alpha' => $alpha, 'threshold' => $threshold, 'last_ema' => 0]);
        }

        $alpha = $spikeRules->value('alpha');
        $threshold = $spikeRules->value('threshold');
        $previousEMA = $spikeRules->value('last_ema');

        //check if last EMA is 0 and set it to the current exception count if null
        if($previousEMA <=0) {
            $previousEMA = $exceptionsCount;
        }


        $ema = $alpha * $exceptionsCount + (1 - $alpha) * $previousEMA;

        DB::table('spike_rules')->where('application', $application)->update(['last_ema' => $ema]);

        DB::table('exponential_moving_average')->insert(['EMA' => $ema, 'count' => $exceptionsCount]);


        return $exceptionsCount > $ema + $threshold;
    }
}

