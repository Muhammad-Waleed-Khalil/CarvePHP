<?php

declare(strict_types=1);

namespace Carve\Contracts\ValueObjects;

final class OpenApiSpec
{
    public function __construct(
        public readonly string $title,
        public readonly string $version,
        public readonly array $paths,
        public readonly array $schemas,
        public readonly array $security = [],
    ) {}

    public function toArray(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $this->title,
                'version' => $this->version,
            ],
            'paths' => $this->paths,
            'components' => [
                'schemas' => $this->schemas,
            ],
        ];
    }
}
