<?php

declare(strict_types=1);

namespace Carve\Runtime\ValueObjects;

final class TraceRecord
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $type,
        public readonly ?string $method = null,
        public readonly ?string $uri = null,
        public readonly ?string $routeName = null,
        public readonly ?string $controllerAction = null,
        public readonly ?string $jobClass = null,
        public readonly ?string $userId = null,
        public readonly ?int $statusCode = null,
        public readonly ?int $durationMs = null,
        public readonly ?string $exceptionClass = null,
        public readonly ?string $exceptionMessage = null,
        public readonly array $meta = [],
        public readonly array $events = [],
        public readonly ?string $requestId = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $endedAt = null,
    ) {}
}
