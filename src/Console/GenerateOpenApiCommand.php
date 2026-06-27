<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Contracts\OpenApiGenerator;
use Illuminate\Console\Command;

final class GenerateOpenApiCommand extends Command
{
    protected $signature = 'carve:generate-openapi {boundary : The boundary slug}
        {--boundaries=storage/app/carve/boundaries.json : Boundaries JSON path}
        {--output=carve-output/openapi : Output directory}
        {--force : Overwrite existing files}';

    protected $description = 'Generate an OpenAPI spec for a boundary candidate';

    public function handle(): int
    {
        $boundary = $this->argument('boundary');
        $this->info("Generating OpenAPI spec for: {$boundary}");

        $generator = app(OpenApiGenerator::class);
        $spec = $generator->generate($boundary, $this->option('boundaries'));

        $outputDir = $this->option('output');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents("{$outputDir}/{$boundary}.yaml", $spec);

        $this->info("OpenAPI spec written to {$outputDir}/{$boundary}.yaml");

        return 0;
    }
}
