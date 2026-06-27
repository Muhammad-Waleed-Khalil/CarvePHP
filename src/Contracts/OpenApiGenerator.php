<?php

declare(strict_types=1);

namespace Carve\Contracts;

final class OpenApiGenerator
{
    public function generate(string $boundary, string $boundariesPath, string $format = 'yaml'): string
    {
        $boundaryData = $this->loadBoundaryData($boundary, $boundariesPath);

        if ($format === 'json') {
            return $this->generateJson($boundaryData);
        }

        return $this->generateYaml($boundaryData);
    }

    private function loadBoundaryData(string $boundary, string $path): array
    {
        if (! file_exists($path)) {
            return [
                'name' => $boundary,
                'routes' => [],
                'controllers' => [],
            ];
        }

        $data = json_decode(file_get_contents($path), true) ?? [];

        foreach ($data['candidates'] ?? [] as $candidate) {
            if (($candidate['slug'] ?? '') === $boundary) {
                return $candidate;
            }
        }

        return [
            'name' => $boundary,
            'routes' => [],
            'controllers' => [],
        ];
    }

    private function generateYaml(array $data): string
    {
        $yaml = "openapi: 3.1.0\n";
        $yaml .= "info:\n";
        $yaml .= '  title: '.($data['name'] ?? 'Unknown')." Service API\n";
        $yaml .= "  version: 0.1.0\n";
        $yaml .= "servers:\n";
        $yaml .= "  - url: http://localhost:8081\n";
        $yaml .= "paths: {}\n";
        $yaml .= "components:\n";
        $yaml .= "  securitySchemes:\n";
        $yaml .= "    bearerAuth:\n";
        $yaml .= "      type: http\n";
        $yaml .= "      scheme: bearer\n";
        $yaml .= "  schemas: {}\n";

        return $yaml;
    }

    private function generateJson(array $data): string
    {
        return json_encode([
            'openapi' => '3.1.0',
            'info' => [
                'title' => ($data['name'] ?? 'Unknown').' Service API',
                'version' => '0.1.0',
            ],
            'servers' => [['url' => 'http://localhost:8081']],
            'paths' => new \stdClass,
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
                ],
                'schemas' => new \stdClass,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
