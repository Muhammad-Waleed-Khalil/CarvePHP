<?php

declare(strict_types=1);

namespace Carve\Boundary\ValueObjects;

final class BoundaryCandidate
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly float $confidence,
        public readonly float $cohesionScore,
        public readonly float $couplingScore,
        public readonly float $riskScore,
        public readonly array $routes = [],
        public readonly array $controllers = [],
        public readonly array $models = [],
        public readonly array $tables = [],
        public readonly array $events = [],
        public readonly array $jobs = [],
        public readonly array $externalDependencies = [],
        public readonly array $explanation = [],
        public readonly array $warnings = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'confidence' => $this->confidence,
            'cohesion_score' => $this->cohesionScore,
            'coupling_score' => $this->couplingScore,
            'risk_score' => $this->riskScore,
            'routes' => $this->routes,
            'controllers' => $this->controllers,
            'models' => $this->models,
            'tables' => $this->tables,
            'events' => $this->events,
            'jobs' => $this->jobs,
            'external_dependencies' => $this->externalDependencies,
            'explanation' => $this->explanation,
            'warnings' => $this->warnings,
        ];
    }
}
