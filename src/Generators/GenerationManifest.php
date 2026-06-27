<?php

declare(strict_types=1);

namespace Carve\Generators;

final class GenerationManifest
{
    public function generate(string $boundary, array $files, array $extra = []): array
    {
        $manifest = array_merge([
            'generated_at' => date('c'),
            'carve_version' => '0.1.0',
            'boundary' => $boundary,
            'files' => $files,
        ], $extra);

        return $manifest;
    }
}
