<?php

namespace App\Service\Analysis;

class TypeAnalyser
{
    public static function analyse($exceptions): array
    {
        return $exceptions
            ->groupBy('type')
            ->map(fn($logs, $type) => ['type' => $type, 'count' => count($logs)])
            ->values()
            ->toArray();
    }
}
