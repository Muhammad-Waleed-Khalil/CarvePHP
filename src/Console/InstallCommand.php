<?php

declare(strict_types=1);

namespace Carve\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class InstallCommand extends Command
{
    protected $signature = 'carve:install
        {--force : Overwrite existing files}
        {--with-migrations : Publish migrations}
        {--with-stubs : Publish stub files}';

    protected $description = 'Publish CarvePHP config, migrations, and stubs';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--provider' => 'Carve\\CarveServiceProvider',
            '--tag' => 'carve-config',
            '--force' => $this->option('force'),
        ]);

        if ($this->option('with-migrations')) {
            $this->call('vendor:publish', [
                '--provider' => 'Carve\\CarveServiceProvider',
                '--tag' => 'carve-migrations',
                '--force' => $this->option('force'),
            ]);
        }

        if ($this->option('with-stubs')) {
            $target = base_path('resources/carve/stubs');
            File::copyDirectory(__DIR__.'/../../resources/stubs', $target);
            $this->info('Stubs published to resources/carve/stubs');
        }

        $this->info('CarvePHP installed successfully.');
        $this->newLine();
        $this->warn('Next steps:');
        $this->line('  1. php artisan migrate');
        $this->line('  2. php artisan carve:doctor');
        $this->line('  3. php artisan carve:scan --pretty');

        return 0;
    }
}
