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

        return 'Boundary_'.implode('_', array_slice($tables, 0, 2));
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

        $first = $tables[0];
        $prefix = '';

        for ($i = 1; $i <= strlen($first); $i++) {
            $candidate = substr($first, 0, $i);

            foreach ($tables as $table) {
                if (! str_starts_with($table, $candidate)) {
                    break 2;
                }
            }

            $prefix = $candidate;
        }

        // Trim trailing underscore and require a meaningful prefix length
        $prefix = rtrim($prefix, '_');

        return strlen($prefix) >= 3 ? $prefix : null;
    }
}
