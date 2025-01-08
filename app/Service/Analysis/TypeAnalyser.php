<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TypeAnalyser implements TypeAnalyserInterface
{
    // TODO: get this from the config
    private int $buffer = 30;

    /**
     * Analyzes the given collection of exceptions and returns the top 5 types.
     *
     * @param Collection $exceptions The collection of exceptions to analyze.
     * @return Collection The top 5 exception types.
     */
    public function analyse(Collection $exceptions): Collection
    {
        return $exceptions->groupBy('type')->sortDesc()->take(5);
    }

    /**
     * Detects anomalies in the given collection of exceptions.
     *
     * @param Collection $exceptions The collection of exceptions to analyze.
     * @return array An array of detected anomalies with their types and frequencies.
     */
    public function anomalyDetection(Collection $exceptions): array
    {
        // Map the exceptions to their types and frequency.
        $count = $exceptions->count(); // only need to count once
        $types = $exceptions->groupBy('type')->map(function ($group) use ($count) {
            return number_format(($group->count() / $count) * 100, 2);
        })->sortDesc();

        // Check if there is a sudden increase in the frequency of a type.

        $startDate = now()->subDays(1);

        $endDate = now();

        // Cache the historical data to avoid querying the whole table every time
        $cacheKey = 'historical_data_' . $startDate->format('Y_m_d') . '_' . $endDate->format('Y_m_d');
        $historicalData = cache()->remember($cacheKey, 1440, function () use ($types, $startDate, $endDate) {
            return DB::table('exception_logs')
                ->select('type', DB::raw('count(*) as frequency'))
                ->whereIn('type', $types->keys())
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('type')
                ->pluck('frequency', 'type');
        });

        // compare the historical data with the current data
        $historicalDataCount = $historicalData->sum();

        $result = [];

        foreach ($historicalData as $type => $frequency) {
            //(
            if(!$types->has($type)) {
                continue;
            }
            $frequency = number_format(($frequency / $historicalDataCount) * 100, 2);
            $result[$type] = $frequency > (int) $types[$type]+$this->buffer ? $frequency : 0;
        }
        return  $result;
    }
}
