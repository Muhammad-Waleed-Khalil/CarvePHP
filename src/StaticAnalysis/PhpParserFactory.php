<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use PhpParser\Parser;
use PhpParser\ParserFactory as PhpParserFactoryBase;

final class PhpParserFactory
{
    public function create(): Parser
    {
        $factory = new PhpParserFactoryBase;

        return $factory->createForHostVersion();
    }
}
