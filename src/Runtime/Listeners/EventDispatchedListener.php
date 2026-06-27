<?php

declare(strict_types=1);

namespace Carve\Runtime\Listeners;

use Carve\Runtime\TraceContextManager;

final class EventDispatchedListener
{
    private const IGNORED_PREFIXES = [
        'Illuminate\\Database\\Events\\',
        'Illuminate\\Routing\\Events\\',
        'Illuminate\\Console\\Events\\',
        'Illuminate\\Mail\\Events\\',
        'Illuminate\\Notifications\\Events\\',
    ];

    public function handle(object $event): void
    {
        $context = TraceContextManager::current();

        if ($context === null) {
            return;
        }

        $eventClass = $event::class;

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if (str_starts_with($eventClass, $prefix)) {
                return;
            }
        }

        $context->events[] = [
            'event_type' => 'laravel_event',
            'class_name' => $eventClass,
        ];
    }
}
