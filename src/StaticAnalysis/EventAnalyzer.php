<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class EventAnalyzer
{
    public function analyze(array $files): array
    {
        $events = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Events'.DIRECTORY_SEPARATOR)) {
            }
        }

        return $events;
    }
}
