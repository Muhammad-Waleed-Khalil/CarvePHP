<?php

declare(strict_types=1);

namespace Carve\Tests\Unit\Runtime;

use Carve\Runtime\TraceContextManager;
use PHPUnit\Framework\TestCase;

final class TraceContextManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        TraceContextManager::stop();
    }

    public function test_start_creates_context_with_trace_id(): void
    {
        $context = TraceContextManager::start();

        $this->assertNotNull($context->traceId);
        $this->assertSame(32, strlen($context->traceId));
    }

    public function test_start_sets_started_at(): void
    {
        $before = microtime(true);
        $context = TraceContextManager::start();
        $after = microtime(true);

        $this->assertGreaterThanOrEqual($before, $context->startedAt);
        $this->assertLessThanOrEqual($after, $context->startedAt);
    }

    public function test_current_returns_active_context(): void
    {
        $context = TraceContextManager::start();

        $this->assertSame($context, TraceContextManager::current());
    }

    public function test_current_returns_null_after_stop(): void
    {
        TraceContextManager::start();
        TraceContextManager::stop();

        $this->assertNull(TraceContextManager::current());
    }

    public function test_current_returns_null_when_not_started(): void
    {
        $this->assertNull(TraceContextManager::current());
    }

    public function test_stop_clears_context(): void
    {
        TraceContextManager::start();
        TraceContextManager::stop();

        $this->assertNull(TraceContextManager::current());
    }

    public function test_start_replaces_previous_context(): void
    {
        $first = TraceContextManager::start();
        $second = TraceContextManager::start();

        $this->assertNotSame($first, $second);
        $this->assertSame($second, TraceContextManager::current());
    }
}
