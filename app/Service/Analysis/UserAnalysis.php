<?php

namespace App\Service\Analysis;

class UserAnalysis
{

    public static function analyse($exceptions)
    {
        return $exceptions
            ->groupBy('user_id')
            ->map(fn($logs, $user_id) => ['user_id' => $user_id, 'count' => count($logs)])
            ->values()
            ->toArray();
    }
}
