<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Graph\Node;
use Carve\Graph\Edge;
use Carve\Graph\WeightedGraph;
use Carve\Graph\NodeType;
use Carve\Graph\EdgeType;
use PHPUnit\Framework\TestCase;

final class GraphBuilderTest extends TestCase
{
    public function test_can_add_nodes(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));

        $this->assertCount(1, $graph->getNodes());
    }

    public function test_can_add_edges(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addEdge(new Edge('route:GET:/api/invoices', 'table:invoices', EdgeType::TOUCHES_TABLE, 5.0));

        $this->assertCount(1, $graph->getEdges());
    }

    public function test_adjacency_returns_neighbors(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addEdge(new Edge('route:GET:/api/invoices', 'table:invoices', EdgeType::TOUCHES_TABLE, 5.0));

        $neighbors = $graph->getNeighbors('route:GET:/api/invoices');

        $this->assertContains('table:invoices', $neighbors);
    }

    public function test_edge_weight_is_accessible(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('a', 'test', 'a', 'A'));
        $graph->addNode(new Node('b', 'test', 'b', 'B'));
        $graph->addEdge(new Edge('a', 'b', 'connects', 3.0));

        $this->assertEquals(3.0, $graph->getEdgeWeight('a', 'b'));
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('n1', 'test', 'Node 1', 'Node 1'));
        $graph->addNode(new Node('n2', 'test', 'Node 2', 'Node 2'));
        $graph->addEdge(new Edge('n1', 'n2', 'connects', 1.0));

        $array = $graph->toArray();

        $this->assertArrayHasKey('nodes', $array);
        $this->assertArrayHasKey('edges', $array);
        $this->assertCount(2, $array['nodes']);
        $this->assertCount(1, $array['edges']);
    }
}
