<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis\ValueObjects;

final class RouteInfo
{
    public function __construct(
        public readonly string $id,
        public readonly string $method,
        public readonly string $uri,
        public readonly ?string $name,
        public readonly ?string $action,
        public readonly ?string $controller,
        public readonly ?string $controllerMethod,
        public readonly array $middleware,
        public readonly ?string $file,
        public readonly ?int $line,
        public readonly ?string $prefix,
        public readonly ?string $domain,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'uri' => $this->uri,
            'name' => $this->name,
            'action' => $this->action,
            'controller' => $this->controller,
            'controller_method' => $this->controllerMethod,
            'middleware' => $this->middleware,
            'file' => $this->file,
            'line' => $this->line,
            'prefix' => $this->prefix,
            'domain' => $this->domain,
        ];
    }
}
