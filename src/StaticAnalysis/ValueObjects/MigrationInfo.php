<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class MigrationInfo
{
    public function __construct(
        public readonly string $file,
        public readonly array $createdTables,
        public readonly array $modifiedTables,
        public readonly array $droppedTables,
    ) {}

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'created_tables' => $this->createdTables,
            'modified_tables' => $this->modifiedTables,
            'dropped_tables' => $this->droppedTables,
        ];
    }
}
