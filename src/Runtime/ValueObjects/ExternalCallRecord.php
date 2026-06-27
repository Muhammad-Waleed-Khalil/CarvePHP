<?php

declare(strict_types=1);

namespace Carve\Runtime\ValueObjects;

final class ExternalCallRecord
{
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly ?int $statusCode = null,
        public readonly ?int $durationMs = null,
    ) {}
}
