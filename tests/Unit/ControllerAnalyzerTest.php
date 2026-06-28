<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\StaticAnalysis\ControllerAnalyzer;
use Carve\StaticAnalysis\PhpParserFactory;
use Carve\StaticAnalysis\ValueObjects\ClassInfo;
use PHPUnit\Framework\TestCase;

final class ControllerAnalyzerTest extends TestCase
{
    private ControllerAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new ControllerAnalyzer(new PhpParserFactory);
    }

    public function test_extracts_controller_info(): void
    {
        $fixture = dirname(__DIR__).'/../examples/laravel-monolith-fixture/app/Http/Controllers/InvoiceController.php';
        $files = [$fixture];

        $controllers = $this->analyzer->analyze($files);

        $this->assertCount(1, $controllers);

        $controller = $controllers[0];
        $this->assertInstanceOf(ClassInfo::class, $controller);
        $this->assertSame('InvoiceController', $controller->name);
        $this->assertSame('App\Http\Controllers', $controller->namespace);

        $methods = $controller->methods;
        $this->assertCount(2, $methods);

        $this->assertSame('index', $methods[0]->name);
        $this->assertSame('Illuminate\Http\JsonResponse', $methods[0]->returnType);

        $this->assertSame('store', $methods[1]->name);
        $this->assertSame('Illuminate\Http\JsonResponse', $methods[1]->returnType);

        $this->assertContains('App\Models\Invoice', $controller->dependencies);
    }

    public function test_skips_non_controller_files(): void
    {
        $files = [__FILE__];
        $this->assertEmpty($this->analyzer->analyze($files));
    }

    public function test_returns_empty_for_empty_file_list(): void
    {
        $this->assertEmpty($this->analyzer->analyze([]));
    }

    public function test_extracts_multiple_controllers(): void
    {
        $base = dirname(__DIR__).'/../examples/laravel-monolith-fixture';
        $files = [
            $base.'/app/Http/Controllers/InvoiceController.php',
            $base.'/app/Http/Controllers/PaymentController.php',
            $base.'/app/Http/Controllers/TicketController.php',
        ];

        $controllers = $this->analyzer->analyze($files);

        $this->assertCount(3, $controllers);
        $names = array_map(fn (ClassInfo $c) => $c->name, $controllers);
        $this->assertContains('InvoiceController', $names);
        $this->assertContains('PaymentController', $names);
        $this->assertContains('TicketController', $names);
    }
}
