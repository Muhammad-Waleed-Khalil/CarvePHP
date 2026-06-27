<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Carve\StaticAnalysis\ValueObjects\MigrationInfo;
use PhpParser\Node;

final class MigrationAnalyzer
{
    public function __construct(
        private readonly PhpParserFactory $parserFactory,
    ) {}

    public function analyze(array $files): array
    {
        $parser = $this->parserFactory->create();
        $migrations = [];

        foreach ($files as $file) {
            if (! str_contains($file, 'migrations'.DIRECTORY_SEPARATOR) && ! str_contains($file, 'migrations/')) {
                continue;
            }

            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            $stmts = $parser->parse($code);
            if ($stmts === null) {
                continue;
            }

            $info = $this->extractMigrationInfo($stmts, $file);
            if ($info !== null) {
                $migrations[] = $info;
            }
        }

        return $migrations;
    }

    private function extractMigrationInfo(array $stmts, string $file): ?MigrationInfo
    {
        $classNode = $this->findMigrationClass($stmts);
        if ($classNode === null) {
            return null;
        }

        $created = [];
        $modified = [];
        $dropped = [];

        foreach (['up', 'down'] as $methodName) {
            $method = $this->findMethod($classNode, $methodName);
            if ($method !== null && $method->stmts !== null) {
                foreach ($method->stmts as $stmt) {
                    $this->extractSchemaOps($stmt, $created, $modified, $dropped);
                }
            }
        }

        return new MigrationInfo(
            file: $file,
            createdTables: $created,
            modifiedTables: $modified,
            droppedTables: $dropped,
        );
    }

    private function findMigrationClass(array $stmts): ?Node\Stmt\Class_
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Class_ && $stmt->extends !== null) {
                $extends = $stmt->extends->toString();
                if (str_ends_with($extends, 'Migration')) {
                    return $stmt;
                }
            }
            // Handle "return new class extends Migration { ... }"
            if ($stmt instanceof Node\Stmt\Return_
                && $stmt->expr instanceof Node\Expr\New_
                && $stmt->expr->class instanceof Node\Stmt\Class_
                && $stmt->expr->class->extends !== null
                && str_ends_with($stmt->expr->class->extends->toString(), 'Migration')
            ) {
                return $stmt->expr->class;
            }
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $found = $this->findMigrationClass($stmt->stmts);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function findMethod(Node\Stmt\Class_ $class, string $name): ?Node\Stmt\ClassMethod
    {
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name->name === $name) {
                return $stmt;
            }
        }

        return null;
    }

    private function extractSchemaOps(Node $node, array &$created, array &$modified, array &$dropped): void
    {
        if ($node instanceof Node\Expr\StaticCall) {
            $call = $node;
        } elseif ($node instanceof Node\Stmt\Expression && $node->expr instanceof Node\Expr\StaticCall) {
            $call = $node->expr;
            if (! $call->class instanceof Node\Name) {
                return;
            }
            $class = $call->class->toString();
            if ($class !== 'Schema' && $class !== 'Illuminate\Support\Facades\Schema') {
                return;
            }

            $method = $call->name instanceof Node\Identifier ? $call->name->name : null;

            if ($method === 'create' && isset($call->args[0]) && $call->args[0]->value instanceof Node\Scalar\String_) {
                $created[] = $call->args[0]->value->value;
            } elseif ($method === 'table' && isset($call->args[0]) && $call->args[0]->value instanceof Node\Scalar\String_) {
                $modified[] = $call->args[0]->value->value;
            } elseif (in_array($method, ['drop', 'dropIfExists'], true) && isset($call->args[0]) && $call->args[0]->value instanceof Node\Scalar\String_) {
                $dropped[] = $call->args[0]->value->value;
            }
        }

        foreach ($node->getSubNodeNames() as $sub) {
            $val = $node->$sub;
            if ($val instanceof Node) {
                $this->extractSchemaOps($val, $created, $modified, $dropped);
            } elseif (is_array($val)) {
                foreach ($val as $item) {
                    if ($item instanceof Node) {
                        $this->extractSchemaOps($item, $created, $modified, $dropped);
                    }
                }
            }
        }
    }
}
