<?php

declare(strict_types=1);

namespace Carve\Boundary\ValueObjects;

final class RiskScore
{
    public function __construct(
        public readonly float $score,
        public readonly array $components = [],
    ) {}

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'components' => $this->components,
        ];
    }
}
