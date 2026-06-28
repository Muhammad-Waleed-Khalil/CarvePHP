<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Illuminate\Filesystem\Filesystem;

final class SourceFileFinder
{
    public function __construct(
        private readonly Filesystem $files,
    ) {}

    public function find(array $paths, array $exclude = []): array
    {
        $files = [];

        $searchPaths = $paths !== [] ? $paths : [base_path()];

        foreach ($searchPaths as $path) {
            $absolutePath = $this->resolvePath($path);

            if (! $this->files->isDirectory($absolutePath)) {
                continue;
            }

            $root = base_path();

            $iterator = $this->files->allFiles($absolutePath);

            foreach ($iterator as $file) {
                $filePath = $file->getPathname();

                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($root.DIRECTORY_SEPARATOR, '', $filePath);

                if ($this->isExcluded($relativePath, $exclude)) {
                    continue;
                }

                $files[] = $filePath;
            }
        }

        return $files;
    }

    private function resolvePath(string $path): string
    {
        if ($path === base_path() || $this->files->isDirectory($path)) {
            return $path;
        }

        $resolved = base_path($path);

        if ($this->files->isDirectory($resolved)) {
            return $resolved;
        }

        return $path;
    }

    private function isExcluded(string $relativePath, array $exclude): bool
    {
        foreach ($exclude as $excludedPath) {
            $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $excludedPath);
            $normalized = rtrim($normalized, DIRECTORY_SEPARATOR);

            if (str_starts_with($relativePath, $normalized.DIRECTORY_SEPARATOR) || $relativePath === $normalized) {
                return true;
            }
        }

        return false;
    }
}
