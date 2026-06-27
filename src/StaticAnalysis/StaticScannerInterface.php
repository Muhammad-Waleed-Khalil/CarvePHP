<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

interface StaticScannerInterface
{
    public function scan(array $paths, array $exclude = []): array;
}
