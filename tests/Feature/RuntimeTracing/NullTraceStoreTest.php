<?php

declare(strict_types=1);

namespace Carve\Tests\Feature\RuntimeTracing;

use Carve\Runtime\Stores\NullTraceStore;
use Carve\Runtime\ValueObjects\TraceRecord;
use PHPUnit\Framework\TestCase;

final class NullTraceStoreTest extends TestCase
{
    private NullTraceStore $store;

    protected function setUp(): void
    {
        $this->store = new NullTraceStore;
    }

    public function test_save_does_nothing(): void
    {
        $record = new TraceRecord(traceId: 'test', type: 'http');

        $this->expectNotToPerformAssertions();
        $this->store->save($record);
    }

    public function test_find_between_returns_empty_array(): void
    {
        $results = $this->store->findBetween('2024-01-01', '2024-12-31');

        $this->assertSame([], $results);
    }
}
