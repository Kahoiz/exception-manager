<?php

namespace App\Service\Analysis\Interfaces;

use App\Service\Analysis\Collection;

interface SpikeAnalyserInterface
{
   /**
 * Detects if there is a spike in the given exception logs for the specified application.
 *
 * @param Collection $exceptions A collection of exception logs.
 * @param string $application The name of the application.
 * @return bool Returns true if a spike is detected, false otherwise.
 */
public function detectSpike($exceptions, $application) : bool;
}
