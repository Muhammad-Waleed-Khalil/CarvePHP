<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Carve\StaticAnalysis\ValueObjects\ClassInfo;
use Carve\StaticAnalysis\ValueObjects\MethodInfo;
use PhpParser\Node;

final class ControllerAnalyzer
{
    public function __construct(
        private readonly PhpParserFactory $parserFactory,
    ) {}

    public function analyze(array $files): array
    {
        $parser = $this->parserFactory->create();
        $controllers = [];

        foreach ($files as $file) {
            if (! str_contains($file, 'Http'.DIRECTORY_SEPARATOR.'Controllers') && ! str_contains($file, 'Http/Controllers')) {
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

            $info = $this->extractClassInfo($stmts, $file);
            if ($info !== null) {
                $controllers[] = $info;
            }
        }

        return $controllers;
    }

    private function extractClassInfo(array $stmts, string $file): ?ClassInfo
    {
        $namespace = null;
        $imports = [];

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $namespace = $stmt->name->toString();
                foreach ($stmt->stmts as $inner) {
                    if ($inner instanceof Node\Stmt\Use_) {
                        foreach ($inner->uses as $use) {
                            $imports[$use->name->getLast()] = $use->name->toString();
                        }
                    }
                }
            }
        }

        $classNode = $this->findClassNode($stmts);
        if ($classNode === null) {
            return null;
        }

        $className = $classNode->name->name;
        $fqcn = $namespace !== null ? $namespace.'\\'.$className : $className;

        $methods = [];
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $params = [];
                foreach ($stmt->params as $param) {
                    $params[] = $param->var->name;
                }

                $returnType = null;
                if ($stmt->returnType instanceof Node\Name) {
                    $returnType = $stmt->returnType->toString();
                    if (isset($imports[$returnType])) {
                        $returnType = $imports[$returnType];
                    }
                } elseif ($stmt->returnType instanceof Node\Identifier) {
                    $returnType = $stmt->returnType->name;
                }

                $methods[] = new MethodInfo(
                    name: $stmt->name->name,
                    parameters: $params,
                    returnType: $returnType,
                );
            }
        }

        $deps = $this->extractDependencies($classNode, $imports);

        return new ClassInfo(
            name: $fqcn,
            namespace: $namespace ?? '',
            file: $file,
            methods: $methods,
            dependencies: $deps,
        );
    }

    private function findClassNode(array $stmts): ?Node\Stmt\Class_
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Class_) {
                return $stmt;
            }
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $found = $this->findClassNode($stmt->stmts);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function extractDependencies(Node\Stmt\Class_ $class, array $imports): array
    {
        $deps = [];

        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name->name === '__construct') {
                foreach ($stmt->params as $param) {
                    $type = $param->type;
                    if ($type instanceof Node\Name) {
                        $name = $type->toString();
                        $deps[] = $imports[$name] ?? $name;
                    } elseif ($type instanceof Node\Identifier) {
                        $deps[] = $type->name;
                    }
                }
            }

            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $this->collectMethodDeps($stmt, $imports, $deps);
            }
        }

        return array_values(array_unique($deps));
    }

    private function collectMethodDeps(Node\Stmt\ClassMethod $method, array $imports, array &$deps): void
    {
        if ($method->stmts === null) {
            return;
        }

        foreach ($method->stmts as $stmt) {
            $this->collectDepsFromNode($stmt, $imports, $deps);
        }
    }

    private function collectDepsFromNode(Node $node, array $imports, array &$deps): void
    {
        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
            $name = $node->class->toString();
            $resolved = $imports[$name] ?? $name;
            if ($resolved !== 'parent' && $resolved !== 'self' && $resolved !== 'static') {
                $deps[] = $resolved;
            }
        }

        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            $name = $node->class->toString();
            $resolved = $imports[$name] ?? $name;
            if ($resolved !== 'self' && $resolved !== 'static') {
                $deps[] = $resolved;
            }
        }

        foreach ($node->getSubNodeNames() as $sub) {
            $val = $node->$sub;
            if ($val instanceof Node) {
                $this->collectDepsFromNode($val, $imports, $deps);
            } elseif (is_array($val)) {
                foreach ($val as $item) {
                    if ($item instanceof Node) {
                        $this->collectDepsFromNode($item, $imports, $deps);
                    }
                }
            }
        }
    }
}
