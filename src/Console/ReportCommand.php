<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Reports\ReportFormatter;
use Illuminate\Console\Command;

final class ReportCommand extends Command
{
    protected $signature = 'carve:report
        {--scan=storage/app/carve/static-scan.json : Static scan JSON path}
        {--graph=storage/app/carve/graph.json : Graph JSON path}
        {--boundaries=storage/app/carve/boundaries.json : Boundaries JSON path}
        {--format=markdown : Output format (markdown|json)}
        {--output=carve-report.md : Output report path}';

    protected $description = 'Generate a comprehensive migration assessment report';

    public function handle(): int
    {
        $this->info('Generating report...');

        $formatter = app(ReportFormatter::class);
        $report = $formatter->format(
            format: (string) $this->option('format'),
            scanPath: (string) $this->option('scan'),
            graphPath: (string) $this->option('graph'),
            boundariesPath: (string) $this->option('boundaries'),
        );

        $outputPath = (string) $this->option('output');
        $outputDir = dirname($outputPath);
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($outputPath, $report);

        $this->info("Report written to {$outputPath}");

        return self::SUCCESS;
    }
}
