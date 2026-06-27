<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class ModelAnalyzer
{
    public function analyze(array $files): array
    {
        $models = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Models' . DIRECTORY_SEPARATOR)) {
            }
        }

        return $models;
    }
}
