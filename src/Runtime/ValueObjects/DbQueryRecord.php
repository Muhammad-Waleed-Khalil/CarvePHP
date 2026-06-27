<?php

declare(strict_types=1);

namespace Carve\Runtime\ValueObjects;

final class DbQueryRecord
{
    public function __construct(
        public readonly string $sql,
        public readonly array $tables,
        public readonly string $operation,
        public readonly int $durationMs,
    ) {}
}
