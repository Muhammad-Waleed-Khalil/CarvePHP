<?php

declare(strict_types=1);

namespace Carve\Contracts;

final class SchemaInferer
{
    public function inferFromArray(array $data): array
    {
        $properties = [];

        foreach ($data as $key => $value) {
            $properties[$key] = $this->inferType($value);
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'additionalProperties' => true,
        ];
    }

    private function inferType(mixed $value): array
    {
        return match (true) {
            is_int($value) => ['type' => 'integer'],
            is_float($value) => ['type' => 'number'],
            is_bool($value) => ['type' => 'boolean'],
            is_array($value) => ['type' => 'array'],
            is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value) => [
                'type' => 'string',
                'format' => 'date-time',
            ],
            default => ['type' => 'string'],
        };
    }
}
