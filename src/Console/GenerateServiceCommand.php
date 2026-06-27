<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Generators\ServiceGenerator;
use Illuminate\Console\Command;

final class GenerateServiceCommand extends Command
{
    protected $signature = 'carve:generate-service {boundary : The boundary slug to generate}
        {--boundaries=storage/app/carve/boundaries.json : Boundaries JSON path}
        {--runtime=laravel : Target runtime}
        {--output=carve-output/services : Output directory}
        {--with-openapi : Generate OpenAPI spec}
        {--with-client : Generate monolith client SDK}
        {--with-tests : Generate contract test stubs}
        {--dry-run : Show what would be generated}
        {--force : Overwrite existing files}';

    protected $description = 'Generate a service skeleton for a boundary candidate';

    public function handle(): int
    {
        $boundary = $this->argument('boundary');
        $this->info("Generating service skeleton for: {$boundary}");

        $generator = app(ServiceGenerator::class);
        $manifest = $generator->generate(
            boundary: $boundary,
            boundariesPath: $this->option('boundaries'),
            runtime: $this->option('runtime'),
            outputDir: $this->option('output'),
            options: [
                'with_openapi' => $this->option('with-openapi'),
                'with_client' => $this->option('with-client'),
                'with_tests' => $this->option('with-tests'),
                'dry_run' => $this->option('dry-run'),
                'force' => $this->option('force'),
            ],
        );

        $this->info("Service generated at {$this->option('output')}/{$boundary}");

        if (! empty($manifest['warnings'])) {
            foreach ($manifest['warnings'] as $warning) {
                $this->warn($warning);
            }
        }

        return self::SUCCESS;
    }
}
