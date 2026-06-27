<?php

declare(strict_types=1);

namespace Carve\Generators;

final class FileWriter
{
    public function write(string $path, string $content, bool $mkdir = false, bool $force = false): void
    {
        if ($mkdir) {
            $dir = dirname($path);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        if (file_exists($path) && ! $force) {
            $path = $this->backupPath($path);
        }

        file_put_contents($path, $content);
    }

    private function backupPath(string $path): string
    {
        $info = pathinfo($path);

        return $info['dirname'].DIRECTORY_SEPARATOR
            .$info['filename'].'.bak.'
            .($info['extension'] ?? '');
    }
}
