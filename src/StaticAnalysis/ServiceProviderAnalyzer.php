<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class ServiceProviderAnalyzer
{
    public function analyze(array $files): array
    {
        $providers = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Providers'.DIRECTORY_SEPARATOR)) {
            }
        }

        return $providers;
    }
}
