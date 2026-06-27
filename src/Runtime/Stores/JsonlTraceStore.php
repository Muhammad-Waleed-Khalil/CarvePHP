<?php

declare(strict_types=1);

namespace Carve\Runtime\Stores;

use Carve\Runtime\TraceStoreInterface;
use Carve\Runtime\ValueObjects\TraceRecord;

final class JsonlTraceStore implements TraceStoreInterface
{
    public function __construct(
        private readonly string $path,
    ) {}

    public function save(TraceRecord $record): void
    {
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $line = json_encode([
            'trace_id' => $record->traceId,
            'type' => $record->type,
            'method' => $record->method,
            'uri' => $record->uri,
            'route_name' => $record->routeName,
            'controller_action' => $record->controllerAction,
            'status_code' => $record->statusCode,
            'duration_ms' => $record->durationMs,
            'started_at' => $record->startedAt,
            'events' => $record->events,
        ]) . "\n";

        file_put_contents($this->path, $line, FILE_APPEND | LOCK_EX);
    }

    public function findBetween(string $from, string $to): array
    {
        if (! file_exists($this->path)) {
            return [];
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $results = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);

            if ($data && isset($data['started_at'])) {
                if ($data['started_at'] >= $from && $data['started_at'] <= $to) {
                    $results[] = $data;
                }
            }
        }

        return $results;
    }
}
