<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\StaticAnalysis\RawSqlTableExtractor;
use PHPUnit\Framework\TestCase;

final class RawSqlTableExtractorTest extends TestCase
{
    private RawSqlTableExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new RawSqlTableExtractor();
    }

    public function test_extracts_from_select(): void
    {
        $tables = $this->extractor->extract('select * from invoices where id = 1');

        $this->assertContains('invoices', $tables);
    }

    public function test_extracts_from_insert(): void
    {
        $tables = $this->extractor->extract('insert into payments (amount) values (100)');

        $this->assertContains('payments', $tables);
    }

    public function test_extracts_from_update(): void
    {
        $tables = $this->extractor->extract('update users set name = ? where id = 1');

        $this->assertContains('users', $tables);
    }

    public function test_extracts_from_delete(): void
    {
        $tables = $this->extractor->extract('delete from tickets where id = 1');

        $this->assertContains('tickets', $tables);
    }

    public function test_extracts_join_tables(): void
    {
        $tables = $this->extractor->extract('select * from invoices join payments on invoices.id = payments.invoice_id');

        $this->assertContains('invoices', $tables);
        $this->assertContains('payments', $tables);
    }

    public function test_returns_empty_for_no_sql(): void
    {
        $tables = $this->extractor->extract('<?php echo "hello";');

        $this->assertEmpty($tables);
    }

    public function test_does_not_duplicate_tables(): void
    {
        $tables = $this->extractor->extract('select * from invoices where id in (select invoice_id from payments)');

        $unique = array_unique($tables);
        $this->assertCount(count($unique), $tables);
    }
}
