<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class TableInfo
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $modelClass,
        public readonly string $source,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'model_class' => $this->modelClass,
            'source' => $this->source,
        ];
    }
}
