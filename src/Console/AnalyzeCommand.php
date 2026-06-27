<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Graph\GraphBuilder;
use Illuminate\Console\Command;

final class AnalyzeCommand extends Command
{
    protected $signature = 'carve:analyze
        {--static=storage/app/carve/static-scan.json : Static scan JSON path}
        {--traces=database : Trace source (database|jsonl)}
        {--trace-path=storage/app/carve/traces.jsonl : Trace JSONL path (when traces=jsonl)}
        {--from= : Start date for traces (Y-m-d H:i:s)}
        {--to= : End date for traces (Y-m-d H:i:s)}
        {--output=storage/app/carve/graph.json : Output graph JSON path}';

    protected $description = 'Combine static scan and runtime traces into a dependency graph';

    public function handle(): int
    {
        $this->info('Building dependency graph...');

        $outputPath = $this->option('output');
        $outputDir = dirname((string) $outputPath);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $builder = app(GraphBuilder::class);
        $graph = $builder->build(
            staticScanPath: (string) $this->option('static'),
            traceSource: (string) $this->option('traces'),
            tracePath: $this->option('trace-path') ? (string) $this->option('trace-path') : null,
            from: $this->option('from') ? (string) $this->option('from') : null,
            to: $this->option('to') ? (string) $this->option('to') : null,
        );

        file_put_contents((string) $outputPath, json_encode(
            $graph->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ));

        $this->info("Graph written to {$outputPath}");
        $this->line("Nodes: {$graph->nodeCount}, Edges: {$graph->edgeCount}");

        if ($graph->nodeCount === 0) {
            $this->warn('No nodes were created. Check that the static scan file exists and contains data.');
        }

        return 0;
    }
}
