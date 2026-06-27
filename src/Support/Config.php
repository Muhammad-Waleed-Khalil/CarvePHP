<?php

declare(strict_types=1);

namespace Carve\Support;

final class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return config("carve.{$key}", $default);
    }

    public static function tracingEnabled(): bool
    {
        return (bool) self::get('runtime_tracing.enabled', false);
    }

    public static function outputDir(): string
    {
        return (string) self::get('generation.default_output_dir', base_path('carve-output'));
    }
}
