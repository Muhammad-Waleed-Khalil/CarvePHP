<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Generators\ClientSdkGenerator;
use Illuminate\Console\Command;

final class GenerateClientCommand extends Command
{
    protected $signature = 'carve:generate-client {boundary : The boundary slug}
        {--boundaries=storage/app/carve/boundaries.json : Boundaries JSON path}
        {--output=carve-output/clients : Output directory}
        {--force : Overwrite existing files}';

    protected $description = 'Generate a monolith client SDK for a boundary candidate';

    public function handle(): int
    {
        $boundary = $this->argument('boundary');
        $this->info("Generating client SDK for: {$boundary}");

        $generator = app(ClientSdkGenerator::class);
        $generator->generate(
            boundary: $boundary,
            boundariesPath: $this->option('boundaries'),
            outputDir: $this->option('output'),
        );

        $this->info("Client SDK written to {$this->option('output')}");

        return 0;
    }
}
