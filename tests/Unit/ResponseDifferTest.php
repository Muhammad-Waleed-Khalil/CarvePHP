<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Shadow\ResponseDiffer;
use Carve\Shadow\ResponseNormalizer;
use PHPUnit\Framework\TestCase;

final class ResponseDifferTest extends TestCase
{
    private ResponseDiffer $differ;

    protected function setUp(): void
    {
        $normalizer = new ResponseNormalizer([
            '$.meta.timestamp',
            '$.request_id',
        ]);
        $this->differ = new ResponseDiffer($normalizer);
    }

    public function test_identical_responses_have_no_diffs(): void
    {
        $response = ['id' => 1, 'name' => 'test'];
        $diffs = $this->differ->diff($response, $response);

        $this->assertEmpty($diffs);
    }

    public function test_detects_type_mismatch(): void
    {
        $monolith = ['amount' => '100.00'];
        $service = ['amount' => 100];

        $diffs = $this->differ->diff($monolith, $service);

        $this->assertNotEmpty($diffs);
        $this->assertEquals('type_mismatch', $diffs[0]->type);
    }

    public function test_detects_missing_keys(): void
    {
        $monolith = ['id' => 1, 'name' => 'test'];
        $service = ['id' => 1];

        $diffs = $this->differ->diff($monolith, $service);

        $this->assertNotEmpty($diffs);
    }

    public function test_ignores_configured_paths(): void
    {
        $monolith = ['data' => ['value' => 1], 'meta' => ['timestamp' => '2026-01-01']];
        $service = ['data' => ['value' => 1], 'meta' => ['timestamp' => '2026-06-27']];

        $diffs = $this->differ->diff($monolith, $service);

        $this->assertEmpty($diffs);
    }
}
