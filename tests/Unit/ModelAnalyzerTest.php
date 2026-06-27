<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\StaticAnalysis\ModelAnalyzer;
use Carve\StaticAnalysis\PhpParserFactory;
use Carve\StaticAnalysis\ValueObjects\ModelInfo;
use PHPUnit\Framework\TestCase;

final class ModelAnalyzerTest extends TestCase
{
    private ModelAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new ModelAnalyzer(new PhpParserFactory);
    }

    public function test_extracts_model_info(): void
    {
        $fixture = dirname(__DIR__).'/../examples/laravel-monolith-fixture/app/Models/Invoice.php';
        $files = [$fixture];

        $models = $this->analyzer->analyze($files);

        $this->assertCount(1, $models);

        $model = $models[0];
        $this->assertInstanceOf(ModelInfo::class, $model);
        $this->assertSame('App\Models\Invoice', $model->class);
        $this->assertSame('invoices', $model->table);
        $this->assertSame(['amount', 'status', 'user_id'], $model->fillable);
    }

    public function test_infers_table_name(): void
    {
        $fixture = dirname(__DIR__).'/../examples/laravel-monolith-fixture/app/Models/Payment.php';
        $files = [$fixture];

        $models = $this->analyzer->analyze($files);

        $this->assertCount(1, $models);
        $this->assertSame('payments', $models[0]->table);
        $this->assertSame(['invoice_id', 'amount', 'method'], $models[0]->fillable);
    }

    public function test_skips_non_model_files(): void
    {
        $files = [__FILE__];
        $this->assertEmpty($this->analyzer->analyze($files));
    }

    public function test_returns_empty_for_empty_file_list(): void
    {
        $this->assertEmpty($this->analyzer->analyze([]));
    }

    public function test_extracts_multiple_models(): void
    {
        $base = dirname(__DIR__).'/../examples/laravel-monolith-fixture';
        $files = [
            $base.'/app/Models/Invoice.php',
            $base.'/app/Models/Payment.php',
            $base.'/app/Models/Ticket.php',
        ];

        $models = $this->analyzer->analyze($files);
        $this->assertCount(3, $models);

        $classes = array_map(fn (ModelInfo $m) => $m->class, $models);
        $this->assertContains('App\Models\Invoice', $classes);
        $this->assertContains('App\Models\Payment', $classes);
        $this->assertContains('App\Models\Ticket', $classes);
    }
}
