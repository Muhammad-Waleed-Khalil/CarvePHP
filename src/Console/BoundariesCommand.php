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
        {--report=carve-boundaries.md : Output Markdown report path}';

    protected $description = 'Suggest candidate service boundaries from the dependency graph';

    public function handle(): int
    {
        $this->info('Analyzing boundaries...');

        $suggester = app(BoundarySuggester::class);
        $candidates = $suggester->suggest(
            graphPath: (string) $this->option('graph'),
            algorithm: (string) $this->option('algorithm'),
            minClusterSize: (int) $this->option('min-size'),
        );

        $outputDir = dirname((string) $this->option('output'));
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents(
            (string) $this->option('output'),
            json_encode($candidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );

        $this->info('Candidates: '.count($candidates['candidates']));
        $this->line('JSON written to '.$this->option('output'));

        // Write Markdown report if requested
        $reportPath = $this->option('report');
        if ($reportPath) {
            $reportDir = dirname((string) $reportPath);
            if (! is_dir($reportDir)) {
                mkdir($reportDir, 0755, true);
            }

            $report = $this->generateMarkdownReport($candidates);
            file_put_contents((string) $reportPath, $report);
            $this->line('Report written to '.$reportPath);
        }

        return 0;
    }

    private function generateMarkdownReport(array $data): string
    {
        $lines = [];
        $lines[] = '# CarvePHP Boundary Analysis Report';
        $lines[] = '';
        $lines[] = 'Generated: '.($data['meta']['generated_at'] ?? date('c'));
        $lines[] = '';
        $lines[] = 'Algorithm: '.($data['meta']['algorithm'] ?? 'table_affinity');
        $lines[] = '';
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
                $lines[] = '';
                foreach ($candidate['tables'] as $table) {
                    $lines[] = "- `{$table}`";
                }
                $lines[] = '';
            }

            if (! empty($candidate['controllers'])) {
                $lines[] = '### Controllers';
                $lines[] = '';
                foreach ($candidate['controllers'] as $ctrl) {
                    $lines[] = "- `{$ctrl}`";
                }
                $lines[] = '';
            }

            if (! empty($candidate['routes'])) {
                $lines[] = '### Routes';
                $lines[] = '';
                foreach ($candidate['routes'] as $route) {
                    $lines[] = "- `{$route}`";
                }
                $lines[] = '';
            }

            if (! empty($candidate['models'])) {
                $lines[] = '### Models';
                $lines[] = '';
                foreach ($candidate['models'] as $model) {
                    $lines[] = "- `{$model}`";
                }
                $lines[] = '';
            }

            if (! empty($candidate['explanation'])) {
                $lines[] = '### Why Suggested';
                $lines[] = '';
                foreach ($candidate['explanation'] as $exp) {
                    $lines[] = "- {$exp}";
                }
                $lines[] = '';
            }

            if (! empty($candidate['warnings'])) {
                $lines[] = '### Warnings';
                $lines[] = '';
                foreach ($candidate['warnings'] as $warn) {
                    $lines[] = "- ⚠ {$warn}";
                }
                $lines[] = '';
            }

            $lines[] = '---';
            $lines[] = '';
        }

        if (! empty($data['warnings'])) {
            $lines[] = '## Global Warnings';
            $lines[] = '';
            foreach ($data['warnings'] as $warn) {
                $lines[] = "- ⚠ {$warn}";
            }
        }

        return implode("\n", $lines);
    }
}
