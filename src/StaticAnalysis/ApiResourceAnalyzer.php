<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class ApiResourceAnalyzer
{
    public function analyze(array $files): array
    {
        $resources = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Http'.DIRECTORY_SEPARATOR.'Resources')) {
            }
        }

        return $resources;
    }
}
