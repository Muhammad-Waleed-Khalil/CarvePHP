<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class StaticScanner implements StaticScannerInterface
{
    public function __construct(
        private readonly SourceFileFinder $fileFinder,
        private readonly RouteAnalyzer $routeAnalyzer,
        private readonly ControllerAnalyzer $controllerAnalyzer,
        private readonly ModelAnalyzer $modelAnalyzer,
        private readonly MigrationAnalyzer $migrationAnalyzer,
        private readonly DbUsageAnalyzer $dbUsageAnalyzer,
    ) {}

    public function scan(array $paths, array $exclude = []): array
    {
        $files = $this->fileFinder->find($paths, $exclude);

        return [
            'meta' => [
                'generated_at' => date('c'),
                'php_version' => PHP_VERSION,
                'scanner_version' => '0.1.0',
            ],
            'routes' => $this->routeAnalyzer->analyze($files),
            'classes' => $this->controllerAnalyzer->analyze($files),
            'models' => $this->modelAnalyzer->analyze($files),
            'tables' => [],
            'migrations' => $this->migrationAnalyzer->analyze($files),
            'db_usages' => $this->dbUsageAnalyzer->analyze($files),
            'edges' => [],
            'warnings' => [],
        ];
    }
}
