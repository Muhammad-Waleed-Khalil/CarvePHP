<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\StaticAnalysis\StaticScanner;
use Illuminate\Console\Command;

final class ScanCommand extends Command
{
    protected $signature = 'carve:scan
        {--format=json : Output format (json|md)}
        {--output=storage/app/carve/static-scan.json : Output file path}
        {--include-modules : Include module directories}
        {--pretty : Pretty-print JSON output}';

    protected $description = 'Run static analysis on the monolith';

    public function handle(): int
    {
        $this->info('Running static scan...');

        $outputPath = $this->option('output');
        $outputDir = dirname($outputPath);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $scanner = app(StaticScanner::class);
        $result = $scanner->scan(
            paths: config('carve.static_analysis.include_paths', ['app', 'routes', 'database']),
            exclude: config('carve.static_analysis.exclude_paths', ['vendor', 'storage', 'bootstrap/cache', 'node_modules']),
        );

        $json = json_encode($result, $this->option('pretty') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);

        file_put_contents($outputPath, $json);

        $this->info("Static scan written to {$outputPath}");

        return self::SUCCESS;
    }
}
