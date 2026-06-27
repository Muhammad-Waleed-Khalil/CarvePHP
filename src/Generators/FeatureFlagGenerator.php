<?php

declare(strict_types=1);

namespace Carve\Generators;

final class FeatureFlagGenerator
{
    public function __construct(
        private readonly FileWriter $writer,
    ) {}

    public function generate(array $boundaries, string $outputDir = 'carve-output/monolith/config'): array
    {
        $services = [];

        foreach ($boundaries as $boundary) {
            $slug = $boundary['slug'] ?? 'unknown';
            $envPrefix = strtoupper($slug);

            $services[$slug] = [
                'enabled' => "env(CARVE_SERVICE_{$envPrefix}_ENABLED, false)",
                'base_url' => "env({$envPrefix}_SERVICE_URL, 'http://localhost:8081')",
                'shadow' => "env(CARVE_SERVICE_{$envPrefix}_SHADOW, false)",
                'rollout_percentage' => "(int) env(CARVE_SERVICE_{$envPrefix}_ROLLOUT, 0)",
            ];
        }

        $content = "<?php\n\nreturn [\n    'services' => ".var_export($services, true).",\n];\n";
        $path = "{$outputDir}/carve_features.php";

        $this->writer->write($path, $content, mkdir: true);

        return [
            [
                'path' => $path,
                'type' => 'feature_flag_config',
                'status' => 'created',
            ],
        ];
    }
}
