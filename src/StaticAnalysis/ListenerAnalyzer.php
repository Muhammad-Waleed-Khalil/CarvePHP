<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class ListenerAnalyzer
{
    public function analyze(array $files): array
    {
        $listeners = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Listeners'.DIRECTORY_SEPARATOR)) {
            }
        }

        return $listeners;
    }
}
