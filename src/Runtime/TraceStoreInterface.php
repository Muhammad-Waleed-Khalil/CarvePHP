<?php

declare(strict_types=1);

namespace Carve\Runtime;

use Carve\Runtime\ValueObjects\TraceRecord;

interface TraceStoreInterface
{
    public function save(TraceRecord $record): void;

    public function findBetween(string $from, string $to): array;
}
