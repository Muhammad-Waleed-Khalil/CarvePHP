<?php

declare(strict_types=1);

namespace Carve\Tests\Feature;

use Carve\CarveServiceProvider;
use Orchestra\Testbench\TestCase;

final class ScanCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CarveServiceProvider::class,
        ];
    }

    public function test_scan_command_runs(): void
    {
        $this->artisan('carve:scan', ['--output' => __DIR__.'/../Fixtures/test-scan.json'])
            ->assertSuccessful();
    }
}
