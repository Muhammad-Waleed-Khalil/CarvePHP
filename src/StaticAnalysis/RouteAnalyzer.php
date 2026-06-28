<?php

declare(strict_types=1);

namespace Carve\StaticAnalysis;

use Carve\StaticAnalysis\ValueObjects\RouteInfo;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;

final class RouteAnalyzer
{
    public const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete', 'options', 'any', 'match', 'resource', 'apiResource', 'view', 'redirect'];

    public function __construct(
        private readonly PhpParserFactory $parserFactory,
    ) {}

    public function analyze(array $files): array
    {
        $parser = $this->parserFactory->create();
        $routes = [];

        foreach ($files as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            if (! str_contains($code, 'Route::')) {
                continue;
            }

            $stmts = $parser->parse($code);
            if ($stmts === null) {
                continue;
            }

            $imports = $this->extractImports($stmts);

            $collector = new RouteCollector($file, $imports);
            $traverser = new NodeTraverser;
            $traverser->addVisitor(new ParentConnectingVisitor);
            $traverser->addVisitor($collector);
            $traverser->traverse($stmts);

            $routes = array_merge($routes, $collector->routes);
        }

        return $routes;
    }

    private function extractImports(array $stmts): array
    {
        $imports = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                foreach ($stmt->uses as $use) {
                    $imports[$use->name->getLast()] = $use->name->toString();
                }
            }
        }

        return $imports;
    }
}

final class RouteCollector extends NodeVisitorAbstract
{
    public array $routes = [];

    public function __construct(
        private readonly string $file,
        private readonly array $imports,
    ) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof Node\Expr\StaticCall) {
            return null;
        }

        if (! $node->class instanceof Node\Name) {
            return null;
        }

        $class = $node->class->toString();

        $routeClass = $this->imports[$class] ?? $class;
        if ($routeClass !== 'Route' && $routeClass !== 'Illuminate\Support\Facades\Route') {
            return null;
        }

        $method = $node->name instanceof Node\Identifier ? $node->name->name : null;
        if ($method === null || ! in_array($method, RouteAnalyzer::HTTP_METHODS, true)) {
            return null;
        }

        $args = $node->args;
        if (count($args) < 2) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        $uri = $args[0]->value instanceof Node\Scalar\String_ ? $args[0]->value->value : null;
        if ($uri === null) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        [$controller, $controllerMethod] = $this->extractTarget($args[1]->value);

        $routeName = $this->extractRouteName($node);
        $id = $routeName ?? strtolower($method).'-'.$uri;

        $this->routes[] = new RouteInfo(
            id: $id,
            method: strtoupper($method),
            uri: $uri,
            name: $routeName,
            action: $controller !== null ? $controller.'@'.($controllerMethod ?? '__invoke') : null,
            controller: $controller,
            controllerMethod: $controllerMethod,
            middleware: [],
            file: $this->file,
            line: $node->getStartLine(),
            prefix: null,
            domain: null,
        );

        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }

    private function extractTarget(Node\Expr $value): array
    {
        if ($value instanceof Node\Expr\Array_) {
            $items = $value->items;
            if (count($items) === 2) {
                $controller = $this->resolveControllerName($items[0]->value);
                $method = $items[1]->value instanceof Node\Scalar\String_ ? $items[1]->value->value : null;

                return [$controller, $method];
            }
        }

        if ($value instanceof Node\Scalar\String_) {
            $action = $value->value;
            if (str_contains($action, '@')) {
                return explode('@', $action, 2);
            }

            return [$action, '__invoke'];
        }

        return [null, null];
    }

    private function resolveControllerName(Node\Expr $node): ?string
    {
        if ($node instanceof Node\Expr\ClassConstFetch && $node->name instanceof Node\Identifier && $node->name->name === 'class') {
            $class = $node->class instanceof Node\Name ? $node->class->toString() : null;
            if ($class !== null && isset($this->imports[$class])) {
                return $this->imports[$class];
            }

            return $class;
        }

        if ($node instanceof Node\Scalar\String_) {
            return $node->value;
        }

        return null;
    }

    private function extractRouteName(Node\Expr\StaticCall $node): ?string
    {
        $parent = $node->getAttribute('parent');
        if (! $parent instanceof Node\Expr\MethodCall) {
            return null;
        }
        if (! $parent->name instanceof Node\Identifier || $parent->name->name !== 'name') {
            return null;
        }
        $arg = $parent->args[0] ?? null;
        if ($arg === null || ! $arg->value instanceof Node\Scalar\String_) {
            return null;
        }

        return $arg->value->value;
    }
}
