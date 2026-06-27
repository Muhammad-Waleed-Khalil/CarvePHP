<?php

declare(strict_types=1);

namespace Carve\Runtime\Listeners;

use Carve\Runtime\TraceContextManager;
use Illuminate\Database\Events\QueryExecuted;

final class QueryExecutedListener
{
    public function handle(QueryExecuted $event): void
    {
        $context = TraceContextManager::current();

        if ($context === null) {
            return;
        }

        if (! config('carve.runtime_tracing.capture_sql', true)) {
            return;
        }

        $sql = $event->sql;
        $table = $this->extractTable($sql);
        $operation = $this->extractOperation($sql);

        $context->events[] = [
            'event_type' => 'db_query',
            'table_name' => $table,
            'operation' => $operation,
            'duration_ms' => (int) ($event->time),
        ];
    }

    private function extractTable(string $sql): ?string
    {
        if (preg_match('/\b(?:from|into|update|table)\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\bjoin\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractOperation(string $sql): string
    {
        $sql = trim($sql);
        if (str_starts_with(strtoupper($sql), 'SELECT')) {
            return 'select';
        }
        if (str_starts_with(strtoupper($sql), 'INSERT')) {
            return 'insert';
        }
        if (str_starts_with(strtoupper($sql), 'UPDATE')) {
            return 'update';
        }
        if (str_starts_with(strtoupper($sql), 'DELETE')) {
            return 'delete';
        }

        return 'unknown';
    }
}
