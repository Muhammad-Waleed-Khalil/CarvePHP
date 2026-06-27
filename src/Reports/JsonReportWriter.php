<?php

declare(strict_types=1);

namespace Carve\Reports;

final class JsonReportWriter
{
    public function generate(array $data, bool $pretty = true): string
    {
        return json_encode(
            array_merge($data, ['generated_at' => date('c')]),
            $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0,
        );
    }
}
