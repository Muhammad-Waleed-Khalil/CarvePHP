<?php

declare(strict_types=1);

namespace Carve\Boundary\ValueObjects;

final class CouplingScore
{
    public function __construct(
        public readonly float $score,
        public readonly float $cohesion,
        public readonly float $internalWeight,
        public readonly float $externalWeight,
    ) {}

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'cohesion' => $this->cohesion,
            'internal_weight' => $this->internalWeight,
            'external_weight' => $this->externalWeight,
        ];
    }
}
