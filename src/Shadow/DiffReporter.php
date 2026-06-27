<?php

declare(strict_types=1);

namespace Carve\Shadow;

final class DiffReporter
{
    public function generate(int $last = 1000, string $format = 'md'): string
    {
        if ($format === 'json') {
            return $this->generateJson();
        }

        return $this->generateMarkdown();
    }

    public function generateFromDiffs(array $diffs, string $format = 'md'): string
    {
        if ($format === 'json') {
            return json_encode($diffs, JSON_PRETTY_PRINT);
        }

        $lines = ['# Shadow Diff Report', '', '## Differences Found: '.count($diffs), ''];

        foreach ($diffs as $diff) {
            $lines[] = "### Path: {$diff->path}";
            $lines[] = "- Type: {$diff->type}";
            $lines[] = '- Monolith: '.json_encode($diff->monolith);
            $lines[] = '- Service: '.json_encode($diff->service);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function generateJson(): string
    {
        return json_encode([
            'generated_at' => date('c'),
            'diffs' => [],
        ], JSON_PRETTY_PRINT);
    }

    private function generateMarkdown(): string
    {
        return "# Shadow Diff Report\n\nNo diffs available.\n";
    }
}
