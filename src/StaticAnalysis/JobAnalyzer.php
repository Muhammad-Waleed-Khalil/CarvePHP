<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class JobAnalyzer
{
    public function analyze(array $files): array
    {
        $jobs = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Jobs' . DIRECTORY_SEPARATOR)) {
            }
        }

        return $jobs;
    }
}
