<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Shadow\DiffReporter;
use Illuminate\Console\Command;

final class DiffCommand extends Command
{
    protected $signature = 'carve:diff
        {--last=1000 : Number of recent diffs to include}
        {--format=md : Output format (md|json)}';

    protected $description = 'Compare monolith vs service responses from shadow results';

    public function handle(): int
    {
        $this->info('Generating diff report...');

        $reporter = app(DiffReporter::class);
        $report = $reporter->generate(
            last: (int) $this->option('last'),
            format: $this->option('format'),
        );

        $this->line($report);

        return self::SUCCESS;
    }
}
