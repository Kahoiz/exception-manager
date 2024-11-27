<?php

namespace App\Service;

use App\Service\Analysis\MessageAnalyser;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use App\Service\Analysis\UserAnalysis;
use App\Service\Analysis\VolumeAnalyser;
use Illuminate\Support\Collection;

class ExceptionAnalyser
{

    protected Collection $exceptions;

    public function __construct($exceptions)
    {
        $this->exceptions = collect($exceptions);
    }

    public function analyse(): array
    {
        return [
            'amount' => VolumeAnalyser::analyse($this->exceptions),
            'spike' => SpikeAnalyser::analyse($this->exceptions),
            'types' => TypeAnalyser::analyse($this->exceptions),
            'messages' => MessageAnalyser::analyse($this->exceptions),
            'throwers' => UserAnalysis::analyse($this->exceptions),
        ];
    }




}
