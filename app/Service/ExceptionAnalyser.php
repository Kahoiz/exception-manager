<?php

namespace App\Service;

use App\Models\Cause;
use App\Service\Analysis\CarrierAnalyser;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use Illuminate\Support\Collection;

class ExceptionAnalyser
{

    protected Collection $exceptions;
    protected string $application;

    public function __construct($exceptions, $application)
    {
        $this->exceptions = collect($exceptions);
        $this->application = $application;

    }

    public function detectSpike(): bool
    {
        return SpikeAnalyser::analyse($this->exceptions, $this->application);
    }

    public function findCause()
    {
        $types = TypeAnalyser::analyse($this->exceptions);
        //if the types array contains CarrierException, do stuff
        foreach($types as $type => $count){

            if(str_contains($type, 'CarrierException')){

                $carrier = CarrierAnalyser::analyse($this->exceptions->groupBy('type')->get($type));
                break;
            }

        }



    }


}
