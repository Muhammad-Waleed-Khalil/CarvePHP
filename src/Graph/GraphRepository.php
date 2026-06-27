<?php

declare(strict_types=1);

namespace Carve\Graph;

final class GraphRepository
{
    private ?WeightedGraph $graph = null;

    public function save(WeightedGraph $graph): void
    {
        $this->graph = $graph;
    }

    public function load(): ?WeightedGraph
    {
        return $this->graph;
    }
}
