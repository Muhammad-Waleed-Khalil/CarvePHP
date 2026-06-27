<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class DbUsageAnalyzer
{
    public function __construct(
        private readonly RawSqlTableExtractor $sqlExtractor,
    ) {}

    public function analyze(array $files): array
    {
        $usages = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if (preg_match_all('/DB::(table|select|statement|insert|update|delete|raw)\(/', $content, $matches)) {
                $usages[] = [
                    'file' => $file,
                    'calls' => $matches[0],
                ];
            }

            $sqlTables = $this->sqlExtractor->extract($content);
            if (! empty($sqlTables)) {
                $usages[] = [
                    'file' => $file,
                    'raw_sql_tables' => $sqlTables,
                ];
            }
        }

        return $usages;
    }
}
