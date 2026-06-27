<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Boundary\Algorithms\TableAffinityClusterer;
use Carve\Boundary\BoundaryNameGuesser;
use Carve\Boundary\CouplingScorer;
use Carve\Boundary\RiskScorer;
use Carve\Graph\Edge;
use Carve\Graph\Node;
use Carve\Graph\NodeType;
use Carve\Graph\WeightedGraph;
use PHPUnit\Framework\TestCase;

final class TableAffinityClustererTest extends TestCase
{
    private TableAffinityClusterer $clusterer;

    protected function setUp(): void
    {
        $this->clusterer = new TableAffinityClusterer;
    }

    public function test_returns_empty_for_empty_graph(): void
    {
        $graph = new WeightedGraph;
        $clusters = $this->clusterer->cluster($graph);

        $this->assertEmpty($clusters);
    }

    public function test_returns_empty_for_no_table_nodes(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('route:GET:/api/test', NodeType::ROUTE, 'test', 'Test'));
        $clusters = $this->clusterer->cluster($graph);

        $this->assertEmpty($clusters);
    }

    public function test_clusters_connected_tables(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addNode(new Node('table:payments', NodeType::TABLE, 'payments', 'payments'));
        $graph->addNode(new Node('table:users', NodeType::TABLE, 'users', 'users'));

        $graph->addEdge(new Edge('table:invoices', 'table:payments', 'co_occurs', 10.0));
        $graph->addEdge(new Edge('table:invoices', 'table:users', 'co_occurs', 2.0));

        $clusters = $this->clusterer->cluster($graph, 1);

        $this->assertNotEmpty($clusters);
    }

    public function test_groups_tables_by_affinity(): void
    {
        $graph = new WeightedGraph;

        // Billing cluster
        $graph->addNode(new Node('table:invoices', NodeType::TABLE, 'invoices', 'invoices'));
        $graph->addNode(new Node('table:payments', NodeType::TABLE, 'payments', 'payments'));

        // Support cluster
        $graph->addNode(new Node('table:tickets', NodeType::TABLE, 'tickets', 'tickets'));
        $graph->addNode(new Node('table:ticket_replies', NodeType::TABLE, 'ticket_replies', 'ticket_replies'));

        // Strong billing edges
        $graph->addEdge(new Edge('table:invoices', 'table:payments', 'co_occurs', 10.0));
        $graph->addEdge(new Edge('table:invoices', 'table:payments', 'touches_table', 5.0));

        // Strong support edges
        $graph->addEdge(new Edge('table:tickets', 'table:ticket_replies', 'co_occurs', 8.0));
        $graph->addEdge(new Edge('table:tickets', 'table:ticket_replies', 'touches_table', 5.0));

        // Weak cross-cluster edge
        $graph->addEdge(new Edge('table:invoices', 'table:tickets', 'co_occurs', 0.5));

        $clusters = $this->clusterer->cluster($graph, 2);

        $this->assertCount(2, $clusters, 'Should produce 2 clusters');

        $tableSets = array_map(fn ($c) => $c['tables'], $clusters);

        // One cluster should have invoices+payments
        $hasBilling = false;
        $hasSupport = false;
        foreach ($tableSets as $tables) {
            sort($tables);
            if ($tables === ['invoices', 'payments']) {
                $hasBilling = true;
            }
            if ($tables === ['ticket_replies', 'tickets']) {
                $hasSupport = true;
            }
        }
        $this->assertTrue($hasBilling, 'Billing cluster should contain invoices and payments');
        $this->assertTrue($hasSupport, 'Support cluster should contain tickets and ticket_replies');
    }

    public function test_min_cluster_size_filters_small_clusters(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('table:alone', NodeType::TABLE, 'alone', 'alone'));

        $clusters = $this->clusterer->cluster($graph, 2);

        $this->assertEmpty($clusters, 'Single table should be filtered by min size');
    }

    public function test_cluster_has_explanation(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('table:a', NodeType::TABLE, 'a', 'a'));
        $graph->addNode(new Node('table:b', NodeType::TABLE, 'b', 'b'));
        $graph->addEdge(new Edge('table:a', 'table:b', 'co_occurs', 5.0));

        $clusters = $this->clusterer->cluster($graph, 1);

        $this->assertNotEmpty($clusters);
        $cluster = reset($clusters);
        $this->assertNotEmpty($cluster['explanation']);
    }

    public function test_boundary_name_guesser(): void
    {
        $guesser = new BoundaryNameGuesser;

        // Single table returns concatenation (no common prefix to extract)
        $name = $guesser->guess(['tables' => ['users']]);
        $this->assertEquals('Boundary_users', $name);

        // Common prefix 'ticket' from tickets + ticket_replies
        $name = $guesser->guess(['tables' => ['tickets', 'ticket_replies']]);
        $this->assertEquals('Ticket', $name);

        // Common prefix 'invoice' from invoice_items + invoice_payments
        $name = $guesser->guess(['tables' => ['invoice_items', 'invoice_payments']]);
        $this->assertEquals('Invoice', $name);

        // Empty tables returns fallback
        $name = $guesser->guess(['tables' => []]);
        $this->assertEquals('Unknown', $name);

        // Disparate tables with no common prefix fall back to concatenation
        $name = $guesser->guess(['tables' => ['invoices', 'payments']]);
        $this->assertEquals('Boundary_invoices_payments', $name);
    }

    public function test_coupling_scorer(): void
    {
        $graph = new WeightedGraph;
        $graph->addNode(new Node('table:a', NodeType::TABLE, 'a', 'a'));
        $graph->addNode(new Node('table:b', NodeType::TABLE, 'b', 'b'));
        $graph->addNode(new Node('table:c', NodeType::TABLE, 'c', 'c'));
        $graph->addEdge(new Edge('table:a', 'table:b', 'co_occurs', 10.0));
        $graph->addEdge(new Edge('table:a', 'table:c', 'co_occurs', 1.0));

        $scorer = new CouplingScorer;
        $score = $scorer->score($graph, ['tables' => ['a', 'b']]);

        $this->assertGreaterThan(0.0, $score->score);
        $this->assertGreaterThan(0.0, $score->cohesion);
    }

    public function test_risk_scorer(): void
    {
        $scorer = new RiskScorer;
        $score = $scorer->scoreFromData(0.5, ['tables' => ['a', 'b']]);

        $this->assertGreaterThan(0.0, $score->score);
        $this->assertLessThanOrEqual(1.0, $score->score);
        $this->assertArrayHasKey('coupling_score', $score->components);
    }
}
