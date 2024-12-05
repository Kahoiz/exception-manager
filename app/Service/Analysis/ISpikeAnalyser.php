<?php

namespace App\Service\Analysis;

interface ISpikeAnalyser
{
    public function DetectSpike($exceptions);
}
