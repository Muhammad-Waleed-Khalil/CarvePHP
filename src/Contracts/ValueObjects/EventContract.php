<?php

declare(strict_types=1);

namespace Carve\Contracts\ValueObjects;

final class EventContract
{
    public function __construct(
        public readonly string $eventClass,
        public readonly int $version,
        public readonly array $schema,
    ) {}

    public function toArray(): array
    {
        return [
            'event_class' => $this->eventClass,
            'version' => $this->version,
            'schema' => $this->schema,
        ];
    }
}
