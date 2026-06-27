<?php

declare(strict_types=1);

namespace Carve\Generators;

final class StranglerConfigGenerator
{
    public function __construct(
        private readonly FileWriter $writer,
    ) {}

    public function generate(array $boundaries, string $outputDir = 'carve-output/monolith/config'): array
    {
        $files = [];

        $config = [
            'routes' => [],
        ];

        foreach ($boundaries as $boundary) {
            $slug = $boundary['slug'] ?? 'unknown';
            $envPrefix = strtoupper($slug);

            foreach ($boundary['routes'] ?? [] as $route) {
                $parts = explode(' ', $route);
                $path = $parts[1] ?? '/';

                $config['routes'][] = [
                    'boundary' => $slug,
                    'pattern' => $path . '*',
                    'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                    'target' => "env({$envPrefix}_SERVICE_URL)",
                    'enabled' => "env(CARVE_SERVICE_{$envPrefix}_ENABLED, false)",
                    'shadow' => "env(CARVE_SERVICE_{$envPrefix}_SHADOW, false)",
                ];
            }
        }

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = "{$outputDir}/carve_services.php";

        $this->writer->write($path, $content, mkdir: true);

        $files[] = [
            'path' => $path,
            'type' => 'strangler_config',
            'status' => 'created',
        ];

        return $files;
    }
}
