<?php

namespace App\Service\Analysis;

use App\Service\Analysis\Interfaces\UserAnalyserInterface;

class UserAnalyser implements UserAnalyserInterface
{

    public function analyse($exceptions)
    {
        return $exceptions
            ->groupBy('user_id')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }
}
