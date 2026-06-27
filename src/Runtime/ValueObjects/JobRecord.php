<?php

declare(strict_types=1);

namespace Carve\Runtime\ValueObjects;

final class JobRecord
{
    public function __construct(
        public readonly string $jobClass,
        public readonly string $status,
        public readonly ?string $exception = null,
    ) {}
}
