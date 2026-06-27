<?php

declare(strict_types=1);

namespace Carve\Support;

final class ComposerPackageDetector
{
    public function detect(string $packageName): bool
    {
        $installed = base_path('vendor/composer/installed.json');

        if (! file_exists($installed)) {
            return false;
        }

        $data = json_decode(file_get_contents($installed), true);
        $packages = $data['packages'] ?? $data ?? [];

        foreach ($packages as $package) {
            if (($package['name'] ?? '') === $packageName) {
                return true;
            }
        }

        return false;
    }
}
