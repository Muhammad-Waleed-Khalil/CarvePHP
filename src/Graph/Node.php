<?php

declare(strict_types=1);

namespace Carve\Graph;

final class Node
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $name,
        public readonly string $label,
        public readonly array $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'meta' => $this->meta,
        ];
    }
}
