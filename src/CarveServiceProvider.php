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
use Illuminate\Support\ServiceProvider;

final class CarveServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/carve.php', 'carve');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/carve.php' => config_path('carve.php'),
            ], 'carve-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'carve-migrations');

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
        }
    }

    public function register(): void
    {
        $this->app->singleton('carve', fn () => new Carve());
    }
}
