<?php

namespace App\Service\Analysis;

class MessageAnalyser
{
    public static function analyse($exceptions): array
    {
        return $exceptions
            ->groupBy('message')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }
}
