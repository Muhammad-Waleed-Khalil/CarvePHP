<?php

declare(strict_types=1);

namespace Carve\Boundary\Algorithms;

use Carve\Boundary\BoundaryAlgorithmInterface;
use Carve\Graph\WeightedGraph;

final class TableAffinityClusterer implements BoundaryAlgorithmInterface
{
    public function cluster(WeightedGraph $graph, int $minClusterSize = 2): array
    {
        $tableNodes = $this->getTableNodes($graph);

        if (count($tableNodes) === 0) {
            return [];
        }

        $clusters = [];
        foreach ($tableNodes as $nodeId => $node) {
            $clusters[$nodeId] = [
                'tables' => [$node->name],
                'routes' => [],
                'controllers' => [],
                'models' => [],
                'events' => [],
                'jobs' => [],
                'external_dependencies' => [],
                'explanation' => [],
                'warnings' => [],
                'cohesion' => 1.0,
            ];
        }

        $edges = [];
        foreach ($graph->getEdges() as $edge) {
            if ($edge->type === 'co_occurs' || $edge->type === 'touches_table') {
                $edges[] = $edge;
            }
        }

        usort($edges, fn ($a, $b) => $b->weight <=> $a->weight);

        $merged = [];

        // Find the highest edge weight for threshold calculation
        $maxWeight = 0.0;
        foreach ($edges as $edge) {
            if ($edge->weight > $maxWeight) {
                $maxWeight = $edge->weight;
            }
        }

        // Only merge if edge weight is at least 20% of max (skip weak edges)
        $threshold = $maxWeight * 0.2;

        foreach ($edges as $edge) {
            if ($edge->weight < $threshold) {
                break; // edges are sorted descending, so rest are even weaker
            }

            $fromCluster = $this->findCluster($edge->from, $merged, $clusters);
            $toCluster = $this->findCluster($edge->to, $merged, $clusters);

            if ($fromCluster !== null && $toCluster !== null && $fromCluster !== $toCluster) {
                $mergedSize = count($clusters[$fromCluster]['tables']) + count($clusters[$toCluster]['tables']);

                if ($mergedSize <= $minClusterSize * 3) {
                    $clusters[$fromCluster]['tables'] = array_unique(array_merge(
                        $clusters[$fromCluster]['tables'],
                        $clusters[$toCluster]['tables'],
                    ));
                    $clusters[$fromCluster]['explanation'][] = "Merged {$edge->from} and {$edge->to} (weight: {$edge->weight})";
                    $merged[] = $toCluster;
                    unset($clusters[$toCluster]);
                }
            }
        }

        return array_filter($clusters, fn ($c) => count($c['tables']) >= $minClusterSize);
    }

    private function getTableNodes(WeightedGraph $graph): array
    {
        $nodes = [];

        foreach ($graph->getNodes() as $node) {
            if ($node->type === 'table') {
                $nodes[$node->id] = $node;
            }
        }

        return $nodes;
    }

    private function findCluster(string $nodeId, array $merged, array $clusters): ?string
    {
        $tableName = str_replace('table:', '', $nodeId);

        foreach ($merged as $clusterId) {
            if (isset($clusters[$clusterId]) && in_array($tableName, $clusters[$clusterId]['tables'])) {
                return $clusterId;
            }
        }

        foreach ($clusters as $clusterId => $cluster) {
            if (in_array($tableName, $cluster['tables'])) {
                return $clusterId;
            }
        }

        return null;
    }
}
