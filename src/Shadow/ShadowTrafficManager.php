<?php

declare(strict_types=1);

namespace Carve\Shadow;

final class ShadowTrafficManager
{
    private array $routes = [];

    public function enable(string $route, string $target): void
    {
        $this->routes[$route] = [
            'enabled' => true,
            'target' => $target,
        ];
    }

    public function disable(string $route): void
    {
        if (isset($this->routes[$route])) {
            $this->routes[$route]['enabled'] = false;
        }
    }

    public function isEnabled(string $route): bool
    {
        return isset($this->routes[$route]) && $this->routes[$route]['enabled'];
    }

    public function report(int $last = 1000): string
    {
        $lines = ["Shadow Traffic Report", "=====================", ""];

        foreach ($this->routes as $route => $config) {
            $status = $config['enabled'] ? 'ENABLED' : 'DISABLED';
            $lines[] = "{$route}: {$status} -> {$config['target']}";
        }

        if (empty($this->routes)) {
            $lines[] = 'No shadow routes configured.';
        }

        return implode("\n", $lines);
    }
}
