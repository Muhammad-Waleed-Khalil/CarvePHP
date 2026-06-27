<?php

declare(strict_types=1);

namespace Carve\Shadow;

final class ResponseNormalizer
{
    private array $ignorePaths = [];

    public function __construct(array $ignorePaths = [])
    {
        $this->ignorePaths = $ignorePaths;
    }

    public function normalize(array $response): array
    {
        $normalized = $response;

        foreach ($this->ignorePaths as $path) {
            $normalized = $this->removePath($normalized, $path);
        }

        return $this->sortKeys($normalized);
    }

    private function removePath(array $data, string $path): array
    {
        $parts = explode('.', str_replace(['$.', '$.'], '', $path));
        $current = &$data;

        foreach ($parts as $i => $part) {
            if ($part === '*' || $part === '[]') {
                foreach ($current as &$item) {
                    if (is_array($item)) {
                        $item = $this->removePath($item, implode('.', array_slice($parts, $i + 1)));
                    }
                }
                unset($item);
                break;
            }

            if ($i === count($parts) - 1) {
                unset($current[$part]);
            } elseif (isset($current[$part])) {
                $current = &$current[$part];
            } else {
                break;
            }
        }

        return $data;
    }

    private function sortKeys(array $data): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sortKeys($value);
            }
        }

        return $data;
    }
}
