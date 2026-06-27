<?php

declare(strict_types=1);

namespace Carve\Contracts;

final class EventContractGenerator
{
    public function generate(string $eventClass, int $version = 1): array
    {
        $shortName = class_basename($eventClass);

        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'title' => "{$shortName}.v{$version}",
            'type' => 'object',
            'required' => ['event_id', 'occurred_at'],
            'properties' => [
                'event_id' => ['type' => 'string', 'format' => 'uuid'],
                'occurred_at' => ['type' => 'string', 'format' => 'date-time'],
            ],
            'additionalProperties' => true,
        ];
    }
}
