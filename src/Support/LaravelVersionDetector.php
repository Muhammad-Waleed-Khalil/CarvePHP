<?php

declare(strict_types=1);

namespace Carve\Support;

final class LaravelVersionDetector
{
    public function detect(): string
    {
        return app()->version();
    }

    public function major(): int
    {
        return (int) explode('.', $this->detect())[0];
    }
}
