<?php

declare(strict_types=1);

namespace Carve\Graph\ValueObjects;

use Carve\Graph\WeightedGraph;

final class GraphBuildResult
{
    public function __construct(
        public readonly int $nodeCount,
        public readonly int $edgeCount,
        public readonly WeightedGraph $graph,
    ) {}

    public function toArray(): array
    {
        return [
            'node_count' => $this->nodeCount,
            'edge_count' => $this->edgeCount,
            'graph' => $this->graph->toArray(),
        ];
    }
}
