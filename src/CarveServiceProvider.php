<?php

declare(strict_types=1);

namespace Carve;

use Carve\Console\AnalyzeCommand;
use Carve\Console\BoundariesCommand;
use Carve\Console\DiffCommand;
use Carve\Console\DoctorCommand;
use Carve\Console\GenerateClientCommand;
use Carve\Console\GenerateOpenApiCommand;
use Carve\Console\GenerateServiceCommand;
use Carve\Console\InstallCommand;
use Carve\Console\ReportCommand;
use Carve\Console\ScanCommand;
use Carve\Console\ShadowCommand;
use Carve\Console\TraceInstallCommand;
use Carve\Runtime\Http\PendingRequestMacro;
use Carve\Runtime\Listeners\JobFailedListener;
use Carve\Runtime\Listeners\JobProcessedListener;
use Carve\Runtime\Listeners\JobProcessingListener;
use Carve\Runtime\Listeners\QueryExecutedListener;
use Carve\Runtime\Middleware\CarveTraceMiddleware;
use Carve\Runtime\Stores\DatabaseTraceStore;
use Carve\Runtime\Stores\JsonlTraceStore;
use Carve\Runtime\Stores\NullTraceStore;
use Carve\Runtime\TraceRecorder;
use Carve\Runtime\TraceStoreInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

final class CarveServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/carve.php', 'carve');

        PendingRequestMacro::register();

        $this->commands([
            InstallCommand::class,
            DoctorCommand::class,
            ScanCommand::class,
            TraceInstallCommand::class,
            AnalyzeCommand::class,
            BoundariesCommand::class,
            ReportCommand::class,
            GenerateServiceCommand::class,
            GenerateOpenApiCommand::class,
            GenerateClientCommand::class,
            ShadowCommand::class,
            DiffCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/carve.php' => config_path('carve.php'),
            ], 'carve-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'carve-migrations');
        }

        $this->registerListeners();
    }

    public function register(): void
    {
        $this->app->singleton('carve', fn () => new Carve);

        $this->app->bind(TraceStoreInterface::class, function ($app) {
            $store = config('carve.runtime_tracing.store', 'database');

            return match ($store) {
                'database' => new DatabaseTraceStore,
                'jsonl' => new JsonlTraceStore(config('carve.runtime_tracing.jsonl_path', storage_path('logs/carve-traces.jsonl'))),
                default => new NullTraceStore,
            };
        });

        $this->app->singleton(TraceRecorder::class, function ($app) {
            return new TraceRecorder($app->make(TraceStoreInterface::class));
        });

        $this->app->singleton(CarveTraceMiddleware::class, function ($app) {
            return new CarveTraceMiddleware($app->make(TraceRecorder::class));
        });
    }

    private function registerListeners(): void
    {
        if (! config('carve.runtime_tracing.enabled', false)) {
            return;
        }

        $events = $this->app->make('events');

        $events->listen(QueryExecuted::class, QueryExecutedListener::class);
        $events->listen(JobProcessing::class, JobProcessingListener::class);
        $events->listen(JobProcessed::class, JobProcessedListener::class);
        $events->listen(JobFailed::class, JobFailedListener::class);
    }
}
