<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class StaticScanResult
{
    public function __construct(
        public readonly array $meta,
        public readonly array $routes,
        public readonly array $classes,
        public readonly array $models,
        public readonly array $tables,
        public readonly array $migrations,
        public readonly array $dbUsages,
        public readonly array $edges,
        public readonly array $warnings,
    ) {}

    public function toArray(): array
    {
        return [
            'meta' => $this->meta,
            'routes' => $this->routes,
            'classes' => $this->classes,
            'models' => $this->models,
            'tables' => $this->tables,
            'migrations' => $this->migrations,
            'db_usages' => $this->dbUsages,
            'edges' => $this->edges,
            'warnings' => $this->warnings,
        ];
    }
}
