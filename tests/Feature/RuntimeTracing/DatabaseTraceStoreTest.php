<?php

declare(strict_types=1);

namespace Carve\Tests\Feature\RuntimeTracing;

use Carve\Runtime\Stores\DatabaseTraceStore;
use Carve\Runtime\ValueObjects\TraceRecord;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

final class DatabaseTraceStoreTest extends TestCase
{
    private DatabaseTraceStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__.'/../../../database/migrations'));

        $this->store = new DatabaseTraceStore;
    }

    protected function defineEnvironment($app): void
    {
        $app->make('config')->set('database.default', 'testing');
        $app->make('config')->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function test_save_inserts_trace_record(): void
    {
        $record = new TraceRecord(
            traceId: 'abc123',
            type: 'http',
            method: 'GET',
            uri: '/test',
            routeName: 'test.route',
            statusCode: 200,
            durationMs: 42,
            startedAt: '2024-01-01 00:00:00',
            endedAt: '2024-01-01 00:00:01',
            meta: ['foo' => 'bar'],
        );

        $this->store->save($record);

        $row = DB::table('carve_traces')->first();
        $this->assertNotNull($row);
        $this->assertSame('abc123', $row->trace_id);
        $this->assertSame('GET', $row->method);
        $this->assertSame(200, $row->status_code);
        $this->assertSame(42, $row->duration_ms);
        $this->assertSame('2024-01-01 00:00:00', $row->started_at);
    }

    public function test_save_with_events(): void
    {
        $record = new TraceRecord(
            traceId: 'def456',
            type: 'http',
            events: [
                ['event_type' => 'query', 'name' => 'users.insert', 'duration_ms' => 5],
            ],
        );

        $this->store->save($record);

        $eventRow = DB::table('carve_trace_events')->first();
        $this->assertNotNull($eventRow);
        $this->assertSame('query', $eventRow->event_type);
        $this->assertSame('users.insert', $eventRow->name);
    }

    public function test_find_between_returns_records(): void
    {
        $record = new TraceRecord(
            traceId: 'ghi789',
            type: 'http',
            startedAt: '2024-06-01 12:00:00',
            endedAt: '2024-06-01 12:00:01',
        );

        $this->store->save($record);

        $results = $this->store->findBetween('2024-01-01 00:00:00', '2024-12-31 23:59:59');
        $this->assertCount(1, $results);

        $results = $this->store->findBetween('2025-01-01 00:00:00', '2025-12-31 23:59:59');
        $this->assertCount(0, $results);
    }

    public function test_nullable_fields_are_nullable(): void
    {
        $record = new TraceRecord(
            traceId: 'nullable-test',
            type: 'http',
        );

        $this->store->save($record);

        $row = DB::table('carve_traces')->first();
        $this->assertNull($row->started_at);
        $this->assertNull($row->ended_at);
        $this->assertNull($row->method);
        $this->assertNull($row->uri);
    }
}
