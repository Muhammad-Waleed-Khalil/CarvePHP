<?php

declare(strict_types=1);

namespace Carve\Runtime;

use Carve\Runtime\ValueObjects\TraceRecord;

final class TraceRecorder
{
    public function __construct(
        private readonly TraceStoreInterface $store,
    ) {}

    public function record(TraceRecord $record): void
    {
        $this->store->save($record);
    }

    public function flush(): void
    {
        if (TraceContextManager::current() !== null) {
            $context = TraceContextManager::current();
            $context->endedAt = microtime(true);
            $context->durationMs = (int) (($context->endedAt - $context->startedAt) * 1000);

            $record = new TraceRecord(
                traceId: $context->traceId,
                type: $context->type,
                method: $context->method,
                uri: $context->uri,
                routeName: $context->routeName,
                controllerAction: $context->controllerAction,
                jobClass: $context->jobClass,
                userId: $context->userId,
                statusCode: $context->statusCode,
                durationMs: $context->durationMs,
                exceptionClass: $context->exceptionClass,
                exceptionMessage: $context->exceptionMessage,
                meta: $context->meta,
                events: $context->events,
            );

            $this->record($record);
            TraceContextManager::stop();
        }
    }
}
