<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

final class FormRequestAnalyzer
{
    public function analyze(array $files): array
    {
        $formRequests = [];

        foreach ($files as $file) {
            if (str_contains($file, 'Http' . DIRECTORY_SEPARATOR . 'Requests')) {
            }
        }

        return $formRequests;
    }
}
