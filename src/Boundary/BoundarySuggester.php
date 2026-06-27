<?php

declare(strict_types=1);

namespace Carve\Boundary;

use Carve\Boundary\Algorithms\TableAffinityClusterer;
use Carve\Boundary\ValueObjects\BoundaryCandidate;
use Carve\Graph\Edge;
use Carve\Graph\GraphRepository;
use Carve\Graph\Node;
use Carve\Graph\WeightedGraph;

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

            $riskScoreObj = $this->riskScorer->scoreFromData($coupling->score, $cluster);
            $riskScore = $riskScoreObj->score;

            $candidates[] = new BoundaryCandidate(
                id: 'boundary:'.strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name)),
                name: $name,
                slug: strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name)),
                confidence: min($cluster['cohesion'] ?? 0.0 + (1.0 - $coupling->score), 1.0),
                cohesionScore: $cluster['cohesion'] ?? 0.0,
                couplingScore: $coupling->score,
                riskScore: $riskScore,
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

    private function loadGraph(string $graphPath): ?WeightedGraph
    {
        if (! file_exists($graphPath)) {
            return null;
        }

        $data = json_decode(file_get_contents($graphPath), true);

        if ($data === null || ! isset($data['nodes'])) {
            return null;
        }

        $graph = new WeightedGraph;

        foreach ($data['nodes'] as $nodeData) {
            $graph->addNode(new Node(
                id: $nodeData['id'],
                type: $nodeData['type'],
                name: $nodeData['name'],
                label: $nodeData['label'],
                meta: $nodeData['meta'] ?? [],
            ));
        }

        foreach ($data['edges'] as $edgeData) {
            $graph->addEdge(new Edge(
                from: $edgeData['from'],
                to: $edgeData['to'],
                type: $edgeData['type'],
                weight: (float) $edgeData['weight'],
                evidence: $edgeData['evidence'] ?? [],
            ));
        }

        $this->repository->save($graph);

        return $graph;
    }
}
