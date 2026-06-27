<?php

declare(strict_types=1);

namespace Carve\Graph;

use Carve\Graph\ValueObjects\GraphBuildResult;
use Illuminate\Support\Facades\DB;

final class GraphBuilder
{
    private const WEIGHT_ROUTE_TO_CONTROLLER = 3.0;

    private const WEIGHT_CONTROLLER_TO_MODEL = 2.5;

    private const WEIGHT_MODEL_TO_TABLE = 4.0;

    private const WEIGHT_STATIC_TABLE_TOUCH = 2.5;

    private const WEIGHT_RUNTIME_TABLE_TOUCH = 5.0;

    private const WEIGHT_RUNTIME_COOCCURRENCE = 6.0;

    public function __construct(
        private readonly GraphRepository $repository,
    ) {}

    public function build(string $staticScanPath, string $traceSource = 'database', ?string $tracePath = null, ?string $from = null, ?string $to = null): GraphBuildResult
    {
        $graph = new WeightedGraph;
        $staticData = $this->loadStaticData($staticScanPath);
        $traceData = $this->loadTraceData($traceSource, $tracePath, $from, $to);

        $this->addStaticNodes($graph, $staticData);
        $this->addStaticEdges($graph, $staticData);
        $this->addTraceEdges($graph, $traceData);

        $this->repository->save($graph);

        return new GraphBuildResult(
            nodeCount: count($graph->getNodes()),
            edgeCount: count($graph->getEdges()),
            graph: $graph,
        );
    }

