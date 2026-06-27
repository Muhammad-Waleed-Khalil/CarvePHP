<?php

declare(strict_types=1);

namespace Carve\Runtime\ValueObjects;

final class EventRecord
{
    public function __construct(
        public readonly string $eventClass,
        public readonly array $payload = [],
    ) {}
}
