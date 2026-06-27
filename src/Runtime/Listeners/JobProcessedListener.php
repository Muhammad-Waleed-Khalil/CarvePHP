<?php

declare(strict_types=1);

namespace Carve\Runtime\Listeners;

use Carve\Runtime\TraceContextManager;
use Illuminate\Queue\Events\JobProcessed;

final class JobProcessedListener
{
    public function handle(JobProcessed $event): void
    {
        $context = TraceContextManager::current();

        if ($context === null) {
            return;
        }

        $context->events[] = [
            'event_type' => 'queue_job',
            'class_name' => $event->job->resolveName(),
            'status' => 'processed',
        ];
    }
}
