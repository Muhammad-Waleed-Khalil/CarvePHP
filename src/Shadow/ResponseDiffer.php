<?php

declare(strict_types=1);

namespace Carve\Shadow;

use Carve\Shadow\ValueObjects\ResponseDiff;

final class ResponseDiffer
{
    public function __construct(
        private readonly ResponseNormalizer $normalizer,
    ) {}

    public function diff(array $monolith, array $service): array
    {
        $monolith = $this->normalizer->normalize($monolith);
        $service = $this->normalizer->normalize($service);

        $diffs = [];
        $this->compareValues($monolith, $service, '', $diffs);

        return $diffs;
    }

    private function compareValues(mixed $a, mixed $b, string $path, array &$diffs): void
    {
        if (gettype($a) !== gettype($b)) {
            $diffs[] = new ResponseDiff(
                path: $path ?: '$',
                monolith: $a,
                service: $b,
                type: 'type_mismatch',
            );

            return;
        }

        if (is_array($a) && is_array($b)) {
            $allKeys = array_unique(array_merge(array_keys($a), array_keys($b)));

            foreach ($allKeys as $key) {
                $newPath = $path ? "{$path}.{$key}" : $key;

                if (! array_key_exists($key, $a)) {
                    $diffs[] = new ResponseDiff($newPath, null, $b[$key], 'missing_in_monolith');
                } elseif (! array_key_exists($key, $b)) {
                    $diffs[] = new ResponseDiff($newPath, $a[$key], null, 'missing_in_service');
                } else {
                    $this->compareValues($a[$key], $b[$key], $newPath, $diffs);
                }
            }
        } elseif ($a !== $b) {
            $diffs[] = new ResponseDiff(
                path: $path ?: '$',
                monolith: $a,
                service: $b,
                type: 'value_mismatch',
            );
        }
    }
}
