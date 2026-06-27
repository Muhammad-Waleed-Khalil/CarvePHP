<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Graph\Edge;
use Carve\Graph\EdgeType;
use Carve\Graph\GraphBuilder;
use Carve\Graph\GraphExporter;
use Carve\Graph\GraphRepository;
use Carve\Graph\Node;
use Carve\Graph\NodeType;
use Carve\Graph\WeightedGraph;
use PHPUnit\Framework\TestCase;

final class GraphBuilderTest extends TestCase
{
    private string $scanPath;

    private string $tracePath;

    protected function setUp(): void
    {
        $this->scanPath = __DIR__.'/../Fixtures/demo-scan.json';
        $this->tracePath = __DIR__.'/../Fixtures/test-traces.jsonl';
    }

    public function test_build_creates_nodes_and_edges_from_static_data(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);

        $this->assertGreaterThan(0, $result->nodeCount, 'Should create nodes');
        $this->assertGreaterThan(0, $result->edgeCount, 'Should create edges');

        $graph = $result->graph;

        // Verify route nodes exist
        $routeNodes = array_filter($graph->getNodes(), fn (Node $n) => $n->type === NodeType::ROUTE);
        $this->assertCount(9, $routeNodes, 'Should have 9 route nodes from fixture');

        // Verify controller nodes exist
        $controllerNodes = array_filter($graph->getNodes(), fn (Node $n) => $n->type === NodeType::CONTROLLER);
        $this->assertCount(4, $controllerNodes, 'Should have 4 controller nodes');

        // Verify model nodes exist
        $modelNodes = array_filter($graph->getNodes(), fn (Node $n) => $n->type === NodeType::MODEL);
        $this->assertCount(5, $modelNodes, 'Should have 5 model nodes');

        // Verify table nodes exist
        $tableNodes = array_filter($graph->getNodes(), fn (Node $n) => $n->type === NodeType::TABLE);
        $this->assertCount(5, $tableNodes, 'Should have 5 table nodes');
    }

    public function test_route_handled_by_edges_created(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::ROUTE_HANDLED_BY);
        $this->assertGreaterThan(0, count($edges), 'Should have route_handled_by edges');

        foreach ($edges as $edge) {
            $this->assertStringStartsWith('route:', $edge->from);
            $this->assertStringStartsWith('controller:', $edge->to);
            $this->assertEquals(3.0, $edge->weight);
        }
    }

    public function test_uses_model_edges_created(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::USES_MODEL);
        $this->assertGreaterThan(0, count($edges), 'Should have uses_model edges');

        foreach ($edges as $edge) {
            $this->assertStringStartsWith('controller:', $edge->from);
            $this->assertStringStartsWith('model:', $edge->to);
        }
    }

    public function test_model_owns_table_edges_created(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::MODEL_OWNS_TABLE);
        $this->assertCount(5, $edges, 'Should have model_owns_table edges for all 5 models');

        foreach ($edges as $edge) {
            $this->assertStringStartsWith('model:', $edge->from);
            $this->assertStringStartsWith('table:', $edge->to);
            $this->assertEquals(4.0, $edge->weight);
        }
    }

    public function test_touches_table_edges_created(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::TOUCHES_TABLE);
        $this->assertGreaterThan(0, count($edges), 'Should have touches_table edges');
    }

    public function test_trace_creates_co_occurs_edges(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::CO_OCCURS);
        $this->assertGreaterThan(0, count($edges), 'Should have co_occurs edges from runtime traces');

        // invoices and payments should co-occur (from trace data)
        $foundInvoicePayment = false;
        foreach ($edges as $edge) {
            if (
                (str_contains($edge->from, 'invoices') && str_contains($edge->to, 'payments')) ||
                (str_contains($edge->from, 'payments') && str_contains($edge->to, 'invoices'))
            ) {
                $foundInvoicePayment = true;
                $this->assertGreaterThan(6.0, $edge->weight, 'invoices-payments co-occurrence should have high weight');
                break;
            }
        }

        $this->assertTrue($foundInvoicePayment, 'invoices and payments should co-occur from trace data');
    }

    public function test_trace_creates_touches_table_with_higher_weight(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);
        $graph = $result->graph;

        $edges = array_filter($graph->getEdges(), fn (Edge $e) => $e->type === EdgeType::TOUCHES_TABLE);

        // At least some touches_table edges should have runtime weight (5.0) not just static weight (2.5)
        $hasRuntimeWeight = false;
        foreach ($edges as $edge) {
            if ($edge->weight >= 5.0) {
                $hasRuntimeWeight = true;
                break;
            }
        }

        $this->assertTrue($hasRuntimeWeight, 'Some touches_table edges should have runtime weight >= 5.0');
    }

    public function test_build_with_empty_static_data_returns_empty_graph(): void
    {
        $emptyPath = tempnam(sys_get_temp_dir(), 'carve-test-empty-');
        file_put_contents($emptyPath, json_encode([
            'meta' => [], 'routes' => [], 'classes' => [], 'models' => [],
            'tables' => [], 'migrations' => [], 'db_usages' => [], 'edges' => [], 'warnings' => [],
        ]));

        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($emptyPath, 'jsonl', null);

        $this->assertEquals(0, $result->nodeCount);
        $this->assertEquals(0, $result->edgeCount);

        unlink($emptyPath);
    }

    public function test_graph_is_exportable(): void
    {
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build($this->scanPath, 'jsonl', $this->tracePath);

        $outputPath = tempnam(sys_get_temp_dir(), 'carve-test-export-');
        (new GraphExporter)->exportToFile($result->graph, $outputPath);

        $this->assertFileExists($outputPath);
        $data = json_decode(file_get_contents($outputPath), true);
        $this->assertArrayHasKey('nodes', $data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertCount($result->nodeCount, $data['nodes']);
        $this->assertCount($result->edgeCount, $data['edges']);

        unlink($outputPath);
    }

    public function test_can_add_nodes(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));

        $this->assertCount(1, $graph->getNodes());
    }

    public function test_can_add_edges(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addEdge(new Edge('route:GET:/api/invoices', 'table:invoices', EdgeType::TOUCHES_TABLE, 5.0));

        $this->assertCount(1, $graph->getEdges());
    }

    public function test_adjacency_returns_neighbors(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('route:GET:/api/invoices', NodeType::ROUTE, 'invoices.index', 'invoices.index'));
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addEdge(new Edge('route:GET:/api/invoices', 'table:invoices', EdgeType::TOUCHES_TABLE, 5.0));

        $neighbors = $graph->getNeighbors('route:GET:/api/invoices');
        $this->assertContains('table:invoices', $neighbors);
    }

    public function test_edge_weight_is_accessible(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('a', 'test', 'a', 'A'));
        $graph->addNode(new Node('b', 'test', 'b', 'B'));
        $graph->addEdge(new Edge('a', 'b', 'connects', 3.0));

        $this->assertEquals(3.0, $graph->getEdgeWeight('a', 'b'));
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $graph = new WeightedGraph;
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
