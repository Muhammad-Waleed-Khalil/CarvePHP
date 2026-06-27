<?php

declare(strict_types=1);

namespace Carve\Reports;

final class MarkdownReportWriter
{
    public function generate(?string $scanPath, ?string $graphPath, ?string $boundariesPath): string
    {
        $report = [];
        $report[] = '# CarvePHP Migration Report';
        $report[] = '';
        $report[] = 'Generated: ' . date('c');
        $report[] = '';
        $report[] = '## Executive Summary';
        $report[] = '';

        if ($scanPath && file_exists($scanPath)) {
            $scan = json_decode(file_get_contents($scanPath), true);
            $report[] = '- Routes: ' . count($scan['routes'] ?? []);
            $report[] = '- Controllers: ' . count($scan['classes'] ?? []);
            $report[] = '- Models: ' . count($scan['models'] ?? []);
            $report[] = '- Tables: ' . count($scan['tables'] ?? []);
        }

        $report[] = '';
        $report[] = '## Candidate Boundaries';
        $report[] = '';

        if ($boundariesPath && file_exists($boundariesPath)) {
            $boundaries = json_decode(file_get_contents($boundariesPath), true);

            foreach ($boundaries['candidates'] ?? [] as $candidate) {
                $report[] = "### {$candidate['name']}";
                $report[] = '';
                $report[] = "- Confidence: " . ($candidate['confidence'] * 100) . '%';
                $report[] = "- Cohesion: " . ($candidate['cohesion_score'] * 100) . '%';
                $report[] = "- Coupling: " . ($candidate['coupling_score'] * 100) . '%';
                $report[] = "- Risk: " . ($candidate['risk_score'] * 100) . '%';

                if (! empty($candidate['explanation'])) {
                    $report[] = '';
                    $report[] = '#### Why suggested';
                    foreach ($candidate['explanation'] as $explanation) {
                        $report[] = "- {$explanation}";
                    }
                }

                $report[] = '';
            }
        }

        $report[] = '## Limitations and Warnings';
        $report[] = '';
        $report[] = '- This report is based on automated analysis and requires human review.';
        $report[] = '- Boundary suggestions are statistical and may not reflect actual domain boundaries.';

        return implode("\n", $report);
    }
}
