<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class ClassInfo
{
    public function __construct(
        public readonly string $name,
        public readonly string $namespace,
        public readonly string $file,
        public readonly array $methods,
        public readonly array $dependencies,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'namespace' => $this->namespace,
            'file' => $this->file,
            'methods' => array_map(fn (MethodInfo $m) => $m->toArray(), $this->methods),
            'dependencies' => $this->dependencies,
        ];
    }
}
