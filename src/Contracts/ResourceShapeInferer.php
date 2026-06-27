<?php

declare(strict_types=1);

namespace Carve\Contracts;

final class ResourceShapeInferer
{
    public function infer(string $fileContent): array
    {
        $shape = [];
        $pattern = '/\'(\w+)\'\s*=>\s*\$this->(\w+)/';

        if (preg_match_all($pattern, $fileContent, $matches)) {
            foreach ($matches[1] as $i => $key) {
                $shape[$key] = $this->guessType($matches[2][$i]);
            }
        }

        return [
            'type' => 'object',
            'properties' => $shape,
            'additionalProperties' => true,
        ];
    }

    private function guessType(string $property): string
    {
        $typeHints = [
            'id' => 'integer',
            'count' => 'integer',
            'amount' => 'number',
            'price' => 'number',
            'total' => 'number',
            'is_' => 'boolean',
            'has_' => 'boolean',
            'created_at' => 'string/date-time',
            'updated_at' => 'string/date-time',
            'deleted_at' => 'string/date-time',
        ];

        foreach ($typeHints as $key => $type) {
            if (str_starts_with($property, $key)) {
                return $type;
            }
        }

        return 'string';
    }
}
