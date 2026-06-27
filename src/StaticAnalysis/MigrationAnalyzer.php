<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class MigrationAnalyzer
{
    public function analyze(array $files): array
    {
        $migrations = [];

        foreach ($files as $file) {
            if (str_contains($file, 'migrations' . DIRECTORY_SEPARATOR)) {
            }
        }

        return $migrations;
    }
}
