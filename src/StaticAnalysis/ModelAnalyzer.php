<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Carve\StaticAnalysis\ValueObjects\ModelInfo;
use PhpParser\Node;

final class ModelAnalyzer
{
    private const RELATIONSHIP_TYPES = [
        'HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'HasManyThrough',
        'MorphOne', 'MorphMany', 'MorphToMany', 'MorphTo', 'MorphedByMany',
    ];

    public function __construct(
        private readonly PhpParserFactory $parserFactory,
    ) {}

    public function analyze(array $files): array
    {
        $parser = $this->parserFactory->create();
        $models = [];

        foreach ($files as $file) {
            if (! str_contains($file, 'Models'.DIRECTORY_SEPARATOR) && ! str_contains($file, 'Models/')) {
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

            $info = $this->extractModelInfo($stmts, $file);
            if ($info !== null) {
                $models[] = $info;
            }
        }

        return $models;
    }

    private function extractModelInfo(array $stmts, string $file): ?ModelInfo
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

        $classNode = $this->findExtendingClass($stmts, 'Model');
        if ($classNode === null) {
            return null;
        }

        $className = $classNode->name->name;
        $fqcn = $namespace !== null ? $namespace.'\\'.$className : $className;

        $table = $this->extractPropertyValue($classNode, 'table');
        $primaryKey = $this->extractPropertyValue($classNode, 'primaryKey');
        $fillable = $this->extractArrayProperty($classNode, 'fillable');
        $casts = $this->extractArrayProperty($classNode, 'casts');

        $relationships = $this->extractRelationships($classNode, $imports);

        $inferredTable = $table ?? $this->inferTableName($className);

        return new ModelInfo(
            class: $fqcn,
            table: $inferredTable,
            primaryKey: $primaryKey,
            fillable: $fillable,
            casts: $casts,
            relationships: $relationships,
        );
    }

    private function findExtendingClass(array $stmts, string $baseName): ?Node\Stmt\Class_
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Class_ && $stmt->extends !== null) {
                $extends = $stmt->extends->toString();
                if ($extends === $baseName || str_ends_with($extends, '\\'.$baseName)) {
                    return $stmt;
                }
            }
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $found = $this->findExtendingClass($stmt->stmts, $baseName);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function extractPropertyValue(Node\Stmt\Class_ $class, string $name): ?string
    {
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $prop) {
                    if ($prop->name->name === $name) {
                        if ($prop->default instanceof Node\Scalar\String_) {
                            return $prop->default->value;
                        }

                        return null;
                    }
                }
            }
        }

        return null;
    }

    private function extractArrayProperty(Node\Stmt\Class_ $class, string $name): array
    {
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $prop) {
                    if ($prop->name->name === $name) {
                        if ($prop->default instanceof Node\Expr\Array_) {
                            $values = [];
                            foreach ($prop->default->items as $item) {
                                if ($item !== null && $item->value instanceof Node\Scalar\String_) {
                                    $values[] = $item->value->value;
                                }
                            }

                            return $values;
                        }

                        return [];
                    }
                }
            }
        }

        return [];
    }

    private function extractRelationships(Node\Stmt\Class_ $class, array $imports): array
    {
        $relationships = [];

        foreach ($class->stmts as $stmt) {
            if (! $stmt instanceof Node\Stmt\ClassMethod) {
                continue;
            }

            if ($stmt->returnType === null) {
                continue;
            }

            $returnType = $stmt->returnType instanceof Node\Name
                ? ($imports[$stmt->returnType->toString()] ?? $stmt->returnType->toString())
                : null;

            if ($returnType === null) {
                continue;
            }

            $shortType = (fn () => null)();
            foreach (self::RELATIONSHIP_TYPES as $type) {
                if (str_ends_with($returnType, '\\'.$type) || $returnType === $type) {
                    $shortType = $type;
                    break;
                }
            }

            if ($shortType === null) {
                continue;
            }

            $related = null;
            if ($stmt->stmts !== null) {
                foreach ($stmt->stmts as $methodStmt) {
                    if ($methodStmt instanceof Node\Stmt\Return_
                        && $methodStmt->expr instanceof Node\Expr\MethodCall
                        && $methodStmt->expr->name instanceof Node\Identifier
                        && in_array($methodStmt->expr->name->name, ['hasMany', 'hasOne', 'belongsTo', 'belongsToMany', 'hasManyThrough', 'morphOne', 'morphMany', 'morphToMany', 'morphedByMany', 'morphTo'], true)
                    ) {
                        $args = $methodStmt->expr->args;
                        if (isset($args[0]) && $args[0]->value instanceof Node\Scalar\String_) {
                            $related = $args[0]->value->value;
                        }
                        if ($related !== null && isset($imports[$related])) {
                            $related = $imports[$related];
                        }
                        break;
                    }
                }
            }

            $relationships[] = [
                'name' => $stmt->name->name,
                'type' => $shortType,
                'related' => $related,
            ];
        }

        return $relationships;
    }

    private function inferTableName(string $className): string
    {
        $snake = mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));

        return str_ends_with($snake, 's') ? $snake : $snake.'s';
    }
}
