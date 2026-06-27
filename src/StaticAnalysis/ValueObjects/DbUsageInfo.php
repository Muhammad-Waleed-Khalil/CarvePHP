<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class DbUsageInfo
{
    public function __construct(
        public readonly string $file,
        public readonly array $calls,
        public readonly array $rawSqlTables,
    ) {}

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'calls' => $this->calls,
            'raw_sql_tables' => $this->rawSqlTables,
        ];
    }
}
