<?php

declare(strict_types=1);

namespace Carve\Generators;

final class ClientSdkGenerator
{
    public function __construct(
        private readonly StubRenderer $renderer,
        private readonly FileWriter $writer,
    ) {}

    public function generate(string $boundary, string $boundariesPath, string $outputDir = 'carve-output/clients'): array
    {
        $boundaryData = $this->loadBoundaryData($boundary, $boundariesPath);
        $className = ucfirst($boundary).'ServiceClient';
        $namespace = config('carve.generation.client_namespace', 'App\\Clients');

        $methods = '';
        foreach ($boundaryData['routes'] ?? [] as $route) {
            $methods .= $this->generateMethod($route);
        }

        $content = $this->renderer->render('client-sdk/Client.php.stub', [
            'CLIENT_NAMESPACE' => $namespace,
            'CLIENT_CLASS_NAME' => $className,
            'CLIENT_METHODS' => $methods,
        ]);

        $path = "{$outputDir}/{$className}.php";
        $this->writer->write($path, $content, mkdir: true);

        return [
            'path' => $path,
            'type' => 'client_sdk',
            'status' => 'created',
        ];
    }

    private function loadBoundaryData(string $boundary, string $path): array
    {
        if (! file_exists($path)) {
            return ['name' => $boundary, 'routes' => []];
        }

        $data = json_decode(file_get_contents($path), true) ?? [];

        foreach ($data['candidates'] ?? [] as $candidate) {
            if (($candidate['slug'] ?? '') === $boundary) {
                return $candidate;
            }
        }

        return ['name' => $boundary, 'routes' => []];
    }

    private function generateMethod(string $route): string
    {
        $parts = explode(' ', $route);
        $method = strtolower($parts[0] ?? 'get');
        $path = $parts[1] ?? '/';
        $methodName = lcfirst(str_replace(['/', '-', '_'], '', ucwords($path, '/-_'))) ?: 'index';

        return "
    public function {$methodName}(array \$payload = [], array \$headers = []): array
    {
        \$response = \$this->http(\$headers)->{$method}('{$path}', \$payload);
        \$response->throw();

        return \$response->json();
    }
";
    }
}
