<?php

namespace App\Service;

use App\Models\Cause;
use App\Service\Analysis\CarrierAnalyserInterface;
use App\Service\Analysis\SpikeAnalyserInterface;
use App\Service\Analysis\TypeAnalyserInterface;
use Illuminate\Support\Collection;

class ExceptionAnalyser
{
    private TypeAnalyserInterface $typeAnalyser;
    private CarrierAnalyserInterface $carrierAnalyser;
    private SpikeAnalyserInterface $spikeAnalyser;

    /**
     * ExceptionAnalyser class constructor.
     *
     * @param TypeAnalyserInterface $typeAnalyser An instance of ITypeAnalyser.
     * @param CarrierAnalyserInterface $carrierAnalyser An instance of ICarrierAnalyser.
     * @param SpikeAnalyserInterface $spikeAnalyser An instance of ISpikeAnalyser.
     */
    public function __construct(
        TypeAnalyserInterface    $typeAnalyser,
        CarrierAnalyserInterface $carrierAnalyser,
        SpikeAnalyserInterface   $spikeAnalyser)
    {
        $this->typeAnalyser = $typeAnalyser;
        $this->carrierAnalyser = $carrierAnalyser;
        $this->spikeAnalyser = $spikeAnalyser;
    }

    /**
     * Detects if there is a spike in the given exception logs for the specified application.
     *
     * @param Collection $exceptionLogs A collection of exception logs.
     * @param string $application The name of the application.
     * @return bool Returns true if a spike is detected, false otherwise.
     */
    public function detectSpike($exceptionLogs, $application): bool
    {
        return $this->spikeAnalyser->detectSpike($exceptionLogs , $application);
    }

    /**
     * Finds the cause of the exceptions in the given exception logs.
     *
     * @param Collection $exceptionLogs A collection of exception logs.
     */
    public function findCause($exceptionLogs)
    {
        $types = $this->typeAnalyser->analyse($exceptionLogs);
        // If the types array contains CarrierException, do stuff
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
