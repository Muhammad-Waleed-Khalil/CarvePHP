<?php

declare(strict_types=1);

namespace Carve\Graph;

final class GraphExporter
{
    public function toJson(WeightedGraph $graph, bool $pretty = true): string
    {
        return json_encode(
            $graph->toArray(),
            $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0,
        );
    }

    public function exportToFile(WeightedGraph $graph, string $path, bool $pretty = true): void
    {
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $this->toJson($graph, $pretty));
    }
}
