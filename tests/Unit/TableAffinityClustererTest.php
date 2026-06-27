<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Boundary\Algorithms\TableAffinityClusterer;
use Carve\Graph\Node;
use Carve\Graph\Edge;
use Carve\Graph\WeightedGraph;
use Carve\Graph\NodeType;
use PHPUnit\Framework\TestCase;

final class TableAffinityClustererTest extends TestCase
{
    private TableAffinityClusterer $clusterer;

    protected function setUp(): void
    {
        $this->clusterer = new TableAffinityClusterer();
    }

    public function test_returns_empty_for_empty_graph(): void
    {
        $graph = new WeightedGraph();
        $clusters = $this->clusterer->cluster($graph);

        $this->assertEmpty($clusters);
    }

    public function test_returns_empty_for_no_table_nodes(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('route:GET:/api/test', NodeType::ROUTE, 'test', 'Test'));
        $clusters = $this->clusterer->cluster($graph);

        $this->assertEmpty($clusters);
    }

    public function test_clusters_connected_tables(): void
    {
        $graph = new WeightedGraph();
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addNode(new Node('table:payments', NodeType::TABLE, 'payments', 'payments'));
        $graph->addNode(new Node('table:users', NodeType::TABLE, 'users', 'users'));

        $graph->addEdge(new Edge('table:invoices', 'table:payments', 'co_occurs', 10.0));
        $graph->addEdge(new Edge('table:invoices', 'table:users', 'co_occurs', 2.0));

        $clusters = $this->clusterer->cluster($graph, 1);

        $this->assertNotEmpty($clusters);
    }
}
