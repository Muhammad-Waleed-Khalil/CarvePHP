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
        {--from= : Start date for traces}
        {--to= : End date for traces}
        {--output=storage/app/carve/graph.json : Output graph JSON path}';

    protected $description = 'Combine static scan and runtime traces into a dependency graph';

    public function handle(): int
    {
        $this->info('Building dependency graph...');

        $outputPath = $this->option('output');
        $outputDir = dirname($outputPath);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $builder = app(GraphBuilder::class);
        $graph = $builder->build(
            staticScanPath: $this->option('static'),
            traceSource: $this->option('traces'),
        );

        file_put_contents($outputPath, json_encode($graph->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Graph written to {$outputPath}");

        return self::SUCCESS;
    }
}
