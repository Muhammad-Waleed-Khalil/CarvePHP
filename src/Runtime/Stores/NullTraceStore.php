<?php

declare(strict_types=1);

namespace Carve\Runtime\Stores;

use Carve\Runtime\TraceStoreInterface;
use Carve\Runtime\ValueObjects\TraceRecord;

final class NullTraceStore implements TraceStoreInterface
{
    public function save(TraceRecord $record): void {}

    public function findBetween(string $from, string $to): array
    {
        return [];
    }
}
