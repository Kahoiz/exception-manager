<?php

namespace App\Service;

use App\Jobs\CarrierHealthCheck;
use App\Models\Cause;
use App\Service\Analysis\Interfaces\CarrierAnalyserInterface;
use App\Service\Analysis\Interfaces\SpikeAnalyserInterface;
use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use Illuminate\Support\Collection;

class ExceptionAnalyser
{


    public function __construct(
        protected TypeAnalyserInterface    $typeAnalyser,
        protected CarrierAnalyserInterface $carrierAnalyser,
        protected SpikeAnalyserInterface   $spikeAnalyser)
    {

    }

    /**
     * @return bool Returns true if a spike is detected, false otherwise.
     */
    public function detectSpike($exceptionLogs, $application): bool
    {
        return $this->spikeAnalyser->detectSpike($exceptionLogs, $application);
    }

    /**
     * Finds the cause of the exceptions in the given exception logs.
     */
    public function identifyCause($exceptionLogs) : Cause
    {
        $cause = new Cause;

        $types = $this->typeAnalyser->analyse($exceptionLogs);

        $data['types'] = $types->keys()->toArray();


        if ($types->containsRequestException()) {
            CarrierHealthCheck::dispatch()->onQueue('health-check'); //to determine if carriers are alive
        }

        if ($types->containsCarrierException()) {

            $carrierLogs = $types->filter(function ($value, $key) {
                return str_contains($key, 'CarrierException');
            })->flatten(1); //flatten to remove the key

            $data['carrier'] = $this->carrierAnalyser->analyse($carrierLogs);
        }

        $cause->data = json_encode($data);

        return $cause;
    }
}
