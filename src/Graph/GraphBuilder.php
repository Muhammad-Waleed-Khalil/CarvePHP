<?php

declare(strict_types=1);

namespace Carve\Graph;

use Carve\Graph\ValueObjects\GraphBuildResult;

final class GraphBuilder
{
    public function __construct(
        private readonly GraphRepository $repository,
    ) {}

    public function build(string $staticScanPath, string $traceSource = 'database'): GraphBuildResult
    {
        $graph = new WeightedGraph();
        $staticData = $this->loadStaticData($staticScanPath);
        $traceData = $this->loadTraceData($traceSource);

        $this->addStaticNodes($graph, $staticData);
        $this->addStaticEdges($graph, $staticData);
        $this->addTraceEdges($graph, $traceData);

        $this->repository->save($graph);

        return new GraphBuildResult(
            nodeCount: count($graph->getNodes()),
            edgeCount: count($graph->getEdges()),
            graph: $graph,
        );
    }

    private function loadStaticData(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function loadTraceData(string $source): array
    {
        return [];
    }

    private function addStaticNodes(WeightedGraph $graph, array $data): void {}

    private function addStaticEdges(WeightedGraph $graph, array $data): void {}

    private function addTraceEdges(WeightedGraph $graph, array $data): void {}
}
