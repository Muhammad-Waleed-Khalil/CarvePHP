<?php

declare(strict_types=1);

namespace Carve\Tests\Feature\RuntimeTracing;

use Carve\Runtime\Stores\JsonlTraceStore;
use Carve\Runtime\ValueObjects\TraceRecord;
use PHPUnit\Framework\TestCase;

final class JsonlTraceStoreTest extends TestCase
{
    private string $path;

    private JsonlTraceStore $store;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().'/carve-test-'.uniqid().'.jsonl';
        $this->store = new JsonlTraceStore($this->path);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function test_save_appends_record_to_file(): void
    {
        $record = new TraceRecord(
            traceId: 'abc123',
            type: 'http',
            method: 'GET',
            uri: '/test',
            statusCode: 200,
            durationMs: 42,
        );

        $this->store->save($record);

        $contents = file_get_contents($this->path);
        $this->assertStringContainsString('abc123', $contents);
        $this->assertStringContainsString('GET', $contents);
        $this->assertStringContainsString('http', $contents);
    }

    public function test_save_appends_multiple_records(): void
    {
        $a = new TraceRecord(traceId: 'first', type: 'http');
        $b = new TraceRecord(traceId: 'second', type: 'job');

        $this->store->save($a);
        $this->store->save($b);

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('first', $lines[0]);
        $this->assertStringContainsString('second', $lines[1]);
    }

    public function test_find_between_returns_filtered_records(): void
    {
        $early = new TraceRecord(
            traceId: 'early',
            type: 'http',
            startedAt: '2024-01-01 00:00:00',
        );
        $middle = new TraceRecord(
            traceId: 'middle',
            type: 'http',
            startedAt: '2024-06-15 12:00:00',
        );
        $late = new TraceRecord(
            traceId: 'late',
            type: 'http',
            startedAt: '2024-12-31 23:59:59',
        );

        $this->store->save($early);
        $this->store->save($middle);
        $this->store->save($late);

        $results = $this->store->findBetween('2024-06-01 00:00:00', '2024-12-31 00:00:00');
        $this->assertCount(1, $results);
        $this->assertSame('middle', $results[0]['trace_id']);
    }

    public function test_find_between_returns_empty_when_no_file(): void
    {
        $emptyPath = sys_get_temp_dir().'/carve-test-nonexistent-'.uniqid().'.jsonl';
        $store = new JsonlTraceStore($emptyPath);

        $results = $store->findBetween('2024-01-01', '2024-12-31');
        $this->assertSame([], $results);
    }

    public function test_creates_directory_if_not_exists(): void
    {
        $dir = sys_get_temp_dir().'/carve-test-dir-'.uniqid();
        $path = $dir.'/traces.jsonl';
        $store = new JsonlTraceStore($path);

        $record = new TraceRecord(traceId: 'dir-test', type: 'http');
        $store->save($record);

        $this->assertFileExists($path);
        unlink($path);
        rmdir($dir);
    }
}
