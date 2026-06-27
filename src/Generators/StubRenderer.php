<?php

declare(strict_types=1);

namespace Carve\Generators;

final class StubRenderer
{
    private const STUB_PATH = __DIR__.'/../../resources/stubs';

    public function render(string $stubName, array $variables = []): string
    {
        $stubPath = self::STUB_PATH.'/'.$stubName;

        if (! file_exists($stubPath)) {
            return '// STUB NOT FOUND: '.$stubName;
        }

        $content = file_get_contents($stubPath);

        foreach ($variables as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        return $content;
    }
}
