<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Boundary\BoundarySuggester;
use Illuminate\Console\Command;

final class BoundariesCommand extends Command
{
    protected $signature = 'carve:boundaries
        {--graph=storage/app/carve/graph.json : Graph JSON path}
        {--algorithm=table_affinity : Clustering algorithm}
        {--min-size=2 : Minimum cluster size}
        {--output=storage/app/carve/boundaries.json : Output JSON path}
        {--report=storage/app/carve/boundaries.md : Output Markdown report path}';

    protected $description = 'Suggest candidate microservice boundaries';

    public function handle(): int
    {
        $this->info('Detecting boundaries...');

        $suggester = app(BoundarySuggester::class);
        $candidates = $suggester->suggest(
            graphPath: $this->option('graph'),
            algorithm: $this->option('algorithm'),
            minClusterSize: (int) $this->option('min-size'),
        );

        $outputDir = dirname($this->option('output'));
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents(
            $this->option('output'),
            json_encode($candidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );

        $this->info("Boundaries written to {$this->option('output')}");

        return self::SUCCESS;
    }
}
