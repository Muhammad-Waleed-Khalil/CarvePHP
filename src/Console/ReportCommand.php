<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Reports\MarkdownReportWriter;
use Illuminate\Console\Command;

final class ReportCommand extends Command
{
    protected $signature = 'carve:report
        {--scan= : Static scan JSON path}
        {--graph= : Graph JSON path}
        {--boundaries= : Boundaries JSON path}
        {--output=carve-report.md : Output report path}';

    protected $description = 'Generate a full CarvePHP migration report';

    public function handle(): int
    {
        $this->info('Generating report...');

        $writer = app(MarkdownReportWriter::class);
        $report = $writer->generate(
            scanPath: $this->option('scan'),
            graphPath: $this->option('graph'),
            boundariesPath: $this->option('boundaries'),
        );

        file_put_contents($this->option('output'), $report);

        $this->info("Report written to {$this->option('output')}");

        return self::SUCCESS;
    }
}
