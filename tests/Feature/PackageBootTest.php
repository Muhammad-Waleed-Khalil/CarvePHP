<?php

declare(strict_types=1);

namespace Carve\Tests\Feature;

use Carve\CarveServiceProvider;
use Orchestra\Testbench\TestCase;

final class PackageBootTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CarveServiceProvider::class,
        ];
    }

    public function test_service_provider_is_registered(): void
    {
        $this->assertInstanceOf(
            CarveServiceProvider::class,
            $this->app->getProvider(CarveServiceProvider::class),
        );
    }

    public function test_config_is_merged(): void
    {
        $this->assertIsArray(config('carve'));
    }

    public function test_commands_are_registered(): void
    {
        $commands = [
            'carve:install',
            'carve:doctor',
            'carve:scan',
            'carve:trace-install',
            'carve:analyze',
            'carve:boundaries',
            'carve:report',
            'carve:generate-service',
            'carve:generate-openapi',
            'carve:generate-client',
            'carve:shadow',
            'carve:diff',
        ];

        foreach ($commands as $command) {
            $this->assertTrue($this->app->bound("command.{$command}") || $this->artisan($command));
        }
    }
}