    private function loadStaticData(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function loadTraceData(string $source, ?string $tracePath, ?string $from, ?string $to): array
    {
        if ($source === 'jsonl') {
            return $this->loadTraceDataFromJsonl($tracePath);
        }

        if ($source === 'database') {
            return $this->loadTraceDataFromDatabase($from, $to);
        }

        return [];
    }

    private function loadTraceDataFromJsonl(?string $path): array
    {
        if ($path === null || ! file_exists($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $traces = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data !== null) {
                $traces[] = $data;
            }
        }

        return $traces;
    }

    private function loadTraceDataFromDatabase(?string $from, ?string $to): array
    {
        if (! class_exists(DB::class)) {
            return [];
        }

        try {
            $query = DB::table('carve_traces');

            if ($from !== null) {
                $query->where('started_at', '>=', $from);
            }

            if ($to !== null) {
                $query->where('started_at', '<=', $to);
            }

            $traces = $query->get()->all();
            $result = [];

            foreach ($traces as $trace) {
                $events = [];
                $traceEvents = DB::table('carve_trace_events')
                    ->where('trace_id', $trace->trace_id)
                    ->get()
                    ->all();

                foreach ($traceEvents as $event) {
                    $events[] = [
                        'event_type' => $event->event_type,
                        'table_name' => $event->table_name,
                        'operation' => $event->operation,
                        'duration_ms' => $event->duration_ms,
                    ];
                }

                $result[] = [
                    'trace_id' => $trace->trace_id,
                    'type' => $trace->type,
                    'method' => $trace->method,
                    'uri' => $trace->uri,
                    'route_name' => $trace->route_name,
                    'events' => $events,
                ];
            }

            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    private function addStaticNodes(WeightedGraph $graph, array $data): void
    {
        foreach ($data['routes'] ?? [] as $route) {
            $id = $route['id'] ?? 'route:'.$route['method'].':'.$route['uri'];
            $graph->addNode(new Node(
                id: $id,
                type: NodeType::ROUTE,
                name: $route['name'] ?? $route['uri'],
                label: ($route['method'] ?? 'GET').' '.($route['uri'] ?? ''),
                meta: ['uri' => $route['uri'] ?? '', 'method' => $route['method'] ?? 'GET'],
            ));
        }

        $addedControllers = [];
        foreach ($data['classes'] ?? [] as $class) {
            $fqcn = ($class['namespace'] ?? '') !== '' ? ($class['namespace'] ?? '').'\\'.$class['name'] : $class['name'];
            $id = 'controller:'.$fqcn;
            if (! isset($addedControllers[$id])) {
                $addedControllers[$id] = true;
                $graph->addNode(new Node(
                    id: $id,
                    type: NodeType::CONTROLLER,
                    name: $class['name'],
                    label: $class['name'],
                    meta: ['namespace' => $class['namespace'] ?? '', 'file' => $class['file'] ?? ''],
                ));
            }
        }

        foreach ($data['models'] ?? [] as $model) {
            $id = 'model:'.$model['class'];
            $graph->addNode(new Node(
                id: $id,
                type: NodeType::MODEL,
                name: $model['class'],
                label: $model['class'],
                meta: ['table' => $model['table'] ?? ''],
            ));
        }

        foreach ($data['tables'] ?? [] as $table) {
            $id = 'table:'.$table['name'];
            $graph->addNode(new Node(
                id: $id,
                type: NodeType::TABLE,
                name: $table['name'],
                label: $table['name'],
                meta: ['model_class' => $table['model_class'] ?? null, 'source' => $table['source'] ?? 'unknown'],
            ));
        }
    }

    private function addStaticEdges(WeightedGraph $graph, array $data): void
    {
        $nameToFqcn = [];
        foreach ($data['classes'] ?? [] as $class) {
            $fqcn = ($class['namespace'] ?? '') !== '' ? ($class['namespace'] ?? '').'\\'.$class['name'] : $class['name'];
            $nameToFqcn[$class['name']] = $fqcn;
        }

        $fileToFqcn = [];
        foreach ($data['classes'] ?? [] as $class) {
            $fqcn = ($class['namespace'] ?? '') !== '' ? ($class['namespace'] ?? '').'\\'.$class['name'] : $class['name'];
            if (isset($class['file'])) {
                $fileToFqcn[$class['file']] = $fqcn;
            }
        }

        $routeControllerMap = [];
        foreach ($data['routes'] ?? [] as $route) {
            $routeId = $route['id'] ?? 'route:'.$route['method'].':'.$route['uri'];
            $controllerName = $route['controller'] ?? null;

            if ($controllerName !== null) {
                $routeControllerMap[$routeId] = $controllerName;
            }
        }

        // route_handled_by
        foreach ($routeControllerMap as $routeId => $controllerName) {
            $controllerId = 'controller:'.$controllerName;
            if ($graph->getNode($controllerId) !== null) {
                $graph->addEdge(new Edge(
                    from: $routeId,
                    to: $controllerId,
                    type: EdgeType::ROUTE_HANDLED_BY,
                    weight: self::WEIGHT_ROUTE_TO_CONTROLLER,
                    evidence: ["Route mapped to {$controllerName}"],
                ));
            }
        }

        // uses_model from controller class dependencies
        foreach ($data['classes'] ?? [] as $class) {
            $fqcn = $nameToFqcn[$class['name']] ?? $class['name'];
            $controllerId = 'controller:'.$fqcn;

            if ($graph->getNode($controllerId) === null) {
                continue;
            }

            foreach ($class['dependencies'] ?? [] as $dependency) {
                $modelId = 'model:'.$dependency;
                if ($graph->getNode($modelId) !== null) {
                    $graph->addEdge(new Edge(
                        from: $controllerId,
                        to: $modelId,
                        type: EdgeType::USES_MODEL,
                        weight: self::WEIGHT_CONTROLLER_TO_MODEL,
                        evidence: ["{$fqcn} depends on {$dependency}"],
                    ));
                }
            }
        }

        // model_owns_table
        foreach ($data['models'] ?? [] as $model) {
            $modelId = 'model:'.$model['class'];
            $tableName = $model['table'] ?? null;

            if ($tableName !== null) {
                $tableId = 'table:'.$tableName;
                if ($graph->getNode($tableId) !== null) {
                    $graph->addEdge(new Edge(
                        from: $modelId,
                        to: $tableId,
                        type: EdgeType::MODEL_OWNS_TABLE,
                        weight: self::WEIGHT_MODEL_TO_TABLE,
                        evidence: ["{$model['class']} maps to table {$tableName}"],
                    ));
                }
            }
        }

        // touches_table from db_usages
        foreach ($data['db_usages'] ?? [] as $usage) {
            $file = $usage['file'] ?? '';
            $fqcn = $fileToFqcn[$file] ?? null;

            if ($fqcn === null) {
                continue;
            }

            $controllerId = 'controller:'.$fqcn;
            if ($graph->getNode($controllerId) === null) {
                continue;
            }

            foreach ($usage['raw_sql_tables'] ?? [] as $tableName) {
                $tableId = 'table:'.$tableName;
                if ($graph->getNode($tableId) !== null) {
                    $graph->addEdge(new Edge(
                        from: $controllerId,
                        to: $tableId,
                        type: EdgeType::TOUCHES_TABLE,
                        weight: self::WEIGHT_STATIC_TABLE_TOUCH,
                        evidence: ["{$fqcn} touches {$tableName} via DB query in {$file}"],
                    ));
                }
            }
        }
    }

    private function addTraceEdges(WeightedGraph $graph, array $traceData): void
    {
        // Build route-to-controller lookup from the static data
        $routeControllerMap = [];
        foreach ($graph->getEdges() as $edge) {
            if ($edge->type === EdgeType::ROUTE_HANDLED_BY) {
                $routeControllerMap[$edge->from] = $edge->to;
            }
        }

        $tableCooccurrences = [];
        $routeTableTouches = [];

        foreach ($traceData as $trace) {
            $events = $trace['events'] ?? [];
            $traceTables = [];

            foreach ($events as $event) {
                $tableName = $event['table_name'] ?? null;
                if ($tableName === null) {
                    continue;
                }

                $tableId = 'table:'.$tableName;
                $traceTables[$tableId] = true;

                // Ensure table node exists
                if ($graph->getNode($tableId) === null) {
                    $graph->addNode(new Node(
                        id: $tableId,
                        type: NodeType::TABLE,
                        name: $tableName,
                        label: $tableName,
                        meta: ['source' => 'runtime_discovered'],
                    ));
                }
            }

            // Track co-occurrences
            $tableIds = array_keys($traceTables);
            for ($i = 0; $i < count($tableIds); $i++) {
                for ($j = $i + 1; $j < count($tableIds); $j++) {
                    $key = $tableIds[$i].'::'.$tableIds[$j];
                    $tableCooccurrences[$key] = ($tableCooccurrences[$key] ?? 0) + 1;
                }
            }

            // Track route-to-table touches
            $routeName = $trace['route_name'] ?? null;
            if ($routeName !== null) {
                foreach ($tableIds as $tableId) {
                    $touchesKey = $routeName.'::'.$tableId;
                    $routeTableTouches[$touchesKey] = ($routeTableTouches[$touchesKey] ?? 0) + 1;
                }
            }
        }

        // Add co_occurs edges
        foreach ($tableCooccurrences as $key => $count) {
            [$tableA, $tableB] = explode('::', $key);
            $weight = self::WEIGHT_RUNTIME_COOCCURRENCE * $count;

            $graph->addEdge(new Edge(
                from: $tableA,
                to: $tableB,
                type: EdgeType::CO_OCCURS,
                weight: $weight,
                evidence: ["Co-occurred {$count}x in runtime traces"],
            ));
        }

        // Add runtime touches_table edges via route-controller chain
        foreach ($routeTableTouches as $key => $count) {
            [$routeName, $tableId] = explode('::', $key);

            foreach ($traceData as $trace) {
                if (($trace['route_name'] ?? null) !== $routeName) {
                    continue;
                }

                $traceMethod = $trace['method'] ?? '';
                $traceUri = $trace['uri'] ?? '';
                $normalizedUri = str_starts_with($traceUri, '/') ? $traceUri : '/'.$traceUri;
                $routeId = 'route:'.$traceMethod.':'.$normalizedUri;

                $controllerId = $routeControllerMap[$routeId] ?? null;
                if ($controllerId !== null && $graph->getNode($controllerId) !== null) {
                    $weight = self::WEIGHT_RUNTIME_TABLE_TOUCH * $count;
                    $graph->addEdge(new Edge(
                        from: $controllerId,
                        to: $tableId,
                        type: EdgeType::TOUCHES_TABLE,
                        weight: $weight,
                        evidence: ["Runtime trace shows {$controllerId} touched {$tableId} {$count}x"],
                    ));
                }
            }
        }
    }
}
