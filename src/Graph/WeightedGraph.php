<?php

declare(strict_types=1);

namespace Carve\Graph;

final class WeightedGraph
{
    private array $nodes = [];
    private array $edges = [];
    private array $adjacency = [];

    public function addNode(Node $node): void
    {
        $this->nodes[$node->id] = $node;
    }

    public function addEdge(Edge $edge): void
    {
        $this->edges[] = $edge;
        $this->adjacency[$edge->from][$edge->to] = ($this->adjacency[$edge->from][$edge->to] ?? 0) + $edge->weight;
        $this->adjacency[$edge->to][$edge->from] = ($this->adjacency[$edge->to][$edge->from] ?? 0) + $edge->weight;
    }

    public function getNode(string $id): ?Node
    {
        return $this->nodes[$id] ?? null;
    }

    public function getNodes(): array
    {
        return array_values($this->nodes);
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function getNeighbors(string $nodeId): array
    {
        return array_keys($this->adjacency[$nodeId] ?? []);
    }

    public function getEdgeWeight(string $from, string $to): float
    {
        return $this->adjacency[$from][$to] ?? 0.0;
    }

    public function toArray(): array
    {
        return [
            'nodes' => array_map(fn (Node $n) => $n->toArray(), $this->getNodes()),
            'edges' => array_map(fn (Edge $e) => $e->toArray(), $this->getEdges()),
        ];
    }
}
