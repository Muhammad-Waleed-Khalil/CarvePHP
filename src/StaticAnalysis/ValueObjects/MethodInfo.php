<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class MethodInfo
{
    public function __construct(
        public readonly string $name,
        public readonly array $parameters,
        public readonly ?string $returnType,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'parameters' => $this->parameters,
            'return_type' => $this->returnType,
        ];
    }
}
