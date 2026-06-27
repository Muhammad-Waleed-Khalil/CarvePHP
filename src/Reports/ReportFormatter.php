<?php

declare(strict_types=1);

namespace Carve\Reports;

final class ReportFormatter
{
    public function __construct(
        private readonly MarkdownReportWriter $markdownWriter,
        private readonly JsonReportWriter $jsonWriter,
    ) {}

    public function format(string $format, ?string $scanPath, ?string $graphPath, ?string $boundariesPath): string
    {
        return match ($format) {
            'json' => $this->jsonWriter->generate([
                'scan' => $scanPath ? $this->loadJson($scanPath) : null,
                'graph' => $graphPath ? $this->loadJson($graphPath) : null,
                'boundaries' => $boundariesPath ? $this->loadJson($boundariesPath) : null,
            ]),
            default => $this->markdownWriter->generate($scanPath, $graphPath, $boundariesPath),
        };
    }

    private function loadJson(string $path): ?array
    {
        if (! file_exists($path)) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }
}
