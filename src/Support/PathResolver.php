<?php

declare(strict_types=1);

namespace Carve\Support;

final class PathResolver
{
    public function resolve(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return base_path($path);
    }

    public function outputDir(string $subpath = ''): string
    {
        $base = config('carve.generation.default_output_dir', base_path('carve-output'));

        if ($subpath !== '') {
            return rtrim($base, '/\\').'/'.ltrim($subpath, '/\\');
        }

        return $base;
    }
}
