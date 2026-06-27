<?php

declare(strict_types=1);

namespace Carve\Graph;

final class Edge
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $type,
        public readonly float $weight,
        public readonly array $evidence = [],
    ) {}

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'type' => $this->type,
            'weight' => $this->weight,
            'evidence' => $this->evidence,
        ];
    }
}
