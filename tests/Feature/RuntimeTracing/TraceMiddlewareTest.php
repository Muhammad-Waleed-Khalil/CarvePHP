<?php

declare(strict_types=1);

namespace Carve\Tests\Feature\RuntimeTracing;

use Carve\CarveServiceProvider;
use Carve\Runtime\Middleware\CarveTraceMiddleware;
use Carve\Runtime\TraceContextManager;
use Carve\Runtime\TraceStoreInterface;
use Carve\Runtime\ValueObjects\TraceRecord;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

final class TraceMiddlewareTest extends TestCase
{
    private NullStoreSpy $store;

    protected function getPackageProviders($app): array
    {
        return [CarveServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        TraceContextManager::stop();
        $this->store = new NullStoreSpy;
        $this->app->instance(TraceStoreInterface::class, $this->store);
    }

    protected function defineEnvironment($app): void
    {
        $app->make('config')->set('carve.runtime_tracing.store', 'null');
    }

    public function test_does_nothing_when_tracing_disabled(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', false);

        Route::get('/test-disabled', fn () => ['ok' => true])
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-disabled')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNull(TraceContextManager::current());
        $this->assertCount(0, $this->store->records);
    }

    public function test_records_http_trace_when_enabled(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);

        Route::get('/test-traced', fn () => ['ok' => true])
            ->name('test.traced')
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-traced')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertCount(1, $this->store->records);
        $record = $this->store->records[0];
        $this->assertSame('http', $record->type);
        $this->assertSame('GET', $record->method);
        $this->assertNotNull($record->startedAt);
        $this->assertNotNull($record->endedAt);
        $this->assertNotNull($record->durationMs);
        $this->assertSame(200, $record->statusCode);
    }

    public function test_records_500_status_on_exception(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);

        Route::get('/test-exception', fn () => throw new \RuntimeException('Something broke'))
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-exception')->assertStatus(500);

        $this->assertCount(1, $this->store->records);
        $record = $this->store->records[0];
        $this->assertSame(500, $record->statusCode);
    }

    public function test_sample_rate_zero_skips_tracing(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);
        $this->app->make('config')->set('carve.runtime_tracing.sample_rate', 0.0);

        Route::get('/test-unsampled', fn () => ['ok' => true])
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-unsampled')->assertOk();

        $this->assertCount(0, $this->store->records);
    }

    public function test_ignored_routes_are_skipped(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);
        $this->app->make('config')->set('carve.runtime_tracing.ignored_routes', ['ignored.*']);

        Route::get('/test-ignored', fn () => ['ok' => true])
            ->name('ignored.route')
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-ignored')->assertOk();

        $this->assertCount(0, $this->store->records);
    }

    public function test_ignores_routes_without_names(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);
        $this->app->make('config')->set('carve.runtime_tracing.ignored_routes', ['ignored.*']);

        Route::get('/test-no-name', fn () => ['ok' => true])
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-no-name')->assertOk();

        $this->assertCount(1, $this->store->records);
    }

    public function test_tracing_failure_does_not_break_request(): void
    {
        $this->app->make('config')->set('carve.runtime_tracing.enabled', true);

        $failingStore = new class implements TraceStoreInterface
        {
            public function save(TraceRecord $record): void
            {
                throw new \RuntimeException('Store failure');
            }

            public function findBetween(string $from, string $to): array
            {
                return [];
            }
        };

        $this->app->instance(TraceStoreInterface::class, $failingStore);

        Route::get('/test-store-fail', fn () => ['ok' => true])
            ->middleware(CarveTraceMiddleware::class);

        $this->get('/test-store-fail')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }
}

final class NullStoreSpy implements TraceStoreInterface
{
    public array $records = [];

    public function save(TraceRecord $record): void
    {
        $this->records[] = $record;
    }

    public function findBetween(string $from, string $to): array
    {
        return [];
    }
}
