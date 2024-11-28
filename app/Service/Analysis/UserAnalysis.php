<?php

namespace App\Service\Analysis;

class UserAnalysis
{

    public static function analyse($exceptions)
    {
        return $exceptions
            ->groupBy('user_id')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }
}
