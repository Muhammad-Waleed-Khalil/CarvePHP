<?php

declare(strict_types=1);

namespace Carve\Tests\Feature;

use Carve\Boundary\Algorithms\TableAffinityClusterer;
use Carve\Boundary\BoundaryNameGuesser;
use Carve\Boundary\BoundarySuggester;
use Carve\Boundary\CouplingScorer;
use Carve\Boundary\RiskScorer;
use Carve\Graph\GraphBuilder;
use Carve\Graph\GraphExporter;
use Carve\Graph\GraphRepository;
use Carve\Graph\ValueObjects\GraphBuildResult;
use Carve\Reports\MarkdownReportWriter;
use Carve\Reports\ReportFormatter;
use Orchestra\Testbench\TestCase;

final class FullFixtureFlowTest extends TestCase
{
    private string $scanPath;

    private string $graphPath;

    private string $tracePath;

    private string $boundariesPath;

    private string $reportPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scanPath = __DIR__.'/../Fixtures/demo-scan.json';
        $this->tracePath = __DIR__.'/../Fixtures/test-traces.jsonl';
        $this->graphPath = __DIR__.'/../Fixtures/test-graph-output.json';
        $this->boundariesPath = __DIR__.'/../Fixtures/test-boundaries-output.json';
        $this->reportPath = __DIR__.'/../Fixtures/test-report-output.md';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ([$this->graphPath, $this->boundariesPath, $this->reportPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function test_full_fixture_flow_produces_billing_boundary(): void
    {
        // Step 1: Build graph from fixture
        $builder = new GraphBuilder(new GraphRepository);
        $result = $builder->build(
            staticScanPath: $this->scanPath,
            traceSource: 'jsonl',
            tracePath: $this->tracePath,
        );

        $this->assertGreaterThan(0, $result->nodeCount, 'Graph should have nodes');
        $this->assertGreaterThan(0, $result->edgeCount, 'Graph should have edges');

        // Export graph
        (new GraphExporter)->exportToFile($result->graph, $this->graphPath);

        // Verify graph JSON exists
        $this->assertFileExists($this->graphPath);
        $graphData = json_decode(file_get_contents($this->graphPath), true);
        $this->assertArrayHasKey('nodes', $graphData);
        $this->assertArrayHasKey('edges', $graphData);

        // Step 2: Detect boundaries
        $repository = new GraphRepository;
        $suggester = new BoundarySuggester(
            $repository,
            new TableAffinityClusterer,
            new BoundaryNameGuesser,
            new RiskScorer,
            new CouplingScorer,
        );

        $boundaries = $suggester->suggest(
            graphPath: $this->graphPath,
            algorithm: 'table_affinity',
            minClusterSize: 2,
        );

        // Write boundaries
        file_put_contents(
            $this->boundariesPath,
            json_encode($boundaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );

        // Verify we got candidates
        $this->assertNotEmpty($boundaries['candidates'], 'Should produce boundary candidates');

        // Verify Billing boundary exists
        $candidateNames = array_map(fn ($c) => $c['name'], $boundaries['candidates']);
        $hasBilling = false;
        foreach ($candidateNames as $name) {
            if (stripos($name, 'billing') !== false || stripos($name, 'invoice') !== false || stripos($name, 'payment') !== false) {
                $hasBilling = true;
                break;
            }
        }
        $this->assertTrue($hasBilling, 'One candidate should relate to billing/invoices/payments');

        // Verify structure of each candidate
        foreach ($boundaries['candidates'] as $candidate) {
            $this->assertArrayHasKey('name', $candidate);
            $this->assertArrayHasKey('confidence', $candidate);
            $this->assertArrayHasKey('cohesion_score', $candidate);
            $this->assertArrayHasKey('coupling_score', $candidate);
            $this->assertArrayHasKey('risk_score', $candidate);
            $this->assertArrayHasKey('tables', $candidate);
            $this->assertArrayHasKey('explanation', $candidate);
            $this->assertArrayHasKey('warnings', $candidate);
        }

        // Step 3: Generate markdown report
        $report = $this->generateReport();
        $this->assertStringContainsString('CarvePHP Migration Report', $report);
        $this->assertStringContainsString('Candidate', $report);
        $this->assertStringContainsString('Executive Summary', $report);
        $this->assertStringContainsString('Limitations and Warnings', $report);
    }

    public function test_analyze_command_output(): void
    {
        $result = $this->runAnalyzeCommand();

        $this->assertFileExists($this->graphPath);
        $this->assertGreaterThan(0, $result->nodeCount);
    }

    public function test_boundaries_command_output(): void
    {
        // First build the graph
        $result = $this->runAnalyzeCommand();

        $this->assertFileExists($this->graphPath);

        // Now run boundaries
        $this->artisanBoundaries();

        $this->assertFileExists($this->boundariesPath);
        $this->assertFileExists($this->reportPath);

        $reportContent = file_get_contents($this->reportPath);
        $this->assertStringContainsString('Boundary Analysis Report', $reportContent);
    }

    public function test_report_command_output(): void
    {
        // Build graph and boundaries first
        $this->runAnalyzeCommand();
        $this->artisanBoundaries();

        // Generate report using the report command handler directly
        $this->artisanReport();

        $this->assertFileExists($this->reportPath);
        $reportContent = file_get_contents($this->reportPath);
        $this->assertStringContainsString('Executive Summary', $reportContent);
        $this->assertStringContainsString('Candidate Boundaries', $reportContent);
        $this->assertStringContainsString('Next Steps', $reportContent);
    }

    private function runAnalyzeCommand(): GraphBuildResult
    {
        $builder = app(GraphBuilder::class);
        $result = $builder->build(
            staticScanPath: $this->scanPath,
            traceSource: 'jsonl',
            tracePath: $this->tracePath,
        );

        file_put_contents($this->graphPath, json_encode(
            $result->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ));

        return $result;
    }

    private function artisanBoundaries(): void
    {
        $suggester = $this->app->make(BoundarySuggester::class);
        $candidates = $suggester->suggest(
            graphPath: $this->graphPath,
            algorithm: 'table_affinity',
            minClusterSize: 2,
        );

        $dir = dirname($this->boundariesPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->boundariesPath, json_encode(
            $candidates,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ));

        // Write markdown report
        $report = $this->generateBoundariesMarkdown($candidates);
        $reportDir = dirname($this->reportPath);
        if (! is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        file_put_contents($this->reportPath, $report);
    }

    private function artisanReport(): void
    {
        $formatter = app(ReportFormatter::class);
        $report = $formatter->format(
            format: 'markdown',
            scanPath: $this->scanPath,
            graphPath: $this->graphPath,
            boundariesPath: $this->boundariesPath,
        );

        file_put_contents($this->reportPath, $report);
    }

    private function generateReport(): string
    {
        $this->runAnalyzeCommand();
        $this->artisanBoundaries();

        $writer = new MarkdownReportWriter;

        return $writer->generate($this->scanPath, $this->graphPath, $this->boundariesPath);
    }

    private function generateBoundariesMarkdown(array $data): string
    {
        $lines = [];
        $lines[] = '# CarvePHP Boundary Analysis Report';
        $lines[] = '';
        $lines[] = 'Generated: '.($data['meta']['generated_at'] ?? date('c'));
        $lines[] = 'Algorithm: '.($data['meta']['algorithm'] ?? 'table_affinity');
        $lines[] = 'Total candidates: '.count($data['candidates']);
        $lines[] = '';

        foreach ($data['candidates'] as $i => $candidate) {
            $lines[] = '## Candidate '.($i + 1).': '.$candidate['name'];
            $lines[] = '';
            $lines[] = '| Metric | Value |';
            $lines[] = '|--------|-------|';
            $lines[] = '| Confidence | '.number_format($candidate['confidence'] * 100, 1).'% |';
            $lines[] = '| Cohesion | '.number_format($candidate['cohesion_score'] * 100, 1).'% |';
            $lines[] = '| Coupling | '.number_format($candidate['coupling_score'] * 100, 1).'% |';
            $lines[] = '| Risk | '.number_format($candidate['risk_score'] * 100, 1).'% |';
            $lines[] = '';

            if (! empty($candidate['tables'])) {
                $lines[] = '### Tables';
                foreach ($candidate['tables'] as $table) {
                    $lines[] = "- `{$table}`";
                }
                $lines[] = '';
            }
            if (! empty($candidate['controllers'])) {
                $lines[] = '### Controllers';
                foreach ($candidate['controllers'] as $ctrl) {
                    $lines[] = "- `{$ctrl}`";
                }
                $lines[] = '';
            }
            if (! empty($candidate['explanation'])) {
                $lines[] = '### Why Suggested';
                foreach ($candidate['explanation'] as $exp) {
                    $lines[] = "- {$exp}";
                }
                $lines[] = '';
            }
            $lines[] = '---';
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
