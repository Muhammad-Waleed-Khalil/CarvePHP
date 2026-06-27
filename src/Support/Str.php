<?php

declare(strict_types=1);

namespace Carve\Support;

final class Str
{
    public static function classBasename(string $class): string
    {
        $parts = explode('\\', $class);

        return end($parts);
    }

    public static function snakeToPascal(string $input): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    public static function pascalToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public static function kebabToPascal(string $input): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $input)));
    }
}
