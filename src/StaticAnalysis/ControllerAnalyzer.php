<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class ControllerAnalyzer
{
    public function analyze(array $files): array
    {
        $controllers = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Http' . DIRECTORY_SEPARATOR . 'Controllers')) {
            }
        }

        return $controllers;
    }
}
