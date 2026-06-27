<?php

declare(strict_types=1);

namespace Carve\Runtime\Listeners;

use Carve\Runtime\TraceContextManager;
use Illuminate\Queue\Events\JobProcessing;

final class JobProcessingListener
{
    public function handle(JobProcessing $event): void
    {
        $context = TraceContextManager::current();

        if ($context === null) {
            return;
        }

        $context->jobClass = $event->job->resolveName();
    }
}
