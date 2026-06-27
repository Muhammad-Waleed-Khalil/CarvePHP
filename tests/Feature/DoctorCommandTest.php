<?php

declare(strict_types=1);

namespace Carve\Tests\Feature;

use Carve\CarveServiceProvider;
use Orchestra\Testbench\TestCase;

final class DoctorCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CarveServiceProvider::class,
        ];
    }

    public function test_doctor_command_runs(): void
    {
        $this->artisan('carve:doctor')
            ->assertSuccessful();
    }
}
