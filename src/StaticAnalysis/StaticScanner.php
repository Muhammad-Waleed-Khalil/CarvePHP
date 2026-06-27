<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Carve\StaticAnalysis\ValueObjects\StaticScanResult;
use Carve\StaticAnalysis\ValueObjects\TableInfo;

final class StaticScanner
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

        $routes = $this->routeAnalyzer->analyze($files);
        $classes = $this->controllerAnalyzer->analyze($files);
        $models = $this->modelAnalyzer->analyze($files);
        $migrations = $this->migrationAnalyzer->analyze($files);
        $dbUsages = $this->dbUsageAnalyzer->analyze($files);

        $tables = $this->buildTableList($models, $migrations);
        $warnings = $this->collectWarnings($routes, $models, $migrations);

        $result = new StaticScanResult(
            meta: [
                'generated_at' => date('c'),
                'php_version' => PHP_VERSION,
                'scanner_version' => '0.1.0',
            ],
            routes: array_map(fn ($r) => $r->toArray(), $routes),
            classes: array_map(fn ($c) => $c->toArray(), $classes),
            models: array_map(fn ($m) => $m->toArray(), $models),
            tables: array_map(fn ($t) => $t->toArray(), $tables),
            migrations: array_map(fn ($m) => $m->toArray(), $migrations),
            dbUsages: $dbUsages,
            edges: [],
            warnings: $warnings,
        );

        return $result->toArray();
    }

    private function buildTableList(array $models, array $migrations): array
    {
        $tables = [];
        $seen = [];

        foreach ($models as $model) {
            $table = $model->table;
            if ($table !== null && ! isset($seen[$table])) {
                $seen[$table] = true;
                $tables[] = new TableInfo(
                    name: $table,
                    modelClass: $model->class,
                    source: 'model',
                );
            }
        }

        foreach ($migrations as $migration) {
            foreach ($migration->createdTables as $table) {
                if (! isset($seen[$table])) {
                    $seen[$table] = true;
                    $tables[] = new TableInfo(
                        name: $table,
                        modelClass: null,
                        source: 'migration',
                    );
                }
            }
        }

        return $tables;
    }

    private function collectWarnings(array $routes, array $models, array $migrations): array
    {
        $warnings = [];

        $modelTables = [];
        foreach ($models as $model) {
            if ($model->table !== null) {
                $modelTables[$model->table] = $model->class;
            }
        }

        $migrationTables = [];
        foreach ($migrations as $migration) {
            foreach ($migration->createdTables as $table) {
                $migrationTables[$table] = true;
            }
        }

        foreach ($modelTables as $table => $class) {
            if (! isset($migrationTables[$table])) {
                $warnings[] = "Model {$class} references table '{$table}' but no migration creates it (table may exist from package or legacy schema)";
            }
        }

        $unused = array_diff(array_keys($migrationTables), array_keys($modelTables));
        foreach ($unused as $table) {
            $warnings[] = "Migration creates table '{$table}' but no matching Eloquent model found";
        }

        return $warnings;
    }
}
