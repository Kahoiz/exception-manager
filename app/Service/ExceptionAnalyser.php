<?php

namespace App\Service;

use App\Models\Cause;
use App\Service\Analysis\ICarrierAnalyser;
use App\Service\Analysis\ISpikeAnalyser;
use App\Service\Analysis\ITypeAnalyser;
use Illuminate\Support\Collection;

class ExceptionAnalyser
{
    private ITypeAnalyser $typeAnalyser;
    private ICarrierAnalyser $carrierAnalyser;
    private ISpikeAnalyser $spikeAnalyser;


    //All the analysers are injected into the constructor
    public function __construct(
                                ITypeAnalyser $typeAnalyser,
                                ICarrierAnalyser $carrierAnalyser,
                                ISpikeAnalyser $spikeAnalyser)
    {
        $this->typeAnalyser = $typeAnalyser;
        $this->carrierAnalyser = $carrierAnalyser;
        $this->spikeAnalyser = $spikeAnalyser;
    }

    public function detectSpike($exceptionLogs, $application): bool
    {
        return $this->spikeAnalyser->DetectSpike($exceptionLogs , $application);
    }

    public function findCause($exceptionLogs, $application)
    {

        $types = $this->typeAnalyser->analyse($exceptionLogs);
        //if the types array contains CarrierException, do stuff
        foreach ($types as $type => $count) {
            if (str_contains($type, 'CarrierException')) {
                $carrier = $this->carrierAnalyser::analyse($exceptionLogs
                    ->groupBy('type')
                    ->get($type));
                break;
            }
        }
    }
}
