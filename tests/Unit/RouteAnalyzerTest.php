<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\StaticAnalysis\PhpParserFactory;
use Carve\StaticAnalysis\RouteAnalyzer;
use Carve\StaticAnalysis\ValueObjects\RouteInfo;
use PHPUnit\Framework\TestCase;

final class RouteAnalyzerTest extends TestCase
{
    private RouteAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new RouteAnalyzer(new PhpParserFactory);
    }

    public function test_extracts_routes_from_api_file(): void
    {
        $fixture = dirname(__DIR__).'/../examples/laravel-monolith-fixture/routes/api.php';
        $files = [$fixture];

        $routes = $this->analyzer->analyze($files);

        $this->assertCount(5, $routes);

        $indexRoute = $routes[0];
        $this->assertInstanceOf(RouteInfo::class, $indexRoute);
        $this->assertSame('GET', $indexRoute->method);
        $this->assertSame('/api/invoices', $indexRoute->uri);
        $this->assertSame('invoices.index', $indexRoute->name);
        $this->assertSame('App\Http\Controllers\InvoiceController', $indexRoute->controller);
        $this->assertSame('index', $indexRoute->controllerMethod);
    }

    public function test_skips_non_route_files(): void
    {
        $files = [__FILE__];
        $routes = $this->analyzer->analyze($files);
        $this->assertEmpty($routes);
    }

    public function test_returns_empty_for_empty_file_list(): void
    {
        $this->assertEmpty($this->analyzer->analyze([]));
    }
}
