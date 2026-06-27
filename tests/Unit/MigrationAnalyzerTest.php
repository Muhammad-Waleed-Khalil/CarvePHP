<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\StaticAnalysis\MigrationAnalyzer;
use Carve\StaticAnalysis\PhpParserFactory;
use Carve\StaticAnalysis\ValueObjects\MigrationInfo;
use PHPUnit\Framework\TestCase;

final class MigrationAnalyzerTest extends TestCase
{
    private MigrationAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new MigrationAnalyzer(new PhpParserFactory);
    }

    public function test_extracts_migration_info(): void
    {
        $fixture = dirname(__DIR__).'/../examples/laravel-monolith-fixture/database/migrations/2024_01_01_000001_create_invoices_table.php';
        $files = [$fixture];

        $migrations = $this->analyzer->analyze($files);

        $this->assertCount(1, $migrations);

        $migration = $migrations[0];
        $this->assertInstanceOf(MigrationInfo::class, $migration);
        $this->assertStringEndsWith('create_invoices_table.php', $migration->file);
        $this->assertContains('invoices', $migration->createdTables);
        $this->assertContains('payments', $migration->createdTables);
        $this->assertContains('payments', $migration->droppedTables);
        $this->assertContains('invoices', $migration->droppedTables);
    }

    public function test_skips_non_migration_files(): void
    {
        $files = [__FILE__];
        $this->assertEmpty($this->analyzer->analyze($files));
    }

    public function test_returns_empty_for_empty_file_list(): void
    {
        $this->assertEmpty($this->analyzer->analyze([]));
    }
}
