<?php

declare(strict_types=1);

namespace Carve\Runtime\Http;

use Carve\Runtime\TraceContextManager;
use Illuminate\Http\Client\PendingRequest;

final class PendingRequestMacro
{
    public static function register(): void
    {
        PendingRequest::macro('carveTrace', function () {
            $context = TraceContextManager::current();

            if ($context !== null) {
                $this->withHeaders([
                    'X-Carve-Trace-Id' => $context->traceId,
                ]);
            }

            return $this;
        });
    }
}
