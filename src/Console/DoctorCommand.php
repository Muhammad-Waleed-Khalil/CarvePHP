<?php

declare(strict_types=1);

namespace Carve\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

final class DoctorCommand extends Command
{
    protected $signature = 'carve:doctor';

    protected $description = 'Check environment readiness for CarvePHP';

    public function handle(): int
    {
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $configPublished = config('carve.enabled') !== null;
        $traceTableExists = Schema::hasTable('carve_traces');
        $routesDiscoverable = count(app('router')->getRoutes()) > 0;
        $modulesExist = is_dir(base_path('Modules'));
        $dbDriver = config('database.default');

        $this->info('CarvePHP Environment Check');
        $this->newLine();
        $this->line("PHP version:             {$phpVersion}");
        $this->line("Laravel version:         {$laravelVersion}");
        $this->line('Config published:        '.($configPublished ? '<info>Yes</info>' : '<fg=yellow>No</>'));
        $this->line('Trace table exists:      '.($traceTableExists ? '<info>Yes</info>' : '<fg=yellow>No</>'));
        $this->line('Routes discoverable:     '.($routesDiscoverable ? '<info>Yes</info>' : '<fg=yellow>No</>'));
        $this->line('Modules folder:          '.($modulesExist ? '<info>Yes</info>' : '<fg=yellow>No</>'));
        $this->line("DB driver:               {$dbDriver}");

        $warnings = [];

        if (version_compare($phpVersion, '8.2.0', '<')) {
            $warnings[] = 'PHP 8.2+ required';
        }

        if (! $configPublished) {
            $warnings[] = 'Run php artisan carve:install to publish config';
        }

        if (! $traceTableExists) {
            $warnings[] = 'Run php artisan migrate to create trace tables';
        }

        if (! empty($warnings)) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->line("  - {$warning}");
            }
        }

        return self::SUCCESS;
    }
}
