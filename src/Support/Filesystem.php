<?php

declare(strict_types=1);

namespace Carve\Support;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

final class Filesystem
{
    public function __construct(
        private readonly IlluminateFilesystem $files,
    ) {}

    public function ensureDirectoryExists(string $path, int $mode = 0755): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, $mode, true, true);
        }
    }

    public function writeFile(string $path, string $content): void
    {
        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
    }

    public function fileExists(string $path): bool
    {
        return $this->files->exists($path);
    }
}
