<?php

declare(strict_types=1);

namespace Carve\Runtime;

final class TraceContext
{
    public string $traceId;
    public ?string $requestId = null;
    public string $type = 'http';
    public ?string $method = null;
    public ?string $uri = null;
    public ?string $routeName = null;
    public ?string $controllerAction = null;
    public ?string $jobClass = null;
    public ?string $userId = null;
    public ?int $statusCode = null;
    public float $startedAt;
    public ?float $endedAt = null;
    public ?int $durationMs = null;
    public ?string $exceptionClass = null;
    public ?string $exceptionMessage = null;
    public array $meta = [];
    public array $events = [];
}
