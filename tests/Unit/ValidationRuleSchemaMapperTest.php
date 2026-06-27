<?php

declare(strict_types=1);

namespace Carve\Tests\Unit;

use Carve\Contracts\ValidationRuleSchemaMapper;
use PHPUnit\Framework\TestCase;

final class ValidationRuleSchemaMapperTest extends TestCase
{
    private ValidationRuleSchemaMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ValidationRuleSchemaMapper();
    }

    public function test_maps_string_rule(): void
    {
        $result = $this->mapper->map(['name' => 'required|string']);

        $this->assertEquals('object', $result['type']);
        $this->assertEquals(['name'], $result['required']);
        $this->assertEquals('string', $result['properties']['name']['type']);
    }

    public function test_maps_integer_rule(): void
    {
        $result = $this->mapper->map(['age' => 'integer']);

        $this->assertEquals('integer', $result['properties']['age']['type']);
    }

    public function test_maps_numeric_rule(): void
    {
        $result = $this->mapper->map(['amount' => 'numeric']);

        $this->assertEquals('number', $result['properties']['amount']['type']);
    }

    public function test_maps_boolean_rule(): void
    {
        $result = $this->mapper->map(['active' => 'boolean']);

        $this->assertEquals('boolean', $result['properties']['active']['type']);
    }

    public function test_maps_email_rule(): void
    {
        $result = $this->mapper->map(['email' => 'email']);

        $this->assertEquals('string', $result['properties']['email']['type']);
        $this->assertEquals('email', $result['properties']['email']['format']);
    }

    public function test_maps_array_rule(): void
    {
        $result = $this->mapper->map(['items' => 'array']);

        $this->assertEquals('array', $result['properties']['items']['type']);
    }

    public function test_maps_nullable_rule(): void
    {
        $result = $this->mapper->map(['comment' => 'nullable|string']);

        $this->assertTrue($result['properties']['comment']['nullable']);
    }

    public function test_maps_min_max_as_array(): void
    {
        $result = $this->mapper->map(['age' => 'integer|min:18|max:120']);

        $this->assertEquals(18, $result['properties']['age']['min']);
        $this->assertEquals(120, $result['properties']['age']['max']);
    }
}
