<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class EnvConfigAnalyzer
{
    public function analyze(array $files): array
    {
        $configs = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if (preg_match_all('/config\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                $configs[] = [
                    'file' => $file,
                    'config_keys' => $matches[1],
                ];
            }

            if (preg_match_all('/env\([\'"]([^\'"]+)[\'"]\)/', $content, $envMatches)) {
                $configs[] = [
                    'file' => $file,
                    'env_keys' => $envMatches[1],
                ];
            }
        }

        return $configs;
    }
}
