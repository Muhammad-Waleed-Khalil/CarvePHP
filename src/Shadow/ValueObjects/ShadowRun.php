<?php

declare(strict_types=1);

namespace Carve\Shadow\ValueObjects;

final class ShadowRun
{
    public function __construct(
        public readonly string $route,
        public readonly int $monolithStatus,
        public readonly int $serviceStatus,
        public readonly bool $match,
        public readonly array $diffs = [],
    ) {}

    public function toArray(): array
    {
        return [
            'route' => $this->route,
            'monolith_status' => $this->monolithStatus,
            'service_status' => $this->serviceStatus,
            'match' => $this->match,
            'diffs' => array_map(fn (ResponseDiff $d) => $d->toArray(), $this->diffs),
        ];
    }
}
