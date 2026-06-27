<?php

declare(strict_types=1);

namespace Carve\Console;

use Illuminate\Console\Command;

final class TraceInstallCommand extends Command
{
    protected $signature = 'carve:trace-install
        {--middleware : Show middleware setup instructions}
        {--events : Show event listener setup instructions}
        {--queues : Show queue listener setup instructions}
        {--dry-run : Show instructions without making changes}';

    protected $description = 'Guide runtime tracing middleware/listener setup';

    public function handle(): int
    {
        $this->info('Runtime Tracing Setup Guide');
        $this->newLine();
        $this->line('Add the following to bootstrap/app.php or Http/Kernel.php:');
        $this->line('');
        $this->line('  ->withMiddleware(function (Middleware $middleware) {');
        $this->line('      $middleware->append(\\Carve\\Runtime\\Middleware\\CarveTraceMiddleware::class);');
        $this->line('  })');
        $this->newLine();
        $this->line('Register listeners in AppServiceProvider:');
        $this->line('');
        $this->line('  Event::listen(\\Illuminate\\Database\\Events\\QueryExecuted::class,');
        $this->line('      \\Carve\\Runtime\\Listeners\\QueryExecutedListener::class);');
        $this->line('  Event::listen(\\Illuminate\\Queue\\Events\\JobProcessing::class,');
        $this->line('      \\Carve\\Runtime\\Listeners\\JobProcessingListener::class);');

        $this->newLine();
        $this->warn('Runtime tracing does NOT modify your application files automatically.');
        $this->line('Enable tracing in .env: CARVE_TRACE_ENABLED=true');

        return 0;
    }
}
