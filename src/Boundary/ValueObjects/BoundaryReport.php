<?php

declare(strict_types=1);

namespace Carve\Boundary\ValueObjects;

final class BoundaryReport
{
    public function __construct(
        public readonly string $generatedAt,
        public readonly string $algorithm,
        public readonly array $candidates,
        public readonly array $warnings = [],
    ) {}

    public function toArray(): array
    {
        return [
            'generated_at' => $this->generatedAt,
            'algorithm' => $this->algorithm,
            'candidates' => array_map(fn (BoundaryCandidate $c) => $c->toArray(), $this->candidates),
            'warnings' => $this->warnings,
        ];
    }
}
