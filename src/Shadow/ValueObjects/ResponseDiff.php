<?php

declare(strict_types=1);

namespace Carve\Shadow\ValueObjects;

final class ResponseDiff
{
    public function __construct(
        public readonly string $path,
        public readonly mixed $monolith,
        public readonly mixed $service,
        public readonly string $type,
    ) {}

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'monolith' => $this->monolith,
            'service' => $this->service,
            'type' => $this->type,
        ];
    }
}
