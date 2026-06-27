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

        foreach ($paths as $path) {
            $absolutePath = base_path($path);

            if (! $this->files->isDirectory($absolutePath)) {
                continue;
            }

            $iterator = $this->files->allFiles($absolutePath);

            foreach ($iterator as $file) {
                $filePath = $file->getPathname();

                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $filePath);

                $excluded = false;
                foreach ($exclude as $excludedPath) {
                    if (str_starts_with($relativePath, $excludedPath)) {
                        $excluded = true;
                        break;
                    }
                }

                if (! $excluded) {
                    $files[] = $filePath;
                }
            }
        }

        return $files;
    }
}
