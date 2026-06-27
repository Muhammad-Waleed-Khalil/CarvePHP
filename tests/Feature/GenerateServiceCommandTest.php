<?php

declare(strict_types=1);

namespace Carve\Tests\Feature;

use Carve\CarveServiceProvider;
use Orchestra\Testbench\TestCase;

final class GenerateServiceCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CarveServiceProvider::class,
        ];
    }

    public function test_generate_service_command_runs(): void
    {
        $this->artisan('carve:generate-service', [
            'boundary' => 'test-boundary',
            '--dry-run' => true,
        ])->assertSuccessful();
    }
}
