<?php

namespace App\Service\Analysis;

use App\Models\SpikeRules;
use App\Service\Analysis\Interfaces\SpikeAnalyserInterface;
use Illuminate\Support\Facades\DB;

class SpikeAnalyser implements SpikeAnalyserInterface
{
    public function detectSpike($exceptions, $application): bool
    {
        if ($exceptions->isEmpty()) {
            return false;
        }

        $exceptionsCount = $exceptions->count();

        //get ruleset for application or create new with default values
        $spikeRules = SpikeRules::where('application', $application)->first();
        if (!$spikeRules) {
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
        $ema = round($ema,2);
        $spikeRules->last_ema = $ema;

        $spikeRules->save();

        //save ema history
        // Currently violates the SRP principle.
        // The data is not used anywhere else in the application,
        // nor do we anticipate it to be used in the near future.
        // therefor we see no reason to extract this to a separate class.
        DB::table('ema_history')->insert([
            'EMA'           => $ema,
            'count'         => $exceptionsCount,
            'application'   => $application,
            'created_at'    => now(),
            'updated_at'    => now()]);

        return $exceptionsCount > $ema + $threshold;
    }
}
