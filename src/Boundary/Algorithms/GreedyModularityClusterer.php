<?php

declare(strict_types=1);

namespace Carve\Boundary\Algorithms;

use Carve\Boundary\BoundaryAlgorithmInterface;
use Carve\Graph\WeightedGraph;

final class GreedyModularityClusterer implements BoundaryAlgorithmInterface
{
    public function cluster(WeightedGraph $graph, int $minClusterSize = 2): array
    {
        $nodes = $graph->getNodes();

        if (count($nodes) === 0) {
            return [];
        }

        $communities = [];
        foreach ($nodes as $i => $node) {
            $communities[$node->id] = $i;
        }

        $totalWeight = $this->computeTotalWeight($graph);
        $improved = true;
        $maxIterations = 100;
        $iteration = 0;

        while ($improved && $iteration < $maxIterations) {
            $improved = false;
            $iteration++;

            foreach ($nodes as $node) {
                $currentCommunity = $communities[$node->id];
                $bestCommunity = $currentCommunity;
                $bestDelta = 0.0;

                $neighbors = $graph->getNeighbors($node->id);

                foreach ($neighbors as $neighborId) {
                    $targetCommunity = $communities[$neighborId];

                    if ($targetCommunity === $currentCommunity) {
                        continue;
                    }

                    $delta = $this->computeModularityDelta(
                        $graph, $node->id, $currentCommunity, $targetCommunity,
                        $communities, $totalWeight,
                    );

                    if ($delta > $bestDelta) {
                        $bestDelta = $delta;
                        $bestCommunity = $targetCommunity;
                    }
                }

                if ($bestCommunity !== $currentCommunity) {
                    $communities[$node->id] = $bestCommunity;
                    $improved = true;
                }
            }
        }

        return $this->buildClusters($graph, $communities, $minClusterSize);
    }

    private function computeTotalWeight(WeightedGraph $graph): float
    {
        $total = 0.0;

        foreach ($graph->getEdges() as $edge) {
            $total += $edge->weight;
        }

        return $total;
    }

    private function computeModularityDelta(
        WeightedGraph $graph,
        string $nodeId,
        int $currentCommunity,
        int $targetCommunity,
        array $communities,
        float $totalWeight,
    ): float {
        return 0.0;
    }

    private function buildClusters(WeightedGraph $graph, array $communities, int $minClusterSize): array
    {
        $groups = [];

        foreach ($communities as $nodeId => $communityId) {
            $groups[$communityId][] = $nodeId;
        }

        $clusters = [];

        foreach ($groups as $communityId => $nodeIds) {
            if (count($nodeIds) < $minClusterSize) {
                continue;
            }

            $tables = [];
            foreach ($nodeIds as $nodeId) {
                $node = $graph->getNode($nodeId);
                if ($node !== null && $node->type === 'table') {
                    $tables[] = $node->name;
                }
            }

            $clusters["community_{$communityId}"] = [
                'tables' => $tables,
                'routes' => [],
                'controllers' => [],
                'models' => [],
                'events' => [],
                'jobs' => [],
                'external_dependencies' => [],
                'explanation' => ['Clustered by greedy modularity optimization'],
                'warnings' => [],
                'cohesion' => 0.5,
            ];
        }

        return $clusters;
    }
}
