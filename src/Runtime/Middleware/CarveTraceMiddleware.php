<?php

declare(strict_types=1);

namespace Carve\Runtime\Middleware;

use Carve\Runtime\TraceContextManager;
use Carve\Runtime\TraceRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CarveTraceMiddleware
{
    public function __construct(
        private readonly TraceRecorder $recorder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('carve.runtime_tracing.enabled', false)) {
            return $next($request);
        }

        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        if (! $this->shouldSample()) {
            return $next($request);
        }

        $context = TraceContextManager::start();
        $context->requestId = $request->header('X-Request-Id');
        $context->method = $request->method();
        $context->uri = $request->path();

        $route = $request->route();
        if ($route !== null) {
            $context->routeName = $route->getName();
            $context->controllerAction = $route->getActionName();
        }

        if (config('carve.runtime_tracing.capture_user_id', false) && $request->user()) {
            $context->userId = (string) $request->user()->getAuthIdentifier();
        }

        try {
            $response = $next($request);
            $context->statusCode = $response->getStatusCode();

            return $response;
        } catch (\Throwable $e) {
            $context->exceptionClass = $e::class;
            $context->exceptionMessage = $e->getMessage();
            throw $e;
        } finally {
            $this->recorder->flush();
        }
    }

    private function shouldIgnore(Request $request): bool
    {
        $ignored = config('carve.runtime_tracing.ignored_routes', []);
        $route = $request->route();

        if ($route === null) {
            return false;
        }

        $name = $route->getName();

        foreach ($ignored as $pattern) {
            $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
            if ($name !== null && preg_match($regex, $name)) {
                return true;
            }
        }

        return false;
    }

    private function shouldSample(): bool
    {
        $rate = config('carve.runtime_tracing.sample_rate', 1.0);

        return $rate >= 1.0 || (mt_rand() / mt_getrandmax()) < $rate;
    }
}
