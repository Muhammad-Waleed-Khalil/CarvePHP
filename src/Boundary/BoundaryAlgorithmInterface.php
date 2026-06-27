<?php

declare(strict_types=1);

namespace Carve\Boundary;

use Carve\Graph\WeightedGraph;

interface BoundaryAlgorithmInterface
{
    public function cluster(WeightedGraph $graph, int $minClusterSize = 2): array;
}
