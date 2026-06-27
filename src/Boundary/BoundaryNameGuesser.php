<?php

declare(strict_types=1);

namespace Carve\Boundary;

final class BoundaryNameGuesser
{
    public function guess(array $cluster): string
    {
        $tables = $cluster['tables'] ?? [];

        if (empty($tables)) {
            return 'Unknown';
        }

        $name = $this->fromTableNames($tables);

        if ($name !== null) {
            return $name;
        }

        return 'Boundary_' . implode('_', array_slice($tables, 0, 2));
    }

    private function fromTableNames(array $tables): ?string
    {
        $commonPrefix = $this->findCommonPrefix($tables);

        if ($commonPrefix !== null) {
            return ucfirst($commonPrefix);
        }

        return null;
    }

    private function findCommonPrefix(array $tables): ?string
    {
        if (count($tables) < 2) {
            return null;
        }

        $parts = array_map(fn ($t) => explode('_', $t), $tables);
        $first = $parts[0];

        for ($i = count($first) - 1; $i >= 1; $i--) {
            $candidate = implode('_', array_slice($first, 0, $i));
            $match = true;

            foreach ($parts as $p) {
                if (! str_starts_with(implode('_', $p), $candidate)) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $candidate;
            }
        }

        return null;
    }
}
