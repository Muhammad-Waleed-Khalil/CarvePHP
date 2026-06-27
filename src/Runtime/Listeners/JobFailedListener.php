<?php

declare(strict_types=1);

namespace Carve\Runtime\Listeners;

use Carve\Runtime\TraceContextManager;
use Illuminate\Queue\Events\JobFailed;

final class JobFailedListener
{
    public function handle(JobFailed $event): void
    {
        $context = TraceContextManager::current();

        if ($context === null) {
            return;
        }

        $context->events[] = [
            'event_type' => 'queue_job',
            'class_name' => $event->job->resolveName(),
            'status' => 'failed',
            'exception' => $event->exception->getMessage(),
        ];
    }
}
