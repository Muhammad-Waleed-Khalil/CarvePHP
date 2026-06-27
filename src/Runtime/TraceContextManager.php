<?php

declare(strict_types=1);

namespace Carve\Runtime;

final class TraceContextManager
{
    private static ?TraceContext $currentContext = null;

    public static function start(): TraceContext
    {
        $context = new TraceContext;
        $context->traceId = bin2hex(random_bytes(16));
        $context->startedAt = microtime(true);
        self::$currentContext = $context;

        return $context;
    }

    public static function current(): ?TraceContext
    {
        return self::$currentContext;
    }

    public static function stop(): void
    {
        self::$currentContext = null;
    }
}
