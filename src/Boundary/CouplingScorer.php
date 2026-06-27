<?php

declare(strict_types=1);

namespace Carve\Boundary;

use Carve\Boundary\ValueObjects\CouplingScore;
use Carve\Graph\WeightedGraph;

final class CouplingScorer
{
    public function score(WeightedGraph $graph, array $cluster): CouplingScore
    {
        $internalWeight = 0.0;
        $externalWeight = 0.0;
        $totalWeight = 0.0;

        $clusterIds = $cluster['node_ids'] ?? $cluster['tables'] ?? [];

        foreach ($graph->getEdges() as $edge) {
            $fromInCluster = $this->isInCluster($edge->from, $clusterIds);
            $toInCluster = $this->isInCluster($edge->to, $clusterIds);

            if ($fromInCluster && $toInCluster) {
                $internalWeight += $edge->weight;
            } elseif ($fromInCluster || $toInCluster) {
                $externalWeight += $edge->weight;
            }

            if ($fromInCluster || $toInCluster) {
                $totalWeight += $edge->weight;
            }
        }

        $cohesion = $totalWeight > 0 ? $internalWeight / $totalWeight : 0.0;
        $coupling = $totalWeight > 0 ? $externalWeight / $totalWeight : 0.0;

        return new CouplingScore(
            score: min($coupling, 1.0),
            cohesion: min($cohesion, 1.0),
            internalWeight: $internalWeight,
            externalWeight: $externalWeight,
        );
    }

    private function isInCluster(string $id, array $clusterIds): bool
    {
        foreach ($clusterIds as $clusterId) {
            if (str_contains($id, $clusterId)) {
                return true;
            }
        }

        return false;
    }
}
