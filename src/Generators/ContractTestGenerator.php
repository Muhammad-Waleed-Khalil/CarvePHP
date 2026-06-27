<?php

declare(strict_types=1);

namespace Carve\Generators;

final class ContractTestGenerator
{
    public function __construct(
        private readonly StubRenderer $renderer,
        private readonly FileWriter $writer,
    ) {}

    public function generate(string $boundary, array $boundaryData, string $outputDir = 'carve-output/monolith/tests'): array
    {
        $className = ucfirst($boundary).'ContractTest';
        $namespace = 'Tests\\Contracts';

        $methods = '';
        foreach ($boundaryData['routes'] ?? [] as $route) {
            $methods .= $this->generateTestMethod($route, $boundary);
        }

        $content = $this->renderer->render('tests/ContractTest.php.stub', [
            'TEST_NAMESPACE' => $namespace,
            'TEST_CLASS_NAME' => $className,
            'BOUNDARY_SLUG' => $boundary,
            'TEST_METHODS' => $methods,
        ]);

        $path = "{$outputDir}/{$className}.php";
        $this->writer->write($path, $content, mkdir: true);

        return [
            'path' => $path,
            'type' => 'contract_test',
            'status' => 'created',
        ];
    }

    private function generateTestMethod(string $route, string $boundary): string
    {
        $parts = explode(' ', $route);
        $method = strtolower($parts[0] ?? 'get');
        $path = $parts[1] ?? '/';
        $testName = 'test_'.strtolower($method).'_'.str_replace(['/', '-'], '_', trim($path, '/'));

        return "
    public function {$testName}_shape(): void
    {
        \$monolith = \$this->getJson('{$path}')->json();
        \$service = Http::baseUrl(\$this->serviceBaseUrl)
            ->{$method}('{$path}')
            ->json();

        \$this->assertShapeMatch(\$monolith, \$service);
    }
";
    }
}
