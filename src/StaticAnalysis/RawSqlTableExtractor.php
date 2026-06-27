<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class RawSqlTableExtractor
{
    public function extract(string $content): array
    {
        $tables = [];

        $patterns = [
            '/from\s+`?(\w+)`?/i',
            '/join\s+`?(\w+)`?/i',
            '/update\s+`?(\w+)`?/i',
            '/insert\s+(?:into\s+)?`?(\w+)`?/i',
            '/delete\s+(?:from\s+)?`?(\w+)`?/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $table) {
                    $table = trim($table);
                    if (! in_array($table, $tables)) {
                        $tables[] = $table;
                    }
                }
            }
        }

        return $tables;
    }
}
