<?php

declare(strict_types=1);

namespace Carve\Runtime\Stores;

use Carve\Runtime\TraceStoreInterface;
use Carve\Runtime\ValueObjects\TraceRecord;
use Illuminate\Support\Facades\DB;

final class DatabaseTraceStore implements TraceStoreInterface
{
    public function save(TraceRecord $record): void
    {
        DB::table('carve_traces')->insert([
            'trace_id' => $record->traceId,
            'request_id' => $record->requestId,
            'type' => $record->type,
            'method' => $record->method,
            'uri' => $record->uri,
            'route_name' => $record->routeName,
            'controller_action' => $record->controllerAction,
            'job_class' => $record->jobClass,
            'user_id' => $record->userId,
            'status_code' => $record->statusCode,
            'started_at' => $record->startedAt,
            'ended_at' => $record->endedAt,
            'duration_ms' => $record->durationMs,
            'exception_class' => $record->exceptionClass,
            'exception_message' => $record->exceptionMessage,
            'meta' => json_encode($record->meta),
        ]);

        foreach ($record->events as $event) {
            DB::table('carve_trace_events')->insert([
                'trace_id' => $record->traceId,
                'event_type' => $event['event_type'] ?? 'unknown',
                'name' => $event['name'] ?? null,
                'table_name' => $event['table_name'] ?? null,
                'operation' => $event['operation'] ?? null,
                'class_name' => $event['class_name'] ?? null,
                'method' => $event['method'] ?? null,
                'duration_ms' => $event['duration_ms'] ?? null,
                'payload' => isset($event['payload']) ? json_encode($event['payload']) : null,
            ]);
        }
    }

    public function findBetween(string $from, string $to): array
    {
        return DB::table('carve_traces')
            ->whereBetween('started_at', [$from, $to])
            ->get()
            ->all();
    }
}
