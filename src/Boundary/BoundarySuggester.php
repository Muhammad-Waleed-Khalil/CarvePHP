<?php

declare(strict_types=1);

namespace Carve\Boundary;

use Carve\Boundary\Algorithms\TableAffinityClusterer;
use Carve\Boundary\ValueObjects\BoundaryCandidate;
use Carve\Graph\GraphRepository;

final class BoundarySuggester
{
    public function __construct(
        private readonly GraphRepository $repository,
        private readonly TableAffinityClusterer $clusterer,
        private readonly BoundaryNameGuesser $nameGuesser,
        private readonly RiskScorer $riskScorer,
        private readonly CouplingScorer $couplingScorer,
    ) {}

    public function suggest(string $graphPath, string $algorithm = 'table_affinity', int $minClusterSize = 2): array
    {
        $graph = $this->loadGraph($graphPath);

        if ($graph === null) {
            return [
                'meta' => [
                    'generated_at' => date('c'),
                    'algorithm' => $algorithm,
                    'min_cluster_size' => $minClusterSize,
                ],
                'candidates' => [],
                'warnings' => ['No graph data available. Run carve:analyze first.'],
            ];
        }

        $clusters = $this->clusterer->cluster($graph, $minClusterSize);
        $candidates = [];

        foreach ($clusters as $cluster) {
            $name = $this->nameGuesser->guess($cluster);
            $coupling = $this->couplingScorer->score($graph, $cluster);
            $risk = $this->riskScorer->score($coupling, $cluster);

            $candidates[] = new BoundaryCandidate(
                id: 'boundary:' . $name,
                name: $name,
                slug: strtolower($name),
                confidence: 0.0,
                cohesionScore: $cluster['cohesion'] ?? 0.0,
                couplingScore: $coupling->score,
                riskScore: $risk->score,
                routes: $cluster['routes'] ?? [],
                controllers: $cluster['controllers'] ?? [],
                models: $cluster['models'] ?? [],
                tables: $cluster['tables'] ?? [],
                events: $cluster['events'] ?? [],
                jobs: $cluster['jobs'] ?? [],
                externalDependencies: $cluster['external_dependencies'] ?? [],
                explanation: $cluster['explanation'] ?? [],
                warnings: $cluster['warnings'] ?? [],
            );
        }

        return [
            'meta' => [
                'generated_at' => date('c'),
                'algorithm' => $algorithm,
                'min_cluster_size' => $minClusterSize,
            ],
            'candidates' => array_map(fn (BoundaryCandidate $c) => $c->toArray(), $candidates),
            'warnings' => [],
        ];
    }

    private function loadGraph(string $graphPath): mixed
    {
        if (! file_exists($graphPath)) {
            return null;
        }

        $data = json_decode(file_get_contents($graphPath), true);

        if ($data === null) {
            return null;
        }

        return $this->repository->load();
    }
}
