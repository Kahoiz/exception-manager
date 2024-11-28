<?php

namespace App\Service\Analysis;

class TypeAnalyser
{
    public static function analyse($exceptions): array
    {
        return $exceptions
            ->groupBy('type')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }
}
