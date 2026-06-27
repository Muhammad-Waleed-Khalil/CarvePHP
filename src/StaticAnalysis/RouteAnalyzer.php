<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class RouteAnalyzer
{
    public function analyze(array $files): array
    {
        $routes = [];

        foreach ($files as $file) {
            if (str_contains($file, 'routes' . DIRECTORY_SEPARATOR)) {
            }
        }

        return $routes;
    }
}
