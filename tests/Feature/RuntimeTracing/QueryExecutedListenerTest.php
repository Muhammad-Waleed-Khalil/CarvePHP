<?php

declare(strict_types=1);

namespace Carve\Tests\Feature\RuntimeTracing;

use Carve\Runtime\Listeners\QueryExecutedListener;
use Carve\Runtime\TraceContextManager;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Orchestra\Testbench\TestCase;

final class QueryExecutedListenerTest extends TestCase
{
    private QueryExecutedListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        TraceContextManager::stop();
        $this->listener = new QueryExecutedListener;
    }

    protected function defineEnvironment($app): void
    {
        $app->make('config')->set('carve.runtime_tracing.capture_sql', true);
    }

    public function test_records_query_event_to_active_context(): void
    {
        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('select * from users where id = ?', [1], 5.0, $connection);

        $this->listener->handle($event);

        $this->assertCount(1, $context->events);
        $this->assertSame('db_query', $context->events[0]['event_type']);
        $this->assertSame('users', $context->events[0]['table_name']);
        $this->assertSame('select', $context->events[0]['operation']);
        $this->assertSame(5, $context->events[0]['duration_ms']);
    }

    public function test_does_nothing_without_active_context(): void
    {
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('select * from users', [], 5.0, $connection);

        $this->expectNotToPerformAssertions();
        $this->listener->handle($event);
    }

    public function test_does_nothing_when_capture_sql_disabled(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.capture_sql', false);

        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('select * from users', [], 5.0, $connection);

        $this->listener->handle($event);

        $this->assertCount(0, $context->events);
    }

    public function test_extracts_insert_operation(): void
    {
        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('insert into posts (title) values (?)', ['Hello'], 3.0, $connection);

        $this->listener->handle($event);

        $this->assertCount(1, $context->events);
        $this->assertSame('posts', $context->events[0]['table_name']);
        $this->assertSame('insert', $context->events[0]['operation']);
    }

    public function test_extracts_update_operation(): void
    {
        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('update users set name = ? where id = ?', ['New', 1], 2.0, $connection);

        $this->listener->handle($event);

        $this->assertSame('users', $context->events[0]['table_name']);
        $this->assertSame('update', $context->events[0]['operation']);
    }

    public function test_extracts_delete_operation(): void
    {
        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted('delete from sessions where expired = 1', [], 1.0, $connection);

        $this->listener->handle($event);

        $this->assertSame('sessions', $context->events[0]['table_name']);
        $this->assertSame('delete', $context->events[0]['operation']);
    }

    public function test_handles_join_queries(): void
    {
        $context = TraceContextManager::start();
        $connection = $this->createMock(Connection::class);
        $event = new QueryExecuted(
            'select u.* from users u join posts p on p.user_id = u.id',
            [],
            10.0,
            $connection,
        );

        $this->listener->handle($event);

        $this->assertSame('users', $context->events[0]['table_name']);
    }
}
