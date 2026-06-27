<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class ModelInfo
{
    public function __construct(
        public readonly string $class,
        public readonly ?string $table,
        public readonly ?string $primaryKey,
        public readonly array $fillable,
        public readonly array $casts,
        public readonly array $relationships,
    ) {}

    public function toArray(): array
    {
        return [
            'class' => $this->class,
            'table' => $this->table,
            'primary_key' => $this->primaryKey,
            'fillable' => $this->fillable,
            'casts' => $this->casts,
            'relationships' => $this->relationships,
        ];
    }
}
